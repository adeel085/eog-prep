<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Edit Topic</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" value="<?= $topic['name'] ?>">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="tutorialLink">Tutorial Link (YouTube)</label>
                            <input type="text" class="form-control" id="tutorialLink" value="<?= $topic['tutorial_link'] ?>">
                        </div>
                    </div>

                    <input type="hidden" id="id" value="<?= $topic['id'] ?>">
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-sm btn-primary" id="saveBtn">Update</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {
        $('#saveBtn').click(async function(e) {
            
            let id = $('#id').val();
            let name = $('#name').val().trim();
            let tutorialLink = $('#tutorialLink').val().trim();

            if (!name) {
                return alert('Please fill all the fields');
            }

            try {
                let formData = new FormData();

                formData.append('id', id);
                formData.append('name', name);
                formData.append('tutorialLink', tutorialLink);
                
                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let response = await ajaxCall({
                    url: baseUrl + '/admin/topics/update',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (response.status == 'success') {
                    window.location.reload();
                    return;
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
            } catch (error) {
                new Notify({
                    title: 'Error',
                    text: error.responseJSON.message || 'Something went wrong',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }

            // Reset save button
            $('#saveBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
        });
    });
</script>
<?= $this->endSection() ?>