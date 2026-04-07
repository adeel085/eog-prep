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

<div class="modal" id="manageAssignmentsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assigned Topics for <span id="classNameAssignments"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    Assign a topic to this class. Leave level empty to allow students in this class to practice any level for that topic.
                </p>

                <div class="form-group">
                    <label for="assignmentGradeFilter">Filter by Grade</label>
                    <select class="form-control" id="assignmentGradeFilter">
                        <option value="">All Grades</option>
                        <?php foreach ($grades as $grade) : ?>
                            <option value="<?= $grade['id'] ?>"><?= esc($grade['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row align-items-end">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="assignmentTopicId">Topic</label>
                            <select class="form-control" id="assignmentTopicId">
                                <option value="">Select Topic</option>
                            </select>
                            <small class="text-muted" id="assignmentTopicHelp">
                                <?php if (count($topics) == 0) : ?>
                                    No topics are available to assign yet.
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="assignmentLevel">Level</label>
                            <select class="form-control" id="assignmentLevel">
                                <option value="">Any level</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-primary w-100 mb-3"
                            id="saveAssignmentBtn"
                            <?= count($topics) == 0 ? 'disabled' : '' ?>
                        >
                            Add
                        </button>
                    </div>
                </div>

                <input type="hidden" id="assignmentClassId">

                <hr>

                <div>
                    <h6 class="mb-3">Current Assignments</h6>
                    <div id="currentAssignmentsList"></div>
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
                                <th>Assigned Topics</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $class) : ?>
                                <tr
                                    data-name="<?= esc($class['name']) ?>"
                                    data-id="<?= $class['id'] ?>"
                                    data-assignments="<?= esc(json_encode($class['assignments'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), 'attr') ?>"
                                >
                                    <td><?= esc($class['name']) ?></td>
                                    <td>
                                        <?php if (!empty($class['assignments'])) : ?>
                                            <div class="d-flex flex-wrap" style="gap: 8px;">
                                                <?php foreach ($class['assignments'] as $assignment) : ?>
                                                    <span class="badge badge-primary" style="font-size: 12px; font-weight: 500;">
                                                        <?= esc($assignment['topic_name']) ?> (<?= $assignment['level'] === null ? 'Any level' : 'Level ' . $assignment['level'] ?>)
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else : ?>
                                            <span class="text-muted">No assigned topics. Students can choose topic and level as usual.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end" style="gap: 10px;">
                                            <a href="javascript:void(0)" class="table-action-btn manage-assignments-btn" data-toggle="tooltip" title="Manage Assigned Topics">
                                                <i class="fa fa-list"></i>
                                            </a>
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
                                    <td colspan="3" class="text-center">No classes found</td>
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
        const assignableTopics = <?= json_encode($topics) ?>;

        function escapeHtml(value) {
            return $("<div>").text(value ?? "").html();
        }

        function renderAssignableTopics(selectedTopicId = '') {
            const gradeId = $("#assignmentGradeFilter").val();
            const filteredTopics = assignableTopics.filter((topic) => {
                if (gradeId === '') {
                    return true;
                }

                return String(topic.grade_id ?? '') === String(gradeId);
            });

            const options = ['<option value="">Select Topic</option>'];
            filteredTopics.forEach((topic) => {
                const isSelected = String(selectedTopicId) === String(topic.id) ? 'selected' : '';
                options.push(`<option value="${topic.id}" ${isSelected}>${escapeHtml(topic.name)}</option>`);
            });

            $("#assignmentTopicId").html(options.join(''));
            $("#saveAssignmentBtn").prop('disabled', filteredTopics.length === 0);

            if (assignableTopics.length === 0) {
                $("#assignmentTopicHelp").text('No topics are available to assign yet.');
            }
            else if (filteredTopics.length === 0) {
                $("#assignmentTopicHelp").text('No topics found for the selected grade.');
            }
            else {
                $("#assignmentTopicHelp").text('');
            }
        }

        function formatAssignmentLabel(assignment) {
            if (assignment.level === null || assignment.level === '' || typeof assignment.level === 'undefined') {
                return `${assignment.topic_name} (Any level)`;
            }

            return `${assignment.topic_name} (Level ${assignment.level})`;
        }

        function renderAssignments(assignments) {
            if (!assignments || assignments.length === 0) {
                $("#currentAssignmentsList").html(`
                    <div class="text-muted">
                        No assignments yet. Students in this class will keep choosing topic and level normally.
                    </div>
                `);
                return;
            }

            const html = assignments.map((assignment) => `
                <div class="border rounded px-3 py-2 mb-2 d-flex justify-content-between align-items-center" style="gap: 12px;">
                    <div>${escapeHtml(formatAssignmentLabel(assignment))}</div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-assignment-btn" data-assignment-id="${assignment.id}">
                        Remove
                    </button>
                </div>
            `).join('');

            $("#currentAssignmentsList").html(html);
        }

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

            try {

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
            }
            catch (error) {
                if (error.status == 401) {
                    new Notify({
                        title: 'Error',
                        text: "Your session has expired. Redirecting to login page...",
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    return;
                }

                new Notify({
                    title: 'Error',
                    text: error.responseJSON.message || 'Something went wrong',
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

        $(".manage-assignments-btn").click(function() {
            let row = $(this).closest('tr');
            let classId = row.data('id');
            let className = row.data('name');
            let assignments = row.attr('data-assignments');

            try {
                assignments = assignments ? JSON.parse(assignments) : [];
            }
            catch (error) {
                assignments = [];
            }

            $("#assignmentClassId").val(classId);
            $("#classNameAssignments").text(className);
            $("#assignmentGradeFilter").val('');
            renderAssignableTopics();
            $("#assignmentTopicId").val('');
            $("#assignmentLevel").val('');

            renderAssignments(assignments);

            $("#manageAssignmentsModal").modal('show');
        });

        $("#assignmentGradeFilter").change(function() {
            renderAssignableTopics();
        });

        renderAssignableTopics();

        $("#saveAssignmentBtn").click(async function() {
            let classId = $("#assignmentClassId").val();
            let topicId = $("#assignmentTopicId").val();
            let level = $("#assignmentLevel").val();

            if (topicId == '') {
                new Notify({
                    title: 'Input Error',
                    text: 'Please select a topic.',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            try {
                let formData = new FormData();
                formData.append('class_id', classId);
                formData.append('topic_id', topicId);
                formData.append('level', level);

                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/classes/assign-topic-level',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.reload();
                    return;
                }

                new Notify({
                    title: 'Error',
                    text: res.message,
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
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

            $(this).html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $(document).on('click', '.remove-assignment-btn', async function() {
            if (!confirm('Are you sure you want to remove this assignment?')) {
                return;
            }

            let assignmentId = $(this).data('assignment-id');

            try {
                let formData = new FormData();
                formData.append('assignment_id', assignmentId);

                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/classes/remove-topic-level',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.reload();
                    return;
                }

                new Notify({
                    title: 'Error',
                    text: res.message,
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
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

            $(this).html($(this).attr('data-content')).css('pointer-events', 'auto');
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
