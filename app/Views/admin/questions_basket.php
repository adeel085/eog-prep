<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Questions Basket</h4>

            <div class="page-title-right">

            </div>

        </div>
    </div>
</div>

<div class="modal" id="createTopicModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Create Topic</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="tutorialLink">Tutorial Link (YouTube)</label>
                            <input type="text" class="form-control" id="tutorialLink">
                        </div>
                    </div>
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="createTopicSaveBtn">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end align-items-center mb-4" style="gap: 10px;">
                    <a href="javascript:void(0)" class="btn btn-sm btn-primary" id="createTopicBtn" style="display: none;">
                        Create Topic
                    </a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-dark" id="emptyBasketBtn">
                        Empty Basket
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" id="questionsBasketTable">
                        <thead>
                            <tr>
                                <th>Question</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4" id="emptyBasketMessage">
                    <p class="text-muted">Your Questions Basket is empty</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML"></script>
<script>
    $(() => {

        function loadQuestionsBasket() {
            let questionsBasket = localStorage.getItem('questionsBasket') || '[]';
            questionsBasket = JSON.parse(questionsBasket);

            if (questionsBasket.length > 0) {
                $("#emptyBasketMessage").hide();
                $("#questionsBasketTable").show();

                // Fetch question details
                $.ajax({
                    url: '/admin/questions/get-details',
                    method: 'POST',
                    data: {
                        questionIds: questionsBasket.join(',')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            displayQuestions(response.questions, questionsBasket);
                        } else {
                            console.error('Error fetching questions:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });

                $("#createTopicBtn").show();
            }
            else {
                $("#emptyBasketMessage").show();
                $("#questionsBasketTable").hide();

                $("#createTopicBtn").hide();
            }
        }

        function displayQuestions(questions, basketIds) {
            const tbody = $('#questionsBasketTable tbody');
            tbody.empty();

            questions.forEach(question => {
                const row = `
                    <tr>
                        <td>${question.question_html}</td>
                        <td>
                            <button class="btn btn-sm btn-danger remove-from-basket"
                                    data-question-id="${question.id}">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Add event listeners for remove buttons
            $('.remove-from-basket').on('click', function() {
                const questionId = parseInt($(this).data('question-id'));
                removeQuestionFromBasket(questionId);
                // Reload the basket after removing
                setTimeout(() => {
                    loadQuestionsBasket();
                }, 100);
            });

            // Force MathJax to re-render the newly loaded content
            MathJax.Hub.Queue([
                "Typeset",
                MathJax.Hub,
                $("#questionsBasketTable").get(0),
            ]);
        }

        $("#createTopicBtn").click(function() {
            $("#createTopicModal").modal('show');
        });

        $("#createTopicSaveBtn").click(async function() {
            let name = $("#name").val();
            let tutorialLink = $("#tutorialLink").val();

            if (!name) {
                new Notify({
                    title: 'Error',
                    text: 'Please enter a topic name',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            let questionsBasket = localStorage.getItem('questionsBasket') || '[]';
            questionsBasket = JSON.parse(questionsBasket);

            let questionIds = questionsBasket.join(',');

            try {
                let formData = new FormData();
                formData.append('name', name);
                formData.append('tutorialLink', tutorialLink);
                formData.append('questionIds', questionIds);

                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let response = await ajaxCall({
                    url: baseUrl + '/admin/topics/saveNew',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (response.status == 'success') {
                    window.location.href = baseUrl + 'admin/topics';
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
            $('#createTopicSaveBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        // Empty basket functionality
        $('#emptyBasketBtn').on('click', function() {
            if (confirm('Are you sure you want to empty the questions basket?')) {
                localStorage.setItem('questionsBasket', '[]');
                $("#questionsBaskerItemsCounter").html('0');
                $("#questionsBaskerItemsCounter").hide();

                new Notify({
                    title: 'Success',
                    text: 'Questions basket emptied successfully',
                    status: 'success',
                    autoclose: true,
                    autotimeout: 1000
                });

                loadQuestionsBasket();
            }
        });

        // Load questions on page load
        loadQuestionsBasket();
    });
</script>
<?= $this->endSection() ?>