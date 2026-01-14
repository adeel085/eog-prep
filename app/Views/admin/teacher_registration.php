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
            <h4 class="text-center mb-4">Create Your Account</h4>
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Full Name" id="name" />
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
            <div>
                <button class="btn btn-primary w-100" id="registerBtn">Create My Account</button>
            </div>
            <div class="text-right mt-3">
                <a href="<?= base_url('admin') ?>">Already have an account? Login</a>
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
    $('#registerBtn').click(async function(e) {
        
        let name = $('#name').val().trim();
        let username = $('#username').val().trim();
        let email = $('#email').val().trim();
        let password = $('#password').val().trim();

        if (!name || !username || !email || !password) {
            new Notify({
                status: 'error',
                title: 'Error',
                text: 'Please fill all the required (*) fields',
                timeout: 3000,
                autoclose: true
            });
            return;
        }

        try {
            let formData = new FormData();
            formData.append('name', name);
            formData.append('username', username);
            formData.append('email', email);
            formData.append('password', password);

            $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            let response = await ajaxCall({
                url: baseUrl + '/admin/teachers/register',
                data: formData,
                csrfHeader: '<?= csrf_header() ?>',
                csrfHash: '<?= csrf_hash() ?>'
            });

            if (response.status == 'success') {
                window.location.href = baseUrl + 'admin/teachers';
                return;
            }
            else {
                new Notify({
                    title: 'Error',
                    text: response.message,
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }
        } catch (error) {
            new Notify({
                title: 'Error',
                text: error.responseJSON.message || 'Something went wrong',
                status: 'error',
                autoclose: true,
                autotimeout: 3000
            });
        }

        // Reset save button
        $('#registerBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
    });
</script>
<?= $this->endSection() ?>
