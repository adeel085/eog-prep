<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Preferences</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <h5 class="mb-3">Send Email Reminders</h5>
                        <select class="form-control" id="sendEmailRemindersSelect">
                            <option value="1" <?= $sendEmailReminders == 1 ? 'selected' : '' ?>>Yes</option>
                            <option value="0" <?= $sendEmailReminders == 0 ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button class="btn btn-sm btn-primary" id="saveChangesBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(document).ready(function() {

        $("#saveChangesBtn").click(async function() {
            let sendEmailReminders = $("#sendEmailRemindersSelect").val();
            
            let formData = new FormData();
            formData.append('sendEmailReminders', sendEmailReminders);

            let response = await ajaxCall({
                url: window.baseUrl + 'admin/preferences/save',
                data: formData,
                csrfHeader: '<?= csrf_header() ?>',
                csrfHash: '<?= csrf_hash() ?>'
            });

            if (response.status == 'success') {
                new Notify({
                    title: 'Success',
                    text: 'Changes saved successfully',
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
        });
    });
</script>
<?= $this->endSection() ?>