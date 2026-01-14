<?= $this->extend('Layouts/default') ?>

<?= $this->section('head') ?>
<style>
    .login-page {
        background-color: #f8f9fa;
        height: var(--app-height);

        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-card {
        width: 400px;
        max-width: 100%;
        padding: 0 20px;
    }

    .login-form {
        background-color: #fff;
        padding: 20px;
        border: 1px solid #d1d1d1;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="login-page">
    <div class="login-card">
        <div class="d-flex justify-content-center">
            <span style="font-size: 30px; font-weight: bold;">EOG Prep</span>
        </div>
        <div class="login-form mt-4">
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Username" id="username" />
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" placeholder="Password" id="password" />
            </div>
            <div class="mb-3">
                <input type="checkbox" id="rememberMe" />
                <label class="mb-0" for="rememberMe">Remember me</label>
            </div>
            <div>
                <button class="btn btn-primary w-100" id="loginBtn">Login as Teacher</button>
            </div>
            <div class="text-right mt-3">
                <a href="<?= base_url('admin/teacher-registration') ?>">Don't have an account? Create one</a>
            </div>
        </div>

        <div class="mt-4 text-center text-muted">
            <small>
                &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
            </small>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $('#loginBtn').click(async function() {
        const username = $('#username').val();
        const password = $('#password').val();
        const rememberMe = $('#rememberMe').is(':checked');

        if (username == '' || password == '') {
            new Notify({
                title: 'Error',
                text: 'Please enter your username and password',
                status: 'error',
                autoclose: true,
                autotimeout: 3000
            });
            return;
        }

        try {

            let formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            formData.append('rememberMe', rememberMe);

            // Show loader in login button
            $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            const res = await ajaxCall({
                url: baseUrl + '/admin/login',
                data: formData,
                csrfHeader: '<?= csrf_header() ?>',
                csrfHash: '<?= csrf_hash() ?>'
            });

            if (res.status == 'success') {
                window.location.href = baseUrl + 'admin/dashboard';
                return;
            }
            else {
                new Notify({
                    title: 'Error',
                    text: res.message,
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }
        } catch (err) {
            new Notify({
                title: 'Error',
                text: err.responseJSON.message || 'Something went wrong',
                status: 'error',
                autoclose: true,
                autotimeout: 3000
            });
        }

        // Reset login button
        $('#loginBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
    });
</script>
<?= $this->endSection() ?>
