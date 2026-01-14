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

    <?php
    if ($weeklyGoal) {
        ?>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row chart-row">

                            <!-- Left section for Weekly Goal doughnut chart -->
                            <div class="col-md-6">
                                <h5 class="card-title text-center">My Weekly Goal</h5>
                                <div class="d-flex justify-content-center">
                                    <canvas id="weeklyGoalChart"></canvas>
                                </div>
                            </div>

                            <!-- Right section for Cumulative Yearly Goal doughnut chart -->
                            <div class="col-md-6">
                                <h5 class="card-title text-center">My Yearly Goal</h5>
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

    <div class="mt-4">

        <h4>Quick Links</h4>

        <?php
        if ($currentTopic && !empty($currentTopic['tutorial_link'])) {
            ?>
            <div class="card mb-2">
                <div class="card-body">
                    <span><?= $currentTopic['name'] ?> &nbsp; <a target="_blank" href="<?= $currentTopic['tutorial_link'] ?>">Watch Tutorial</a></span>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="d-flex flex-wrap" style="gap: 10px;">
            <a class="card page-card" style="padding: 40px 60px; cursor: pointer;" href="<?= base_url('home') ?>">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center">
                        <img src="<?= base_url('public/assets/images/session.png') ?>" alt="Start Session" class="img-fluid" style="width: 130px;">
                        <h5 class="card-title">Start Session</h5>
                    </div>
                </div>
            </a>

            <a class="card page-card" style="padding: 40px 60px; cursor: pointer;" href="<?= base_url('progress') ?>">
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
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<?php
if ($weeklyGoal) {
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const earned = <?= $currentWeekPoints ?>;
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