<?= $this->extend('Layouts/default_2') ?>

<?= $this->section('head') ?>

<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-0">
                        <span>No <?= isset($level) ? 'level ' . $level : '' ?> questions found in your current topic: <?= $currentTopic['name'] ?></span>
                        <br>
                        <a href="<?= base_url('logout') ?>">Logout</a>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(document).ready(function() {

        
    });
</script>
<?= $this->endSection() ?>
