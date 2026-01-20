<?= $this->extend('Layouts/default') ?>

<?= $this->section('head') ?>
<style>
    .header {
        border-bottom: 1px solid #e0e0e0;
        padding-top: 10px;
        padding-bottom: 10px;
        background-color: #ffffff;
    }
    body {
        color: #2f2f2f;
        background-color: #eeeeee;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid header">
    <div class="row">
        <div class="col-12">
            <img src="<?= base_url('public/assets/images/logo.jpg') ?>" alt="Logo" style="width: 100px; height: auto;">
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row" style="row-gap: 20px; max-width: 800px; margin: 0 auto;">
        <div class="col-12">
            <h3 class="text-center">Welcome to the <?= APP_NAME ?></h3>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body pt-5 pb-5">
                    <div class="d-flex flex-column justify-content-center align-items-center">
                        <div style="font-size: 50px;">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <a href="<?= base_url('login') ?>" class="btn btn-primary">I am a Student</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body pt-5 pb-5">
                    <div class="d-flex flex-column justify-content-center align-items-center">
                        <div style="font-size: 50px;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <a href="<?= base_url('admin') ?>" class="btn btn-primary">I am a Teacher</a>
                    </div>
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
