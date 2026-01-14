<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Edit Teacher</h4>

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
                            <input type="text" class="form-control" id="name" value="<?= $teacher['full_name'] ?>">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" value="<?= $teacher['username'] ?>">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" value="<?= $teacher['email'] ?>">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="password">Password <small>(Leave blank to keep the same)</small></label>
                            <input type="password" class="form-control" id="password">
                        </div>
                    </div>

                    <input type="hidden" id="id" value="<?= $teacher['id'] ?>">
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-sm btn-primary" id="saveBtn">Update</button>
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
            
            let id = $('#id').val();
            let name = $('#name').val().trim();
            let username = $('#username').val().trim();
            let email = $('#email').val().trim();
            let password = $('#password').val().trim();

            if (!name || !username || !email) {
                new Notify({
                    title: 'Error',
                    text: 'Please fill all the required (*) fields',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            try {
                let formData = new FormData();

                formData.append('id', id);
                formData.append('name', name);
                formData.append('username', username);
                formData.append('email', email);
                formData.append('password', password);
                
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let response = await ajaxCall({
                    url: baseUrl + '/admin/teachers/update',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (response.status == 'success') {
                    window.location.reload();
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