<?= $this->extend('Layouts/default_2') ?>

<?= $this->section('head') ?>
<style>
    .question-center {
        width: 600px;
        max-width: 100%;
        margin: auto;
    }
    .stars-container {
        display: flex;
        justify-content: center;
        gap: 6px;
        font-size: 32px;
        color: #c0c0c0;
    }
    .notify.notify-autoclose::before {
        display: none;
    }
    .mcq-option-wrapper p {
        margin-bottom: 5px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="question-center">
                
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><?= $currentTopic['name'] ?></span>
                            <span>Level <?= $currentLevel ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        <div id="questionsWrapper">

                        </div>

                        <div class="question-footer d-flex justify-content-end mt-3">
                            <button class="btn btn-primary next-question" id="submitBtn">Submit</button>
                        </div>

                        <div class="question-solution mt-3" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Solution</h5>
                                    <div class="solution-content"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-primary next-question" id="nextQuestionBtn">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML"></script>
<script>
    let questions = <?= json_encode($questions) ?>;
    let currentQuestionIndex = -1;

    $(document).ready(async function() {

        showNextQuestion();

        $("#submitBtn").click(async function() {
            
            let questionType = $("#questionType").val();
            let questionId = $("#questionId").val();
            let selectedAnswer = undefined;

            if (questionType == 'mcq') {
                selectedAnswer = $('input[name="answer"]:checked').val();
            }
            else {
                selectedAnswer = $('.question-item').find('.text-answer').val().trim();

            }

            if (selectedAnswer == undefined || selectedAnswer == '') {
                new Notify({
                    title: 'Warning',
                    text: 'Please select an answer',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            let correct = false;

            questions[currentQuestionIndex].answers.forEach(answer => {
                if (answer.is_correct == 1 && (questionType == 'mcq' ? answer.answer == base64DecodeUnicode(selectedAnswer) : answer.answer == selectedAnswer)) {
                    correct = true;
                }
            });

            if (correct) {
                new Notify({
                    title: 'Success',
                    text: 'Correct answer',
                    status: 'success',
                    autoclose: true,
                    autotimeout: 3000
                });
            }
            else {
                new Notify({
                    title: 'Error',
                    text: 'Incorrect answer',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }

            showNextQuestion();
        });

        $(document).on('keyup', '.text-answer', function() {
            // If Enter key is pressed
            if (event.keyCode == 13) {
                if ($('#submitBtn').attr('data-disabled') == "true") {
                    return;
                }
                $('#submitBtn').click();
            }
        });

        $('#nextQuestionBtn').click(async function() {
            showNextQuestion();
        });
    });

    function showNextQuestion() {

        currentQuestionIndex++;

        $('.question-solution').hide();
        $('.question-solution .solution-content').html('');
        $('#submitBtn').removeAttr('data-disabled');
        $('#submitBtn').css({
            'pointer-events': 'none',
            'opacity': 0.5
        });

        $('.question-item').hide();

        let question = getQuestion();

        if (question == null) {
            new Notify({
                title: 'Info',
                text: 'No more questions found in your current topic and current level',
                status: 'info',
                autoclose: true,
                autotimeout: 3000
            });
            setTimeout(() => {
                window.location.href = baseUrl + '/page-selection';
            }, 3000);
            return;
        }

        renderQuestion(question);

        $('#submitBtn').css({
            'pointer-events': 'auto',
            'opacity': 1
        });
    }

    function renderQuestion(question) {
        $('#questionsWrapper').html(`
            <div class="question-item" data-id="questionid" style="display: block;">
                <h5 class="card-title">${question.question_html}</h5>

                <div class="answer-area">
                    ${(question.question_type == "text") ? `
                        <div class="answer-item">
                            <input type="text" class="form-control text-answer" autofocus>
                        </div>
                    ` : `
                        ${question.answers.map((answer, index) => `
                            <div class="answer-item mcq-option-wrapper">
                                <label class="d-flex align-items-center" style="gap: 10px;">
                                    <input type="radio" name="answer" class="answer-radio" value='${base64EncodeUnicode(answer.answer)}'>
                                    ${answer.answer}
                                </label>
                            </div>
                        `).join('')}
                    `}
                </div>

                <input type="hidden" id="questionType" value="${question.question_type}">
                <input type="hidden" id="questionId" value="${question.id}">
            </div>
        `);

        $('.text-answer').focus();

        // Force MathJax to re-render the newly loaded content
        MathJax.Hub.Queue([
            "Typeset",
            MathJax.Hub,
            $("#questionsWrapper").get(0),
        ]);
    }

    function getQuestion() {
        if (currentQuestionIndex >= questions.length) {
            return null;
        }

        return questions[currentQuestionIndex];
    }

    function base64EncodeUnicode(str) {
        return btoa(
            new TextEncoder().encode(str).reduce((data, byte) => data + String.fromCharCode(byte), '')
        );
    }

    function base64DecodeUnicode(str) {
        return new TextDecoder().decode(Uint8Array.from(atob(str), c => c.charCodeAt(0)));
    }

    if ('BroadcastChannel' in window) {
        const channel = new BroadcastChannel('tab-communication');
        const TAB_ID = Date.now().toString(); // Unique identifier for this tab
        const PAGE_ID = 'student-home-page';

        // Function to handle incoming messages
        channel.onmessage = (event) => {
            const { type, tabId, pageId } = event.data;

            if (pageId !== PAGE_ID) {
                console.log('Message ignored. Not intended for this page.');
                return;
            }

            if (type === 'NEW_TAB_OPENED' && tabId !== TAB_ID) {
                document.body.innerHTML = `
                    <div class="container pt-4">
                        <h3>This tab is now deactivated.</h3>
                        <p>You have opened this page in another tab.</p>
                    </div>
                `;
            }
        };

        // Notify other tabs that this tab is active
        function notifyNewTab() {
            channel.postMessage({
                type: 'NEW_TAB_OPENED',
                tabId: TAB_ID,
                pageId: PAGE_ID
            });
        }

        // Notify other tabs on page load
        notifyNewTab();

        // Clean up when the tab is closed
        window.addEventListener('beforeunload', () => {
            channel.close();
        });
    }
    else {
        console.warn('BroadcastChannel is not supported in this browser. Tab communication will not work.');
    }
</script>
<?= $this->endSection() ?>