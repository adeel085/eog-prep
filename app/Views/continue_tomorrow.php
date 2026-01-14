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
                        <h3 class="mb-3">🎉 Congratulations!</h3>
                        <span>You've already reached a higher level today.</span>
                        <br>
                        <span>Take a well-deserved rest and come back <strong>tomorrow</strong> to continue your journey 🚀</span>
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
