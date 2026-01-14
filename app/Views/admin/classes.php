<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Classes</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="modal" id="newClassModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">New Class</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
                <p class="text-muted">Enter the name of the class. Each class must have a unique name.</p>
				<div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name">
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="newClassSaveBtn">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="editClassModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Edit Class</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name_ed">
                </div>

                <input type="hidden" id="classId_ed">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="editClassSaveBtn">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="sendingEmailModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Email to Parents of <span id="classNameSendingEmail"></span></h5>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="startDate">Start Date</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="classIdSendingEmail">

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-sm btn-primary" id="sendEmailBtn">Send Email</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end align-items-center mb-4">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newClassModal">
                        <i class="fa fa-plus"></i> New Class
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
                            <?php foreach ($classes as $class) : ?>
                                <tr data-name="<?= $class['name'] ?>" data-id="<?= $class['id'] ?>">
                                    <td><?= $class['name'] ?></td>
                                    <td>
                                        <div class="d-flex justify-content-end" style="gap: 10px;">
                                            <a href="javascript:void(0)" class="table-action-btn send-email-btn" data-toggle="tooltip" title="Send Email to Parents">
                                                <i class="fa fa-envelope"></i>
                                            </a>
                                            <a href="<?= base_url('admin/classes/' . $class['id'] . '/students') ?>" class="table-action-btn" data-toggle="tooltip" title="Students List">
                                                <i class="fa fa-user"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="table-action-btn edit-class-btn" data-toggle="tooltip" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="table-action-btn delete-class-btn" data-toggle="tooltip" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php
                            if (count($classes) == 0) :
                            ?>
                                <tr>
                                    <td colspan="2" class="text-center">No classes found</td>
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

        $("#sendEmailBtn").click(async function() {

            let classId = $("#classIdSendingEmail").val();
            let startDate = $("#startDate").val();
            let endDate = $("#endDate").val();

            if (startDate == '' || endDate == '') {
                new Notify({
                    title: 'Input Error',
                    text: 'Please enter a start and end date.',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            if (startDate > endDate) {
                new Notify({
                    title: 'Input Error',
                    text: 'Start date cannot be greater than end date.',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            $("#sendEmailBtn").attr('data-content', $("#sendEmailBtn").html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            let formData = new FormData();
            formData.append('class_id', classId);
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);

            let response = await ajaxCall({
                url: baseUrl + '/class/send-email',
                data: formData,
                csrfHeader: '<?= csrf_header() ?>',
                csrfHash: '<?= csrf_hash() ?>'
            });

            if (response.status == 'success') {
                $("#sendingEmailModal").modal('hide');

                // reset the start and end date
                $("#startDate").val('');
                $("#endDate").val('');

                new Notify({
                    title: 'Success',
                    text: 'Emails sent successfully',
                    status: 'success',
                    autoclose: true,
                    autotimeout: 3000
                });
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

            $("#sendEmailBtn").html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $(".send-email-btn").click(function() {
            let classId = $(this).closest('tr').data('id');
            let className = $(this).closest('tr').data('name');

            $("#classIdSendingEmail").val(classId);

            $("#classNameSendingEmail").text(className);

            $("#sendingEmailModal").modal("show");
        });

        $("#newClassSaveBtn").click(async function() {
            const name = $("#name").val();

            if (name == '') {
                new Notify({
                    title: 'Input Error',
                    text: 'Please enter a name for the class.',
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
                    url: baseUrl + 'admin/classes/saveNew',
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

        $(".delete-class-btn").click(async function() {

            if (!confirm('Are you sure you want to delete this class?')) {
                return;
            }

            let classId = $(this).closest('tr').data('id');

            try {
                let formData = new FormData();
                formData.append('class_id', classId);

                // Show loader in submit button
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/classes/delete',
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

        $(".edit-class-btn").click(function() {

            let classId = $(this).closest('tr').data('id');
            let className = $(this).closest('tr').data('name');

            $("#classId_ed").val(classId);
            $("#name_ed").val(className);

            $("#editClassModal").modal('show');
        });

        $("#editClassSaveBtn").click(async function() {

            let classId = $("#classId_ed").val();
            let name = $("#name_ed").val();

            if (name == '') {
                new Notify({
                    title: 'Input Error',
                    text: 'Please enter a name for the class.',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            try {

                let formData = new FormData();
                formData.append('class_id', classId);
                formData.append('name', name);

                // Show loader in submit button
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/classes/update',
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