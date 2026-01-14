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
        width: 350px;
        max-width: 100%;
        padding: 0 20px;
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
            <div>
                <button class="btn btn-primary w-100" id="loginBtn">Login</button>
            </div>

            <div class="mt-3 text-center">
                <a href="<?= base_url('signup') ?>">Don't have an account? Sign up</a>
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
    $(document).ready(function() {

        $('#loginBtn').click(async function() {
            const username = $('#username').val();
            const password = $('#password').val();

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

                // Show loader in login button
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                const res = await ajaxCall({
                    url: baseUrl + '/login',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.href = baseUrl + 'page-selection';
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
    });
</script>
<?= $this->endSection() ?>
