<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Grades</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="modal" id="newGradeModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">New Grade</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
                <p class="text-muted">Enter the name of the grade. Each grade must have a unique name.</p>
				<div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name">
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="newGradeSaveBtn">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="editGradeModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Edit Grade</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name_ed">
                </div>

                <input type="hidden" id="gradeId_ed">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="editGradeSaveBtn">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end align-items-center mb-4">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newGradeModal">
                        <i class="fa fa-plus"></i> New Grade
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade) : ?>
                                <tr data-name="<?= $grade['name'] ?>" data-id="<?= $grade['id'] ?>">
                                    <td><?= $grade['name'] ?></td>
                                    <td>
                                        <div class="d-flex justify-content-end" style="gap: 10px;">
                                            <a href="javascript:void(0)" class="table-action-btn edit-grade-btn" data-toggle="tooltip" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="table-action-btn delete-grade-btn" data-toggle="tooltip" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php
                            if (count($grades) == 0) :
                            ?>
                                <tr>
                                    <td colspan="2" class="text-center">No grades found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(document).ready(function() {

        $("#newGradeSaveBtn").click(async function() {
            const name = $("#name").val();

            if (name == '') {
                new Notify({
                    title: 'Input Error',
                    text: 'Please enter a name for the grade.',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            try {

                let formData = new FormData();
                formData.append('name', name);

                // Show loader in submit button
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/grades/saveNew',
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

            // Reset submit button
            $(this).html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $(".delete-grade-btn").click(async function() {

            if (!confirm('Are you sure you want to delete this grade?')) {
                return;
            }

            let gradeId = $(this).closest('tr').data('id');

            try {
                let formData = new FormData();
                formData.append('grade_id', gradeId);

                // Show loader in submit button
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/grades/delete',
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

            // Reset submit button
            $(this).html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $(".edit-grade-btn").click(function() {

            let gradeId = $(this).closest('tr').data('id');
            let gradeName = $(this).closest('tr').data('name');

            $("#gradeId_ed").val(gradeId);
            $("#name_ed").val(gradeName);

            $("#editGradeModal").modal('show');
        });

        $("#editGradeSaveBtn").click(async function() {

            let gradeId = $("#gradeId_ed").val();
            let name = $("#name_ed").val();

            if (name == '') {
                new Notify({
                    title: 'Input Error',
                    text: 'Please enter a name for the grade.',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            try {

                let formData = new FormData();
                formData.append('grade_id', gradeId);
                formData.append('name', name);

                // Show loader in submit button
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/grades/update',
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

            // Reset submit button
            $(this).html($(this).attr('data-content')).css('pointer-events', 'auto');
        });
    });
</script>
<?= $this->endSection() ?>