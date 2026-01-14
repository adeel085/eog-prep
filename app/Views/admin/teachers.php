<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Teachers</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-end align-items-center mb-4">
                    <a href="<?= base_url('/admin/teachers/new') ?>" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i> New Teacher
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher) : ?>
                                <tr data-id="<?= $teacher['id'] ?>">
                                    <td><?= $teacher['full_name'] ?></td>
                                    <td><?= $teacher['username'] ?></td>
                                    <td><?= $teacher['email'] ?></td>
                                    <td>
                                        <div class="d-flex justify-content-end table-action-btn" style="gap: 10px;">
                                            <a href="<?= base_url('/admin/teachers/edit/' . $teacher['id']) ?>" class="table-action-btn" data-toggle="tooltip" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="table-action-btn delete-btn" data-toggle="tooltip" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($teachers) == 0) : ?>
                                <tr>
                                    <td colspan="4" class="text-center">No teachers found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <?= $pager->links() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {

        $('.delete-btn').click(async function() {
            
            if (!confirm("Are you sure you want to delete this teacher?")) {
                return;
            }

            let $tr = $(this).closest('tr');

            let id = $tr.data('id');

            try {

                let formData = new FormData();
                formData.append('id', id);

                let res = await ajaxCall({
                    url: baseUrl + 'admin/teachers/delete',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.reload();
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
            }
            catch (err) {
                new Notify({
                    title: 'Error',
                    text: err.responseJSON.message || 'Something went wrong',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>