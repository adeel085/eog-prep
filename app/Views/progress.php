<?= $this->extend('Layouts/default_2') ?>

<?= $this->section('head') ?>
<style>
    .accordion-item {
        margin-bottom: 10px;
        border: 1px solid #c0c0c0;
        background-color: #fff;
    }
    .accordion-header {
        padding: 10px;
    }
    .accordion-body {
        padding: 10px;
        padding-left: 45px;
        background-color: #ffffff;
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

    .progress-item:not(:last-child) {
        margin-bottom: 10px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container" style="margin-top: 40px;">
    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-3">My Progress</h4>

            <div class="accordion-item">
                <div class="accordion-header d-flex justify-content-between" id="heading-<?= $user['id'] ?>">
                    <span><?= $user['username'] ?></span>
                    <span class="text-muted">Grade <?= $user['grade']['grade_level'] ?></span>
                </div>
                <div class="accordion-body">
                    <?php
                    foreach ($studentProgress as $progress) {
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
                    if (count($studentProgress) == 0) {
                        ?>
                        <div class="text-muted">No progress</div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>

<?= $this->endSection() ?>