/* Shared upload feedback and clipboard behavior for the admin library and package modal. */
(() => {
    'use strict';
    const config = window.packageToolsConfig;
    const csrfToken = () => {
        const cookie = document.cookie.split('; ').find(item => item.startsWith(config.csrfCookie + '='));
        return cookie ? decodeURIComponent(cookie.substring(cookie.indexOf('=') + 1)) : config.csrfHash;
    };
    async function copyText(value) {
        if (navigator.clipboard && window.isSecureContext) {
            try { await navigator.clipboard.writeText(value); return; } catch (_) { /* Use selection fallback. */ }
        }
        const temporary = document.createElement('textarea');
        temporary.value = value;
        temporary.style.position = 'fixed';
        temporary.style.opacity = '0';
        // Keep the temporary field inside Bootstrap's modal focus trap when it is open.
        (document.querySelector('.modal.show .modal-body') || document.body).appendChild(temporary);
        temporary.select();
        const copied = document.execCommand('copy');
        temporary.remove();
        if (!copied) throw new Error('Select the text and copy it manually.');
    }
    function showResult(form, success, message, errors = []) {
        const result = form.querySelector('[data-upload-result]');
        result.className = 'mt-3 alert ' + (success ? 'alert-success' : 'alert-danger');
        result.replaceChildren();
        const heading = document.createElement('div');
        heading.textContent = message;
        result.appendChild(heading);
        if (errors.length) {
            const list = document.createElement('ul');
            list.className = 'mb-0 mt-2 pl-3';
            errors.forEach(error => {
                const item = document.createElement('li');
                item.textContent = error;
                list.appendChild(item);
            });
            result.appendChild(list);
        }
        result.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
    document.querySelectorAll('[data-copy-target]').forEach(button => {
        button.addEventListener('click', async () => {
            try {
                await copyText(document.getElementById(button.dataset.copyTarget).value);
                button.textContent = 'URL copied';
            } catch (_) {
                const input = document.getElementById(button.dataset.copyTarget);
                input.focus(); input.select();
                button.textContent = 'Select and copy the URL above';
            }
        });
    });
    const promptButton = document.getElementById('copyPackagePrompt');
    const promptArea = document.getElementById('packageChatGptPrompt');
    if (promptButton) {
        const template = promptArea.value;
        const updatePrompt = () => {
            const label = (id, fallback) => {
                const select = document.getElementById(id);
                return select.value ? select.options[select.selectedIndex].text : fallback;
            };
            promptArea.value = template.replace('{{topic}}', label('packageTopic', '[choose topic]'))
                .replace('{{level}}', label('packageLevel', '[choose difficulty]'))
                .replace('{{grade}}', label('packageGrade', '[choose grade]'));
            promptButton.textContent = 'Copy ChatGPT Prompt';
        };
        ['packageTopic', 'packageLevel', 'packageGrade'].forEach(id => document.getElementById(id).addEventListener('change', updatePrompt));
        updatePrompt();
        promptButton.addEventListener('click', async () => {
            try {
                await copyText(promptArea.value);
                promptButton.textContent = 'Prompt copied';
            } catch (_) {
                document.getElementById('packagePromptDetails').open = true;
                promptArea.focus(); promptArea.select();
                promptButton.textContent = 'Select and copy the prompt below';
            }
        });
    }
    document.querySelectorAll('[data-upload-form]').forEach(form => {
        let busy = false;
        const modal = form.closest('.modal');
        if (modal && window.jQuery) {
            window.jQuery(modal).on('hide.bs.modal', event => { if (busy) event.preventDefault(); });
        }
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (busy || !form.reportValidity()) return;
            const data = new FormData(form);
            const files = [...form.querySelectorAll('input[type=file]')].flatMap(input => [...input.files]);
            const library = form.id === 'imageUploadForm';
            if (files.some(file => file.size > (library ? 5 : 25) * 1024 * 1024) || (library && (files.length > 10 || files.reduce((sum, file) => sum + file.size, 0) > 25 * 1024 * 1024))) {
                showResult(form, false, library ? 'Choose up to 10 images, 5 MB each and 25 MB total.' : 'Choose a ZIP package of at most 25 MB.');
                return;
            }
            const button = form.querySelector('button[type=submit]');
            const originalLabel = button.innerHTML;
            busy = true;
            form.setAttribute('aria-busy', 'true');
            button.disabled = true;
            button.textContent = library ? 'Uploading…' : 'Validating and importing…';
            form.querySelector('[data-upload-refresh]').classList.add('d-none');
            try {
                const response = await fetch(form.action, {
                    method: 'POST', body: data, credentials: 'same-origin',
                    headers: { [config.csrfHeader]: csrfToken(), Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                let result;
                try { result = await response.json(); } catch (_) { throw new Error('The server could not process the upload. Check its upload and request-size limits, then retry.'); }
                if (result.csrfHash) config.csrfHash = result.csrfHash;
                if (!response.ok || result.status !== 'success') {
                    showResult(form, false, result.message || 'Upload rejected.', result.errors || (response.status === 403 ? ['Access denied or security token expired. Refresh this page and try again.'] : []));
                    return;
                }
                showResult(form, true, result.message);
                form.querySelector('[data-upload-refresh]').classList.remove('d-none');
                form.querySelector('input[type=file]').value = '';
            } catch (error) {
                showResult(form, false, error.message || 'Upload failed. Please try again.');
            } finally {
                busy = false;
                form.removeAttribute('aria-busy');
                button.disabled = false;
                button.innerHTML = originalLabel;
            }
        });
    });
})();
