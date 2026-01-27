<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Questions for <?= $topic['name'] ?></h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="border-top: none;">Level</th>
                                <th style="border-top: none;">Question Type</th>
                                <th style="border-top: none; max-width: 200px;">Topics</th>
                                <th style="border-top: none;">Question</th>
                                <th style="border-top: none;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $question) : ?>
                                <tr data-id="<?= $question['id'] ?>">
                                    <td><?= $question['level'] ?></td>
                                    <td><?= $question['question_type'] ?></td>
                                    <td style="max-width: 200px;">
                                        <?php
                                        foreach ($question['topics'] as $index => $topic) {
                                            $topic_id = $topic['topic_id'];
                                            $topic_name = "";
                                            foreach ($topics as $topic) {
                                                if ($topic['id'] == $topic_id) {
                                                    $topic_name = $topic['name'];
                                                    break;
                                                }
                                            }
                                            if ($topic_name) {
                                                echo $topic_name;

                                                if ($index < count($question['topics']) - 1) {
                                                    echo ', ';
                                                }
                                            }
                                        }
                                        if (empty($question['topics'])) {
                                            echo '<span class="text-muted">No topics associated</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?= strip_tags($question['question_html']) ?></td>
                                    <td>
                                        <div class="d-flex justify-content-end table-action-btn" style="gap: 10px;">
                                            <?php
                                            if ($question['owner_id'] == $user['id']) {
                                                ?>
                                                <a href="javascript:void(0)" class="table-action-btn add-to-basket-btn" data-toggle="tooltip" title="Add to Basket">
                                                    <i class="fa fa-shopping-bag"></i>
                                                </a>
                                                <a href="<?= base_url('/admin/questions/edit/' . $question['id']) ?>" class="table-action-btn" data-toggle="tooltip" title="Edit Question">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="table-action-btn remove-btn" data-toggle="tooltip" title="Remove Question">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <?= $pager->links() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="topicId" value="<?= $topic['id'] ?>">

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>
    $(() => {

        $(".add-to-basket-btn").click(function(e) {
            
            let questionId = $(this).closest("tr").attr("data-id");

            addQuestionToBasket(questionId);
        });

        $(".remove-btn").click(async function() {

            if (!confirm("Are you sure you want to remove this question from the topic?")) {
                return;
            }

            let questionId = $(this).closest("tr").data("id");

            try {
                let formData = new FormData();
                formData.append('topicId', $("#topicId").val());
                formData.append('questionId', questionId);

                let res = await ajaxCall({
                    url: baseUrl + 'admin/topics/remove-question',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    window.location.reload();
                    return;
                }
                else {
                    new Notify({
                        title: 'Error',
                        text: res.message || 'Something went wrong',
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });
                }
            }
            catch (error) {
                if (error.status == 401) {
                    new Notify({
                        title: 'Error',
                        text: "Your session has expired. Redirecting to login page...",
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    return;
                }
                
                new Notify({
                    title: 'Error',
                    text: error.responseJSON.message || 'Something went wrong',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>