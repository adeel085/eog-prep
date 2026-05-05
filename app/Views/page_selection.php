<?= $this->extend('Layouts/default_2') ?>

<?= $this->section('head') ?>
<style>
    .chart-row {
        max-width: 1000px;
    }
    #weeklyGoalChart, #yearlyGoalChart {
        width: 300px !important;
        height: 300px !important;
    }
    a .card-title {
        color: #333333;
    }
    a.card:hover {
        text-decoration: none;
    }
    @media (max-width: 767px) {
        .chart-row {
            gap: 40px;
        }
        .page-card {
            width: 100%;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="modal" id="startSessionModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Class Assignment</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
                    <label for="name">Select Topic</label>
                    <select class="form-control" id="topic">
                        <option value="">Select Topic</option>
                        <?php foreach ($topics as $topic) : ?>
                            <option value="<?= $topic['id'] ?>"><?= esc($topic['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="difficultyLevelGroup">
                    <label for="name">Select Difficulty Level</label>
                    <select class="form-control" id="difficultyLevel">
                        <option value="">Select Difficulty Level</option>
                        <option value="1">Easy</option>
                        <option value="2">Medium</option>
                        <option value="3">Hard</option>
                    </select>
                </div>

                <div class="form-group d-none" id="fixedDifficultyLevelGroup">
                    <label>Difficulty Level</label>
                    <input type="text" class="form-control" id="fixedDifficultyLevelLabel" readonly>
                </div>

                <?php if ($hasAssignedTopics) : ?>
                    <small class="text-muted d-block">
                        Only the topics and levels assigned to your class are available here.
                    </small>
                <?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="startSessionBtn">Start Assignment</button>
				<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="customTestModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Make up your own test</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
                    <label>Select Grade</label>
                    <select class="form-control" id="customTestGrade">
                        <option value="">Select Grade</option>
                        <?php foreach ($customTestGrades as $grade) : ?>
                            <option value="<?= $grade['id'] ?>"><?= esc($grade['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

				<div class="form-group">
                    <label>Select Topic</label>
                    <select class="form-control" id="customTestTopic">
                        <option value="">Select Topic</option>
                    </select>
                    <small class="text-muted d-block mt-1" id="customTestTopicHelp">Select a grade first.</small>
                </div>

                <div class="form-group d-none" id="customTestDifficultyGroup">
                    <label>Select Level</label>
                    <select class="form-control" id="customTestDifficultyLevel">
                        <option value="">Select Level</option>
                        <option value="1">Easy</option>
                        <option value="2">Medium</option>
                        <option value="3">Hard</option>
                    </select>
                    <small class="text-muted">Choose one level for this practice test.</small>
                </div>

                <small class="text-muted d-block">
                    This test is independent from class assignments.
                </small>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="startCustomTestBtn">Start Test</button>
				<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <span>Hi <?= explode(' ', $user['full_name'])[0] ?>,</span>
            <h3>Welcome Back</h3>
        </div>

        <div>
            <a href="<?= base_url('logout') ?>" class="btn btn-primary">Logout</a>
        </div>
    </div>

    <div class="mt-4">

        <h4>Quick Links</h4>

        <div class="d-flex flex-wrap" style="gap: 10px;">
            <a
                class="card page-card"
                <?= $autoStartSessionUrl ? 'href="' . esc($autoStartSessionUrl) . '"' : 'data-toggle="modal" data-target="#startSessionModal"' ?>
                style="padding: 40px 60px; cursor: pointer;"
            >
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center">
                        <img src="<?= base_url('public/assets/images/session.png') ?>" alt="Class Assignment" class="img-fluid" style="width: 130px;">
                        <h5 class="card-title">Class Assignment</h5>
                    </div>
                </div>
            </a>

            <a class="card page-card" data-toggle="modal" data-target="#customTestModal" style="padding: 40px 60px; cursor: pointer;">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center">
                        <img src="<?= base_url('public/assets/images/progress.png') ?>" alt="Make up your own test" class="img-fluid" style="width: 130px;">
                        <h5 class="card-title text-center">Make up your<br>own test</h5>
                    </div>
                </div>
            </a>

            <!-- <a class="card page-card" style="padding: 40px 60px; cursor: pointer;" href="<?= base_url('progress') ?>">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center">
                        <img src="<?= base_url('public/assets/images/progress.png') ?>" alt="View Progress" class="img-fluid" style="width: 130px;">
                        <h5 class="card-title">View Progress</h5>
                    </div>
                </div>
            </a>

            <a class="card page-card" style="padding: 40px 60px; cursor: pointer;" href="<?= base_url('history') ?>">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center">
                        <img src="<?= base_url('public/assets/images/history.png') ?>" alt="View History" class="img-fluid" style="width: 130px;">
                        <h5 class="card-title">View History</h5>
                    </div>
                </div>
            </a> -->
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {
        const topics = <?= json_encode($topics) ?>;
        const customTestTopics = <?= json_encode($customTestTopics) ?>;
        const topicsById = {};
        const customTestTopicsById = {};

        topics.forEach((topic) => {
            topicsById[String(topic.id)] = topic;
        });

        customTestTopics.forEach((topic) => {
            customTestTopicsById[String(topic.id)] = topic;
        });

        function escapeHtml(value) {
            return $("<div>").text(value ?? "").html();
        }

        function getLevelLabel(level) {
            if (String(level) === '1') {
                return 'Easy';
            }

            if (String(level) === '2') {
                return 'Medium';
            }

            return 'Hard';
        }

        function renderLevelOptions(topicId) {
            const topic = topicsById[String(topicId)];

            $("#difficultyLevel").val('');
            $("#fixedDifficultyLevelLabel").val('');
            $("#difficultyLevelGroup").addClass('d-none');
            $("#fixedDifficultyLevelGroup").addClass('d-none');
            $("#difficultyLevel").html('<option value="">Select Difficulty Level</option>');

            if (!topic) {
                return;
            }

            if (topic.allows_all_levels) {
                $("#difficultyLevelGroup").removeClass('d-none');
                $("#difficultyLevel").html(`
                    <option value="">Select Difficulty Level</option>
                    <option value="1">Easy</option>
                    <option value="2">Medium</option>
                    <option value="3">Hard</option>
                `);
                return;
            }

            if (topic.assigned_levels.length === 1) {
                $("#fixedDifficultyLevelGroup").removeClass('d-none');
                $("#fixedDifficultyLevelLabel").val(getLevelLabel(topic.assigned_levels[0]));
                $("#difficultyLevel").html(`<option value="${topic.assigned_levels[0]}" selected>${getLevelLabel(topic.assigned_levels[0])}</option>`);
                $("#difficultyLevel").val(String(topic.assigned_levels[0]));
                return;
            }

            $("#difficultyLevelGroup").removeClass('d-none');
            $("#difficultyLevel").html(`
                <option value="">Select Difficulty Level</option>
                ${topic.assigned_levels.map((level) => `<option value="${level}">${getLevelLabel(level)}</option>`).join('')}
            `);
        }

        function renderCustomTestLevels(topicId) {
            const topic = customTestTopicsById[String(topicId)];

            $("#customTestDifficultyLevel").val('');
            $("#customTestDifficultyGroup").addClass('d-none');

            if (!topic) {
                $("#customTestTopicHelp").text('Select a topic.');
                return;
            }

            $("#customTestTopicHelp").text('');
            $("#customTestDifficultyGroup").removeClass('d-none');
        }

        function renderCustomTestTopics() {
            const gradeId = $("#customTestGrade").val();

            $("#customTestTopic").val('');
            $("#customTestDifficultyLevel").val('');
            $("#customTestDifficultyGroup").addClass('d-none');

            if (!gradeId) {
                $("#customTestTopic").html('<option value="">Select Topic</option>');
                $("#customTestTopicHelp").text('Select a grade first.');
                return;
            }

            const filteredTopics = customTestTopics.filter((topic) => String(topic.grade_id) === String(gradeId));
            const options = ['<option value="">Select Topic</option>'];

            filteredTopics.forEach((topic) => {
                options.push(`<option value="${topic.id}">${escapeHtml(topic.name)}</option>`);
            });

            $("#customTestTopic").html(options.join(''));

            if (filteredTopics.length === 0) {
                $("#customTestTopicHelp").text('No topics found for the selected grade.');
            }
            else {
                $("#customTestTopicHelp").text('');
            }
        }

        $("#topic").change(function() {
            renderLevelOptions($(this).val());
        });

        $("#customTestGrade").change(function() {
            renderCustomTestTopics();
        });

        $("#customTestTopic").change(function() {
            renderCustomTestLevels($(this).val());
        });

        $('#startSessionModal').on('show.bs.modal', function() {
            renderLevelOptions($("#topic").val());
        });

        $('#customTestModal').on('show.bs.modal', function() {
            $("#customTestGrade").val('');
            $("#customTestTopic").val('');
            renderCustomTestTopics();
            renderCustomTestLevels('');
        });

        $("#startSessionBtn").click(function() {
            let topicId = $("#topic").val();
            let difficultyLevel = $("#difficultyLevel").val();

            if (!topicId || !difficultyLevel) {
                new Notify({
                    title: 'Error',
                    text: 'Please select a topic and difficulty level',
                    status: 'error',
                });
                return;
            }

            window.location.href = '<?= base_url('home') ?>?topic_id=' + topicId + '&difficulty_level=' + difficultyLevel;
        });

        $("#startCustomTestBtn").click(function() {
            let gradeId = $("#customTestGrade").val();
            let topicId = $("#customTestTopic").val();
            let difficultyLevel = $("#customTestDifficultyLevel").val();

            if (!gradeId) {
                new Notify({
                    title: 'Error',
                    text: 'Please select a grade',
                    status: 'error',
                });
                return;
            }

            if (!topicId) {
                new Notify({
                    title: 'Error',
                    text: 'Please select a topic',
                    status: 'error',
                });
                return;
            }

            if (!difficultyLevel) {
                new Notify({
                    title: 'Error',
                    text: 'Please select a level',
                    status: 'error',
                });
                return;
            }

            window.location.href = '<?= base_url('home/custom-test') ?>?grade_id=' + gradeId + '&topic_id=' + topicId + '&difficulty_level=' + difficultyLevel;
        });
    });
</script>
<?= $this->endSection() ?>
