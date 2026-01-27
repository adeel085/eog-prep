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
            <img src="<?= base_url('public/assets/images/logo.jpg') ?>" alt="Logo" style="width: 150px; height: auto;">
        </div>
        <div class="login-form mt-4">
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Full Name" id="full_name" />
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Username" id="username" />
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" id="email" />
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" placeholder="Password" id="password" />
            </div>
            <div class="mb-3">
                <select class="form-control" id="grade">
                    <option value="" disabled selected>Select Grade</option>
                    <?php foreach ($grades as $grade) : ?>
                        <option value="<?= $grade['id'] ?>"><?= $grade['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button class="btn btn-primary w-100" id="signupBtn">Signup</button>
            </div>

            <div class="mt-3 text-center">
                <a href="<?= base_url() ?>">Already have an account? Login</a>
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

        $('#signupBtn').click(async function() {
            const full_name = $('#full_name').val();
            const username = $('#username').val();
            const email = $('#email').val();
            const password = $('#password').val();
            const gradeId = $('#grade').val();

            if (full_name == '' || username == '' || email == '' || password == '' || gradeId == '') {
                new Notify({
                    title: 'Error',
                    text: 'Please enter your full name, username, email, password and grade',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            try {

                let formData = new FormData();
                formData.append('full_name', full_name);
                formData.append('username', username);
                formData.append('email', email);
                formData.append('password', password);
                formData.append('gradeId', gradeId);
                
                // Show loader in login button
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                const res = await ajaxCall({
                    url: baseUrl + '/signup-user',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.href = baseUrl;
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

            // Reset signup button
            $('#signupBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
        });
    });
</script>
<?= $this->endSection() ?>
