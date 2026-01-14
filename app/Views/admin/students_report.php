<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('head') ?>
<style>
    .accordion-item {
        border: 1px solid #c0c0c0;
    }
    .accordion-header {
        padding: 10px;
    }
    .accordion-body {
        padding: 10px;
        padding-left: 45px;
        background-color: #f0f0f0;
        position: relative;
    }
    .accordion-body::before {
        content: '';
        position: absolute;
        left: 25px;
        top: 10px;
        width: 3px;
        height: calc(100% - 20px);
        background-color: #9a9a9a;
        border-radius: 12px;
    }
    #weeklyGoalChart, #yearlyGoalChart {
        width: 300px !important;
        height: 300px !important;
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

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0"><?= $student['full_name'] ?>'s Reports</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-4">Progress Report</p>

                <!-- Date filter -->
                <div class="d-flex flex-wrap mb-4" style="column-gap: 20px; row-gap: 10px;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <label class="mb-0" for="startDateProgress" style="white-space: nowrap;">Start Date</label>
                        <input type="date" class="form-control form-control-sm" id="startDateProgress" value="<?= $startDateProgress ?>">
                    </div>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <label class="mb-0" for="endDateProgress" style="white-space: nowrap;">End Date</label>
                        <input type="date" class="form-control form-control-sm" id="endDateProgress" value="<?= $endDateProgress ?>">
                    </div>

                    <div>
                        <button class="btn btn-primary btn-sm" id="progressFilterBtn">Filter</button>
                        <?php if ($filteredProgress) : ?>
                            <a class="btn btn-secondary btn-sm" href="<?= base_url('/admin/students/reports/' . $student['id']) ?>">Reset Filter</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="accordion-item">
                    <div class="accordion-header d-flex justify-content-between" id="heading-<?= $student['id'] ?>">
                        <span><?= $student['username'] ?></span>
                        <span class="text-muted">Grade <?= $student['grade']['grade_level'] ?></span>
                    </div>
                    <div class="accordion-body">
                        <?php
                        foreach ($student['progress'] as $progress) {
                            if (!isset($progress['topic'])) {
                                continue;
                            }
                            ?>
                            <div class="progress-item">
                                <span><?= $progress['topic']['name'] ?> &nbsp; &nbsp; Level <?= $progress['level'] ?></span>
                                &nbsp; &nbsp;
                                <!-- Complete or In Progress badge -->
                                <span class="badge <?= $progress['completed'] ? 'bg-success' : 'bg-info' ?>" style="color: #fff;">
                                    <?= $progress['completed'] ? 'Completed' : 'In Progress' ?>
                                </span>
                            </div>
                            <?php
                        }
                        if (count($student['progress']) == 0) {
                            ?>
                            <div class="text-muted">No progress <?= $filteredProgress ? 'in the selected period' : '' ?></div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-4">Missed Questions <?= $isLast7Days ? '(last 7 days)' : '' ?></p>

                <!-- Date filter -->
                <div class="d-flex flex-wrap" style="column-gap: 20px; row-gap: 10px;">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <label class="mb-0" for="startDate" style="white-space: nowrap;">Start Date</label>
                        <input type="date" class="form-control form-control-sm" id="startDate" value="<?= $startDate ?>">
                    </div>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <label class="mb-0" for="endDate" style="white-space: nowrap;">End Date</label>
                        <input type="date" class="form-control form-control-sm" id="endDate" value="<?= $endDate ?>">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm mr-1" id="missedQuestionsFilterBtn">Filter</button>
                        <button class="btn btn-primary btn-sm" id="sendMissedQuestionsEmail">
                            <i class="fa fa-envelope"></i> Send Email
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <?php foreach ($missingQuestions as $question) : ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= $question['question_html'] ?></h5>
                                <?php
                                    foreach ($question['student_answers'] as $answer) {
                                        ?>
                                        <div class="d-flex align-items-center mb-1 answer-item" style="gap: 10px;">
                                            <span class="text-danger">❌</span>
                                            <div style="display: inline-block;margin: 0;background: #dcdcdc;padding: 1px;white-space: pre;"><?= $answer['student_answer'] ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <span style="font-size: 15px;" class="text-muted">[<?= date('M d, Y h:i A', strtotime($answer['created_at'])) ?>]</span>
                                        </div>
                                        <?php
                                    }
                                ?>
                                <div class="d-flex align-items-center mb-3 answer-item" style="gap: 10px;">
                                    <span class="text-success">✅</span>
                                    <div style="display: inline-block;margin: 0;background: #dcdcdc;padding: 1px;white-space: pre;"><?= $question['correct_answer'] ?></div>
                                </div>
                                <p class="card-text text-muted">Incorrect Attempts: <?= $question['incorrect_count'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($missingQuestions) == 0) : ?>
                        <div class="text-muted">No missed questions in the <?= $isLast7Days ? 'last 7 days' : 'selected period' ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if ($weeklyGoal) {
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-4"><strong><?= $student['full_name'] ?>'s</strong> Points Earned</p>

                    <div class="row chart-row">

                        <!-- Left section for Weekly Goal doughnut chart -->
                        <div class="col-md-6">
                            <h5 class="card-title text-center">This Week</h5>
                            <div class="d-flex justify-content-center">
                                <canvas id="weeklyGoalChart"></canvas>
                            </div>
                        </div>

                        <!-- Right section for Cumulative Yearly Goal doughnut chart -->
                        <div class="col-md-6">
                            <h5 class="card-title text-center">This Year</h5>
                            <div class="d-flex justify-content-center">
                                <canvas id="yearlyGoalChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {

        $("#sendMissedQuestionsEmail").click(async () => {

            const studentId = "<?= $student['id'] ?>";
            const startDate = "<?= $startDate ?>";
            const endDate = "<?= $endDate ?>";
            const startDateInputValue = $("#startDate").val();
            const endDateInputValue = $("#endDate").val();

            if (startDateInputValue != startDate || endDateInputValue != endDate) {
                new Notify({
                    title: 'Input Error',
                    text: 'You have changed the date values. Please click the <b>Filter</b> button again to apply the new date filter before sending the email.',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 6000
                });
                return;
            }

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
                    text: 'Start date cannot be after end date',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            $("#sendMissedQuestionsEmail").attr('data-content', $("#sendMissedQuestionsEmail").html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            let formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('start_date', startDate);
            formData.append('end_date', endDate);

            let response = await ajaxCall({
                url: baseUrl + '/admin/students/send-missed-questions-email',
                data: formData,
                csrfHeader: '<?= csrf_header() ?>',
                csrfHash: '<?= csrf_hash() ?>'
            });

            if (response.status == 'success') {
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

            $("#sendMissedQuestionsEmail").html($("#sendMissedQuestionsEmail").attr('data-content')).css('pointer-events', 'auto');
        });

        $("#progressFilterBtn").click(() => {
            const startDate = $("#startDateProgress").val();
            const endDate = $("#endDateProgress").val();

            // Check if startDate is before endDate
            if (startDate > endDate) {
                new Notify({
                    title: 'Error',
                    text: 'Start date cannot be after end date',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            window.location.href = "<?= base_url('/admin/students/reports/' . $student['id']) ?>?sdp=" + startDate + "&edp=" + endDate;
        });

        $("#missedQuestionsFilterBtn").click(() => {
            const startDate = $("#startDate").val();
            const endDate = $("#endDate").val();

            // Check if startDate is before endDate
            if (startDate > endDate) {
                new Notify({
                    title: 'Error',
                    text: 'Start date cannot be after end date',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            window.location.href = "<?= base_url('/admin/students/reports/' . $student['id']) ?>?sd=" + startDate + "&ed=" + endDate;
        });
    });
</script>
<?php
if ($weeklyGoal) {
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const earned = <?= $currentWeekPoints ?? 0 ?>;
        const goal = <?= $weeklyGoal['goal_points'] ?>;
        const percent = Math.round((earned / goal) * 100);

        const yearlyEarned = <?= $currentYearTotalPoints ?>;
        const yearlyGoal = 3500;
        const yearlyPercent = Math.round((yearlyEarned / yearlyGoal) * 100);

        const ctx = document.getElementById('weeklyGoalChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Earned Points', 'Remaining Points'],
                datasets: [{
                    data: [earned, goal - earned],
                    backgroundColor: ['#4CAF50', '#e0e0e0'],
                    borderWidth: 1
                }]
            },
            options: {
                cutout: '75%',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed}`;
                            }
                        }
                    }
                }
            },
            plugins: [
                {
                    id: 'centerText',
                    beforeDraw(chart) {
                        const {width} = chart;
                        const {height} = chart;
                        const ctx = chart.ctx;
                        ctx.restore();

                        const fontSize = (height / 160).toFixed(2);
                        ctx.font = `${fontSize}em sans-serif`;
                        ctx.textBaseline = "middle";

                        const text = `${earned}/${goal}`;
                        const textX = Math.round((width - ctx.measureText(text).width) / 2);
                        const textY = (height / 2) + 15;

                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    }
                }
            ]
        });

        const yearlyCtx = document.getElementById('yearlyGoalChart').getContext('2d');
        new Chart(yearlyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Earned Points', 'Remaining Points'],
                datasets: [{
                    data: [yearlyEarned, yearlyGoal - yearlyEarned],
                    backgroundColor: ['#4CAF50', '#e0e0e0'],
                    borderWidth: 1
                }]
            },
            options: {
                cutout: '75%',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed}`;
                            }
                        }
                    }
                }
            },
            plugins: [
                {
                    id: 'centerText',
                    beforeDraw(chart) {
                        const {width} = chart;
                        const {height} = chart;
                        const ctx = chart.ctx;
                        ctx.restore();

                        const fontSize = (height / 160).toFixed(2);
                        ctx.font = `${fontSize}em sans-serif`;
                        ctx.textBaseline = "middle";

                        const text = `${yearlyEarned}/${yearlyGoal}`;
                        const textX = Math.round((width - ctx.measureText(text).width) / 2);
                        const textY = (height / 2) + 15;

                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    }
                }
            ]
        });
    </script>
    <?php
}
?>
<?= $this->endSection() ?>