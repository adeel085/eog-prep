<?= $this->extend('Layouts/default_2') ?>

<?= $this->section('head') ?>
<style>
    .question-center {
        width: 600px;
        max-width: 100%;
        margin: auto;
    }
    .stars-container {
        display: flex;
        justify-content: center;
        gap: 6px;
        font-size: 32px;
        color: #c0c0c0;
    }
    .notify.notify-autoclose::before {
        display: none;
    }
    .mcq-option-wrapper p {
        margin-bottom: 5px;
    }
    .correct-answer-section .correct-answer-display {
        font-size: 1.1em;
        color: #28a745;
        border-left: 4px solid #28a745 !important;
    }
    .solution-explanation .explanation-content {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        border-left: 4px solid #007bff;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="question-center">

                <!-- Back button -->
                <div class="d-flex justify-content-start mb-3">
                    <a href="<?= base_url('page-selection') ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Quit Session
                    </a>
                </div>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><?= $currentTopic['name'] ?></span>
                            <span>Level <?= $currentLevel ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        <div id="questionsWrapper">

                        </div>

                        <div class="question-footer d-flex justify-content-end mt-3">
                            <button class="btn btn-primary next-question" id="submitBtn">Submit</button>
                        </div>

                        <div class="question-solution mt-3" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Solution</h5>
                                    <div class="solution-content"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-primary next-question" id="nextQuestionBtn">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML"></script>
<script>
    let questions = <?= json_encode($questions) ?>;
    let currentQuestionIndex = -1;
    let correctAnswers = 0;
    let totalQuestions = <?= count($questions) ?>;

    $(document).ready(async function() {

        showNextQuestion();

        $("#submitBtn").click(async function() {
            
            let questionType = $("#questionType").val();
            let questionId = $("#questionId").val();
            let selectedAnswer = undefined;

            if (questionType == 'mcq') {
                selectedAnswer = $('input[name="answer"]:checked').val();
            }
            else {
                selectedAnswer = $('.question-item').find('.text-answer').val().trim();

            }

            if (selectedAnswer == undefined || selectedAnswer == '') {
                new Notify({
                    title: 'Warning',
                    text: 'Please select an answer',
                    status: 'warning',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            let correct = false;

            questions[currentQuestionIndex].answers.forEach(answer => {
                if (answer.is_correct == 1 && (questionType == 'mcq' ? answer.answer == base64DecodeUnicode(selectedAnswer) : answer.answer == selectedAnswer)) {
                    correct = true;
                }
            });

            if (correct) {
                correctAnswers++;
                new Notify({
                    title: 'Success',
                    text: 'Correct answer',
                    status: 'success',
                    autoclose: true,
                    autotimeout: 3000
                });
                // Move to next question automatically when correct
                showNextQuestion();
            }
            else {
                new Notify({
                    title: 'Error',
                    text: 'Incorrect answer',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                // Show the correct answer and solution
                showCorrectAnswerAndSolution();
            }
        });

        $(document).on('keyup', '.text-answer', function() {
            // If Enter key is pressed
            if (event.keyCode == 13) {
                if ($('#submitBtn').attr('data-disabled') == "true") {
                    return;
                }
                $('#submitBtn').click();
            }
        });

        $('#nextQuestionBtn').click(async function() {
            showNextQuestion();
        });
    });

    function showNextQuestion() {

        currentQuestionIndex++;

        $('.question-solution').hide();
        $('.question-solution .solution-content').html('');
        $('#submitBtn').removeAttr('data-disabled');
        $('#submitBtn').show(); // Make sure submit button is visible
        $('#submitBtn').css({
            'pointer-events': 'none',
            'opacity': 0.5
        });

        $('.question-item').hide();

        let question = getQuestion();

        if (question == null) {
            // Session completed - store results and show them
            storeSessionResults();
            return;
        }

        renderQuestion(question);

        $('#submitBtn').css({
            'pointer-events': 'auto',
            'opacity': 1
        });
    }

    function renderQuestion(question) {
        $('#questionsWrapper').html(`
            <div class="question-item" data-id="questionid" style="display: block;">
                <h5 class="card-title">${question.question_html}</h5>

                <div class="answer-area">
                    ${(question.question_type == "text") ? `
                        <div class="answer-item">
                            <input type="text" class="form-control text-answer" autofocus>
                        </div>
                    ` : `
                        ${question.answers.map((answer, index) => `
                            <div class="answer-item mcq-option-wrapper">
                                <label class="d-flex align-items-center" style="gap: 10px;">
                                    <input type="radio" name="answer" class="answer-radio" value='${base64EncodeUnicode(answer.answer)}'>
                                    ${answer.answer}
                                </label>
                            </div>
                        `).join('')}
                    `}
                </div>

                <input type="hidden" id="questionType" value="${question.question_type}">
                <input type="hidden" id="questionId" value="${question.id}">
            </div>
        `);

        $('.text-answer').focus();

        // Force MathJax to re-render the newly loaded content
        MathJax.Hub.Queue([
            "Typeset",
            MathJax.Hub,
            $("#questionsWrapper").get(0),
        ]);
    }

    function getQuestion() {
        if (currentQuestionIndex >= questions.length) {
            return null;
        }

        return questions[currentQuestionIndex];
    }

    function base64EncodeUnicode(str) {
        return btoa(
            new TextEncoder().encode(str).reduce((data, byte) => data + String.fromCharCode(byte), '')
        );
    }

    function base64DecodeUnicode(str) {
        return new TextDecoder().decode(Uint8Array.from(atob(str), c => c.charCodeAt(0)));
    }

    function showCorrectAnswerAndSolution() {
        const question = questions[currentQuestionIndex];
        
        // Find the correct answer
        let correctAnswer = '';
        question.answers.forEach(answer => {
            if (answer.is_correct == 1) {
                correctAnswer = answer.answer;
            }
        });

        // Build the solution content
        let solutionContent = `
            <div class="correct-answer-section mb-3">
                <h6 class="text-success"><strong>Correct Answer:</strong></h6>
                <div class="correct-answer-display p-2 bg-light border rounded">
                    ${correctAnswer}
                </div>
            </div>
        `;

        // Add solution explanation if available
        if (question.solution_html && question.solution_html.trim() !== '') {
            solutionContent += `
                <div class="solution-explanation">
                    <h6><strong>Explanation:</strong></h6>
                    <div class="explanation-content">
                        ${question.solution_html}
                    </div>
                </div>
            `;
        }

        // Update and show the solution section
        $('.question-solution .solution-content').html(solutionContent);
        $('.question-solution').show();

        // Hide submit button, the next button in solution section will be used
        $('#submitBtn').hide();

        // Re-render MathJax for any math content in the solution
        MathJax.Hub.Queue([
            "Typeset",
            MathJax.Hub,
            $(".question-solution").get(0),
        ]);
    }

    async function storeSessionResults() {
        try {

            let formData = new FormData();
            formData.append('topic_id', <?= $currentTopic['id'] ?>);
            formData.append('level', <?= $currentLevel ?>);
            formData.append('correct_count', correctAnswers);
            formData.append('total_questions', totalQuestions);

            const result = await ajaxCall({
                url: baseUrl + '/home/store-session-result',
                data: formData,
                csrfHeader: '<?= csrf_header() ?>',
                csrfHash: '<?= csrf_hash() ?>'
            });

            if (result.status === 'success') {
                showSessionResults(result.data);
            }
            else {
                console.error('Failed to store session results:', result.message);
                // Still show results even if storage failed
                showSessionResults({
                    correct_answers: correctAnswers,
                    total_questions: totalQuestions,
                    percentage: Math.round((correctAnswers / totalQuestions) * 100 * 100) / 100
                });
            }
        } catch (error) {
            console.error('Error storing session results:', error);
            // Show results anyway
            showSessionResults({
                correct_answers: correctAnswers,
                total_questions: totalQuestions,
                percentage: Math.round((correctAnswers / totalQuestions) * 100 * 100) / 100
            });
        }
    }

    function showSessionResults(data) {
        // Hide the question area
        $('.question-center').hide();

        // Show results
        const resultsHtml = `
            <div class="container-fluid mt-5">
                <div class="row">
                    <div class="col-12">
                        <div class="question-center">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h2 class="card-title mb-4">Session Complete!</h2>

                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h4 class="card-title text-success">${data.correct_answers}</h4>
                                                    <p class="card-text">Correct Answers</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h4 class="card-title text-primary">${data.total_questions}</h4>
                                                    <p class="card-text">Total Questions</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h4 class="card-title text-info">${data.percentage}%</h4>
                                                    <p class="card-text">Score</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="stars-container mb-4">
                                        ${getStarsHtml(data.correct_answers, data.total_questions)}
                                    </div>

                                    <p class="text-muted">Redirecting to page selection in a few seconds...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(resultsHtml);

        // Redirect after 5 seconds
        setTimeout(() => {
            window.location.href = baseUrl + 'page-selection';
        }, 5000);
    }

    function getStarsHtml(correct, total) {
        const percentage = (correct / total) * 100;
        let stars = 0;

        if (percentage >= 90) stars = 5;
        else if (percentage >= 75) stars = 4;
        else if (percentage >= 60) stars = 3;
        else if (percentage >= 40) stars = 2;
        else stars = 1;

        let starsHtml = '';
        for (let i = 0; i < 5; i++) {
            if (i < stars) {
                starsHtml += '<span style="color: #ffd700;">★</span>';
            } else {
                starsHtml += '<span style="color: #c0c0c0;">★</span>';
            }
        }
        return starsHtml;
    }

    if ('BroadcastChannel' in window) {
        const channel = new BroadcastChannel('tab-communication');
        const TAB_ID = Date.now().toString(); // Unique identifier for this tab
        const PAGE_ID = 'student-home-page';

        // Function to handle incoming messages
        channel.onmessage = (event) => {
            const { type, tabId, pageId } = event.data;

            if (pageId !== PAGE_ID) {
                console.log('Message ignored. Not intended for this page.');
                return;
            }

            if (type === 'NEW_TAB_OPENED' && tabId !== TAB_ID) {
                document.body.innerHTML = `
                    <div class="container pt-4">
                        <h3>This tab is now deactivated.</h3>
                        <p>You have opened this page in another tab.</p>
                    </div>
                `;
            }
        };

        // Notify other tabs that this tab is active
        function notifyNewTab() {
            channel.postMessage({
                type: 'NEW_TAB_OPENED',
                tabId: TAB_ID,
                pageId: PAGE_ID
            });
        }

        // Notify other tabs on page load
        notifyNewTab();

        // Clean up when the tab is closed
        window.addEventListener('beforeunload', () => {
            channel.close();
        });
    }
    else {
        console.warn('BroadcastChannel is not supported in this browser. Tab communication will not work.');
    }
</script>
<?= $this->endSection() ?>