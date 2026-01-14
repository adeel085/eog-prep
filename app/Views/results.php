<?= $this->extend('Layouts/default') ?>

<?= $this->section('head') ?>
<style>
    .logo {
        width: 130px;
    }

    .stars-container {
        color: #bfbfbf;
    }
    .stars-item.active {
        color: #f94949;
    }

    .stars-item:nth-child(1) {
        font-size: 30px;
    }
    .stars-item:nth-child(2) {
        font-size: 45px;
    }
    .stars-item:nth-child(3) {
        font-size: 60px;
    }
    .stars-item:nth-child(4) {
        font-size: 45px;
    }
    .stars-item:nth-child(5) {
        font-size: 30px;
    }

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

<div class="container pt-3">
    <img src="<?= base_url('public/assets/images/Transparent_Logo.png') ?>" alt="Logo" class="logo">
</div>

<div class="d-flex flex-column justify-content-center align-items-center pt-5">

    <div class="text-center">
        <h3><?= ($stars > 3) ? "Well done!" : "" ?> You got <?= $stars ?> star<?= ($stars > 1) ? "s" : "" ?></h3>
    </div>

    <div class="stars-container d-flex align-items-end">
        <?php
        for ($i = 1; $i <= 5; $i++) {
            ?>
            <div class="stars-item <?= $i <= $stars ? 'active' : '' ?>">
                <i class="fa fa-star"></i>
            </div>
            <?php
        }
        ?>
    </div>

    <?php
    if ($weeklyGoal) {
        ?>
        <div class="row mt-4">
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

    <div class="text-center mt-4">
        <a href="<?= base_url('/') ?>" class="btn btn-primary">Continue</a>
    </div>

    <?php
    if ($currentTopic && !empty($currentTopic['tutorial_link'])) {
        ?>
        <div class="text-center mt-4">
            <span><?= $currentTopic['name'] ?> &nbsp; <a target="_blank" href="<?= $currentTopic['tutorial_link'] ?>">Watch Tutorial</a></span>
        </div>
        <?php
    }
    ?>
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
