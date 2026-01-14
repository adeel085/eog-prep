<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">New Teacher</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-sm btn-primary" id="saveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {
        $('#saveBtn').click(async function(e) {
            
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
                    url: baseUrl + '/admin/teachers/saveNew',
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
            $('#saveBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
        });
    });
</script>
<?= $this->endSection() ?>