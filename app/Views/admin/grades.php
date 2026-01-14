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

<div class="modal" id="topicsModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Set Grade Topics</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
                <p class="text-muted">Select the topics you want to add to this grade.</p>
				<div class="form-group">
                    <?php foreach ($topics as $topic) : ?>
                        <?php
                        if ($topic['owner_id'] != $user['id']) {
                            continue;
                        }
                        ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="topic-<?= $topic['id'] ?>" value="<?= $topic['id'] ?>">
                            <label class="form-check-label" for="topic-<?= $topic['id'] ?>"><?= $topic['name'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <input type="hidden" id="gradeId_topicsModal" value="">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="topicsSaveBtn">Save</button>
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
                <p class="text-muted">Enter the name of the grade.</p>
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

                <input type="hidden" id="grade_name_ed">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="editGradeSaveBtn">Update</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end align-items-center mb-4">
                    <button class="btn btn-sm btn-primary" id="newGradeBtn">
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
                                <tr data-id="<?= $grade['id'] ?>" data-name="<?= $grade['name'] ?>" data-topics='<?= json_encode($grade['topics']) ?>'>
                                    <td><?= $grade['name'] ?></td>
                                    <td>
                                        <div class="d-flex justify-content-end" style="gap: 10px;">
                                            <a href="javascript:void(0)" class="table-action-btn topics-btn" data-toggle="tooltip" title="Associated Topics">
                                                <i class="fa fa-book"></i>
                                            </a>
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
                            <?php if (count($grades) == 0) : ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No grades found</td>
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

        $("#topicsSaveBtn").click(async function() {

            let gradeId = $("#gradeId_topicsModal").val();
            let topics = [];

            $("input[type='checkbox']:checked").each(function() {
                topics.push($(this).val());
            });

            $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            let formData = new FormData();
            formData.append("gradeId", gradeId);
            formData.append("topicsIds", topics.join(','));

            try {
                let res = await ajaxCall({
                    url: baseUrl + 'admin/grades/updateTopics',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    new Notify({
                        title: 'Success',
                        text: 'Topics added/removed successfully',
                        status: 'success',
                        autoclose: true,
                        autotimeout: 3000
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    return;
                }
                else {
                    new Notify({
                        title: 'Error',
                        text: res.message || 'Something went wrong',
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

            // Reset save button
            $("#topicsSaveBtn").html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $(".topics-btn").click(function() {

            $("#topicsModal").find("input[type='checkbox']").prop("checked", false);

            let gradeId = $(this).closest("tr").data("id");
            let gradeTopics = $(this).closest("tr").data("topics");

            gradeTopics.forEach(topic => {
                $("#topic-" + topic['topic_id']).prop("checked", true);
            });

            $("#gradeId_topicsModal").val(gradeId);

            $("#topicsModal").modal("show");
        });

        $('.edit-grade-btn').click(function() {
            
            let $tr = $(this).closest('tr');

            let name = $tr.data('name');
            
            $('#name_ed').val(name);
            $('#grade_name_ed').val(name);
            
            $('#editGradeModal').modal('show');
        });

        $('#editGradeSaveBtn').click(async function() {
            let name = $('#name_ed').val();
            let gradeName = $('#grade_name_ed').val();
            
            try {

                let formData = new FormData();
            
                formData.append('grade_name', gradeName);
                formData.append('new_name', name);

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
        });

        $('.delete-grade-btn').click(async function() {
            let name = $(this).closest('tr').data('name');
            
            if (!confirm('Are you sure you want to delete this grade?')) {
                return;
            }

            try {

                let formData = new FormData();
                formData.append('name', name);

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
        });

        $('#newGradeBtn').click(function() {
            $("#newGradeModal").modal('show');
        });

        $('#newGradeSaveBtn').click(async function() {
            let name = $('#name').val();
            
            if (name == '') {
                new Notify({
                    status: 'error',
                    title: 'Error',
                    text: 'Please enter a name',
                    timeout: 3000,
                    autoclose: true
                });
                return;
            }

            try {

                let formData = new FormData();
                formData.append('name', name);

                // Show loader in button
                $('#newGradeSaveBtn').attr('data-content', $('#newGradeSaveBtn').html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + '/admin/grades/saveNew',
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

            // Reset login button
            $('#newGradeSaveBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
        });
    });
</script>
<?= $this->endSection() ?>