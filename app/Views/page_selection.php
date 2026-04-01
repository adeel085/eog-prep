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
				<h5 class="modal-title">Start Session</h5>
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
                        Your class has assigned topics. Only those topics and levels are available for this session.
                    </small>
                <?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="startSessionBtn">Start Session</button>
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
                        <img src="<?= base_url('public/assets/images/session.png') ?>" alt="Start Session" class="img-fluid" style="width: 130px;">
                        <h5 class="card-title">Start Session</h5>
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
        const topicsById = {};

        topics.forEach((topic) => {
            topicsById[String(topic.id)] = topic;
        });

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

        $("#topic").change(function() {
            renderLevelOptions($(this).val());
        });

        $('#startSessionModal').on('show.bs.modal', function() {
            renderLevelOptions($("#topic").val());
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
    });
</script>
<?= $this->endSection() ?>
