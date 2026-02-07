<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="modal" id="topicWizardModal">
    <div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Topic Wizard</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
                <p class="text-muted">You can use the topic wizard to create a new topic by combining the questions from existing topics.</p>

                <div class="mb-4">
                    <div class="form-group">
                        <label for="topicWizardName">Topic Name</label>
                        <input type="text" class="form-control form-control-sm" id="topicWizardName" placeholder="Topic Name">
                    </div>
                </div>

                <div class="d-flex justify-content-end flex-wrap mb-4" style="gap: 10px;">

                    <button class="btn btn-sm btn-outline-primary" id="topicWizardAddBtn">
                        <i class="fa fa-plus"></i> Add Topic
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm" id="topicWizardTable">
                        <thead>
                            <tr>
                                <th style="font-weight: 400;">Topic</th>
                                <th style="font-weight: 400;">Level 1 Max Questions</th>
                                <th style="font-weight: 400;">Level 2 Max Questions</th>
                                <th style="font-weight: 400;">Level 3 Max Questions</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-control form-control-sm topic-wizard-select">
                                        <option value="">Select Topic</option>
                                        <?php foreach ($topics as $topic) : ?>
                                            <option value="<?= $topic['id'] ?>"><?= $topic['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm topic-wizard-max-questions-l1">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm topic-wizard-max-questions-l2">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm topic-wizard-max-questions-l3">
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="topicWizardCreateBtn">Create Topic</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Topics</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 10px;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <label class="mb-0" for="gradeFilter">Filter by Grade:</label>
                        <select class="form-control form-control-sm" id="gradeFilter" style="width: auto;">
                            <option value="">All Grades</option>
                            <?php foreach ($grades as $grade) : ?>
                                <option value="<?= $grade['id'] ?>" <?= (isset($filterGradeId) && $filterGradeId == $grade['id']) ? 'selected' : '' ?>><?= $grade['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($filterGradeId)) : ?>
                            <a href="<?= base_url('/admin/topics') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fa fa-times"></i> Clear Filter
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#topicWizardModal">
                            <i class="fa fa-hat-wizard"></i> Topic Wizard
                        </button>
                        <a href="<?= base_url('/admin/topics/new') ?>" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> New Topic
                        </a>
                    </div>
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
                            <?php foreach ($topics as $topic) : ?>
                                <tr data-id="<?= $topic['id'] ?>">
                                    <td><?= $topic['name'] ?></td>
                                    <td>
                                        <div class="d-flex justify-content-end table-action-btn" style="gap: 10px;">
                                            <a href="<?= base_url('/admin/topics/' . $topic['id'] . '/questions') ?>" class="table-action-btn" data-toggle="tooltip" title="Questions List">
                                                <i class="fa fa-align-left"></i>
                                            </a>
                                            <a href="<?= base_url('/admin/topics/edit/' . $topic['id']) ?>" class="table-action-btn" data-toggle="tooltip" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="table-action-btn delete-btn" data-toggle="tooltip" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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

    function removeTopicFromWizard(e) {
        let $tr = $(e).closest('tr');
        $tr.remove();
    }

    $(() => {

        $("#gradeFilter").change(function() {
            let gradeId = $(this).val();
            window.location.href = baseUrl + 'admin/topics?gradeId=' + gradeId;
        });

        $("#topicWizardCreateBtn").click(async function(e) {

            let topicName = $("#topicWizardName").val();
            let topics = [];

            if (!topicName) {
                new Notify({
                    title: 'Error',
                    text: 'Please enter a topic name',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            let error = false;

            $("#topicWizardTable .topic-wizard-row").each(function() {
                let $tr = $(this);
                let topicId = $tr.find('.topic-wizard-select').val();
                let level1QuestionsCount = $tr.find('.topic-wizard-max-questions-l1').val();
                let level2QuestionsCount = $tr.find('.topic-wizard-max-questions-l2').val();
                let level3QuestionsCount = $tr.find('.topic-wizard-max-questions-l3').val();

                if (!topicId) {
                    error = "Please select a topic in all rows";
                    return false;
                }

                if (level1QuestionsCount === "" || level2QuestionsCount === "" || level3QuestionsCount === "") {
                    error = "Please enter a valid number in all levels max questions field";
                    return false;
                }

                level1QuestionsCount = Number(level1QuestionsCount);
                level2QuestionsCount = Number(level2QuestionsCount);
                level3QuestionsCount = Number(level3QuestionsCount);

                if (isNaN(level1QuestionsCount) || isNaN(level2QuestionsCount) || isNaN(level3QuestionsCount)) {
                    error = "Please enter a valid number in all levels max questions field";
                    return false;
                }

                topics.push({
                    topicId: topicId,
                    level: 1,
                    maxQuestionsCount: level1QuestionsCount
                });

                topics.push({
                    topicId: topicId,
                    level: 2,
                    maxQuestionsCount: level2QuestionsCount
                });

                topics.push({
                    topicId: topicId,
                    level: 3,
                    maxQuestionsCount: level3QuestionsCount
                });
            });

            if (error) {
                new Notify({
                    title: 'Error',
                    text: error,
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            // send the topics to the server
            try {

                let formData = new FormData();
                formData.append('topics', JSON.stringify(topics));
                formData.append('topicName', topicName);

                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let res = await ajaxCall({
                    url: baseUrl + 'admin/topics/create-from-wizard',
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

            // reset the button
            $(this).html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $("#topicWizardAddBtn").click(function(e) {

            $("#topicWizardTable").append(`
                <tr class="topic-wizard-row">
                    <td>
                        <select class="form-control form-control-sm topic-wizard-select">
                            <option value="">Select Topic</option>
                            <?php foreach ($topics as $topic) : ?>
                                <option value="<?= $topic['id'] ?>"><?= $topic['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm topic-wizard-max-questions-l1"></td>
                    <td><input type="text" class="form-control form-control-sm topic-wizard-max-questions-l2"></td>
                    <td><input type="text" class="form-control form-control-sm topic-wizard-max-questions-l3"></td>
                    <td>
                        <div class="text-right">
                            <i class="fa fa-trash" onclick="removeTopicFromWizard(this)" style="cursor: pointer;"></i>
                        </div>
                    </td>
                </tr>
            `);
        });

        $(document).on('change', '.topic-wizard-select', function() {
            let $tr = $(this).closest('tr');

            let level1QuestionsCount = $tr.find('.topic-wizard-max-questions-l1').val();
            let level2QuestionsCount = $tr.find('.topic-wizard-max-questions-l2').val();
            let level3QuestionsCount = $tr.find('.topic-wizard-max-questions-l3').val();

            if (level1QuestionsCount === "") {
                $tr.find('.topic-wizard-max-questions-l1').val(5);
            }
            
            if (level2QuestionsCount === "") {
                $tr.find('.topic-wizard-max-questions-l2').val(5);
            }

            if (level3QuestionsCount === "") {
                $tr.find('.topic-wizard-max-questions-l3').val(5);
            }
        });

        $('.delete-btn').click(async function() {
            
            if (!confirm("Are you sure you want to delete this topic?")) {
                return;
            }

            let $tr = $(this).closest('tr');

            let id = $tr.data('id');

            try {

                let formData = new FormData();
                formData.append('id', id);

                let res = await ajaxCall({
                    url: baseUrl + 'admin/topics/delete',
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
        });
    });
</script>
<?= $this->endSection() ?>