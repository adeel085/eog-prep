<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Questions</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="modal" id="topicsModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Associated Topics</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
                <p class="text-muted">Select the topics you want to associate with this question.</p>
				<div class="form-group">
                    <?php foreach ($topics as $topic) : ?>
                        <?php
                        if ($topic['owner_id'] != $user['id']) {
                            continue;
                        }
                        ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="topic-<?= $topic['id'] ?>" value="<?= $topic['id'] ?>">
                            <label class="form-check-label" for="topic-<?= $topic['id'] ?>"><?= $topic['name'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <input type="hidden" id="questionId_topicsModal" value="">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="topicsSaveBtn">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end align-items-center mb-4" style="gap: 10px;">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#packageImportModal">
                        <i class="fa fa-file-archive"></i> Import Problem Package
                    </button>

                    <a href="<?= base_url('/admin/questions/new') ?>" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i> New Question
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Question Type</th>
                                <th style="max-width: 200px;">Topics</th>
                                <th>Question</th>
                                <th>Grade</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $question) : ?>
                                <tr data-id="<?= $question['id'] ?>" data-topics='<?= json_encode($question['topics']) ?>'>
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
                                    <td><?= esc(strip_tags($question['question_html'])) ?>
                                        <?php foreach (array_filter(explode('|', $question['tags'] ?? '')) as $tag): ?>
                                            <span class="badge badge-light border"><?= esc($tag) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($question['grade']) {
                                            echo $question['grade']['name'];
                                        }
                                        else {
                                            echo '<span class="text-muted">No grade associated</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end table-action-btn" style="gap: 10px;">
                                            <?php
                                            if ($question['owner_id'] == $user['id']) {
                                                ?>
                                                <a href="javascript:void(0)" class="table-action-btn add-to-basket-btn" data-toggle="tooltip" title="Add to Basket">
                                                    <i class="fa fa-shopping-bag"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="table-action-btn topics-btn" data-toggle="tooltip" title="Associated Topics">
                                                    <i class="fa fa-book"></i>
                                                </a>
                                                <a href="<?= base_url('/admin/questions/edit/' . $question['id']) ?>" class="table-action-btn" data-toggle="tooltip" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="table-action-btn delete-btn" data-toggle="tooltip" title="Delete">
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

<?= $this->include('admin/problem_package_modal') ?>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script>window.packageToolsConfig = <?= json_encode(['csrfHeader' => csrf_header(), 'csrfHash' => csrf_hash(), 'csrfCookie' => config('Cookie')->prefix . config('Security')->cookieName], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= base_url('public/assets/js/admin-package-tools.js') ?>"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML"></script>
<script>
    $(() => {

        $(".add-to-basket-btn").click(function(e) {
            
            let questionId = $(this).closest("tr").attr("data-id");

            addQuestionToBasket(questionId);
        });

        $("#topicsSaveBtn").click(async function() {

            let questionId = $("#questionId_topicsModal").val();
            let topics = [];

            $("input[type='checkbox']:checked").each(function() {
                topics.push($(this).val());
            });

            $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

            let formData = new FormData();
            formData.append("questionId", questionId);
            formData.append("topicsIds", topics.join(','));

            try {
                let res = await ajaxCall({
                    url: baseUrl + 'admin/questions/updateTopics',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (res.status == 'success') {
                    new Notify({
                        title: 'Success',
                        text: 'Topics updated successfully',
                        status: 'success',
                        autoclose: true,
                        autotimeout: 3000
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
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

            // Reset save button
            $("#topicsSaveBtn").html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $(".topics-btn").click(function() {

            $("#topicsModal").find("input[type='checkbox']").prop("checked", false);

            let questionId = $(this).closest("tr").data("id");
            let questionTopics = $(this).closest("tr").data("topics");

            questionTopics.forEach(topic => {
                $("#topic-" + topic['topic_id']).prop("checked", true);
            });

            $("#questionId_topicsModal").val(questionId);

            $("#topicsModal").modal("show");
        });

        $(".delete-btn").click(async function() {

            if (!confirm("Are you sure you want to delete this question?")) {
                return;
            }

            let questionId = $(this).closest("tr").data("id");

            try {
                let formData = new FormData();
                formData.append('questionId', questionId);

                let res = await ajaxCall({
                    url: baseUrl + 'admin/questions/delete',
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