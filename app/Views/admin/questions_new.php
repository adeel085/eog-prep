<?= $this->extend('admin/Layouts/default') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="<?= base_url('/public/libs/ckeditor/samples/css/samples.css') ?>">
<link rel="stylesheet" href="<?= base_url('/public/libs/ckeditor/samples/toolbarconfigurator/lib/codemirror/neo.css') ?>">
<style>
    .mcq-answer-input-wrapper .option-editor + div {
        flex: 1;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">New Question</h4>

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
                            <label for="topic">Topic</label>
                            <select class="form-control" id="topic" name="topic">
                                <?php foreach ($topics as $topic) : ?>
                                    <option value="<?= $topic['id'] ?>"><?= $topic['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="difficulty">Difficulty Level</label>
                            <select class="form-control" id="difficulty">
                                <option value="1">Level 1 - Easy</option>
                                <option value="2">Level 2 - Medium</option>
                                <option value="3">Level 3 - Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="questionType">Question Type</label>
                            <select class="form-control" id="questionType">
                                <option value="text">Text Entry</option>
                                <option value="mcq">Multiple Choice</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="grade">Grade</label>
                            <select class="form-control" id="grade">
                                <?php foreach ($grades as $grade) : ?>
                                    <option value="<?= $grade['id'] ?>"><?= $grade['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="question">Question</label>
                            <div id="questionEditor"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="solution">Solution Explanation</label>
                            <div id="solutionEditor"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="answer">Answer</label>
                            <div>
                                <!-- Text Answer Inputs -->
                                <div id="textAnswerInputsWrapper" class="answer-inputs-wrapper">
                                    <div class="d-flex flex-column answer-inputs-wrapper-inner" style="gap: 10px;">
                                        <div class="d-flex text-answer-input-wrapper" style="gap: 10px;">
                                            <input type="text" class="form-control text-answer-input">
                                            <button id="textAnswerAddBtn" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- MCQ Answer Inputs -->
                                <div id="mcqAnswerInputWrapper" class="answer-inputs-wrapper" style="display: none;">
                                    <div class="d-flex flex-column answer-inputs-wrapper-inner" style="gap: 10px;">
                                        <div class="d-flex align-items-center mcq-answer-input-wrapper" style="gap: 10px;">
                                            <div id="option1Editor" class="option-editor"></div>
                                            <div class="d-flex">
                                                <input type="radio" class="mcq-answer-radio" name="correct-mcq-answer">
                                            </div>
                                            <button id="mcqAnswerAddBtn" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-sm btn-primary" id="saveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('foot') ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML"></script>
<script src="<?= base_url('/public/libs/ckeditor/ckeditor.js') ?>"></script>
<script>
    if (CKEDITOR.env.ie && CKEDITOR.env.version < 9)
        CKEDITOR.tools.enableHtml5Elements(document);

    // The trick to keep the editor in the sample quite small
    // unless user specified own height.
    CKEDITOR.config.height = 150;
    CKEDITOR.config.width = 'auto';
    // CKEDITOR.config.extraPlugins = 'forms';
    CKEDITOR.config.mathJaxLib = '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML';

    var initSample = (function() {
        var wysiwygareaAvailable = isWysiwygareaAvailable();

        return function(editorId) {
            var editorElement = CKEDITOR.document.getById(editorId);

            // Depending on the wysiwygarea plugin availability initialize classic or inline editor.
            if (wysiwygareaAvailable) {
                CKEDITOR.replace(editorId);
            } else {
                editorElement.setAttribute('contenteditable', 'true');
                CKEDITOR.inline(editorId);
            }
        };

        function isWysiwygareaAvailable() {
            // If in development mode, then the wysiwygarea must be available.
            // Split REV into two strings so builder does not replace it :D.
            if (CKEDITOR.revision == ('%RE' + 'V%')) {
                return true;
            }

            return !!CKEDITOR.plugins.get('wysiwygarea');
        }
    })();

    initSample("questionEditor");
    initSample("solutionEditor");

    let optionEditorNumber = 1;

    $(() => {

        setTimeout(() => {
            CKEDITOR.config.height = 80;

            $(".option-editor").each((index, item) => {
                initSample($(item).attr("id"));
            });
        }, 500);

        $("#saveBtn").click(async function(e) {
            
            let topicId = $("#topic").val();
            let difficulty = $("#difficulty").val();
            let questionType = $("#questionType").val();
            let gradeId = $("#grade").val();
            let question = CKEDITOR.instances["questionEditor"].getData();
            let solution = CKEDITOR.instances["solutionEditor"].getData();
            let answers = [];
            let correctAnswerIndex = -1;

            if (!topicId) {
                new Notify({
                    title: 'Error',
                    text: 'Please select a topic',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            if (!gradeId) {
                new Notify({
                    title: 'Error',
                    text: 'Please select a grade',
                    status: 'error',
                    autoclose: true,
                    autotimeout: 3000
                });
                return;
            }

            if (questionType == "text") {

                let foundEmpty = false;

                $("#textAnswerInputsWrapper .text-answer-input-wrapper").each(function(index, input) {
                    let answer = $(this).find(".text-answer-input").val().trim();

                    if (answer == '') {
                        foundEmpty = true;
                        return false;
                    }

                    answers.push(answer);
                });

                if (foundEmpty) {
                    new Notify({
                        title: 'Error',
                        text: 'An empty answer is not allowed',
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });
                    return;
                }
            }
            else {
                $("#mcqAnswerInputWrapper .mcq-answer-input-wrapper").each(function(index, input) {

                    if ($(this).find(".mcq-answer-radio").prop("checked")) {
                        correctAnswerIndex = index;
                    }

                    let optionEditorId = $(input).find(".option-editor").attr("id");
                    answers.push(CKEDITOR.instances[optionEditorId].getData());
                });

                if (correctAnswerIndex == -1) {
                    new Notify({
                        title: 'Error',
                        text: 'Please select a correct answer',
                        status: 'error',
                        autoclose: true,
                        autotimeout: 3000
                    });
                    return;
                }
            }

            try {
                let formData = new FormData();
                formData.append('topicId', topicId);
                formData.append('difficulty', difficulty);
                formData.append('gradeId', gradeId);
                formData.append('questionType', questionType);
                formData.append('question', question);
                formData.append('solution', solution);
                formData.append('answers', base64EncodeUnicode(JSON.stringify(answers)));
                formData.append('correctAnswerIndex', correctAnswerIndex);

                $(this).attr('data-content', $(this).html()).html('<i class="fa fa-spinner fa-spin"></i>').css('pointer-events', 'none');

                let response = await ajaxCall({
                    url: baseUrl + '/admin/questions/saveNew',
                    data: formData,
                    csrfHeader: '<?= csrf_header() ?>',
                    csrfHash: '<?= csrf_hash() ?>'
                });

                if (response.status == 'success') {
                    window.location.href = baseUrl + 'admin/questions';
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
            $('#saveBtn').html($(this).attr('data-content')).css('pointer-events', 'auto');
        });

        $("#questionType").change(function(e) {
            $(".answer-inputs-wrapper").hide();

            if ($(this).val() == "mcq") {
                $("#mcqAnswerInputWrapper").show();
            }
            else {
                $("#textAnswerInputsWrapper").show();
            }
        });

        $("#textAnswerAddBtn").click(function(e) {
            $("#textAnswerInputsWrapper .answer-inputs-wrapper-inner").append(
                `<div class="d-flex text-answer-input-wrapper" style="gap: 10px;">
                    <input type="text" class="form-control text-answer-input">
                    <button class="btn btn-sm btn-outline-primary remove-text-answer-btn">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>`);
        });

        $(document).on("click", ".remove-text-answer-btn", function(e) {
            $(this).closest(".text-answer-input-wrapper").remove();
        });

        $("#mcqAnswerAddBtn").click(function(e) {

            optionEditorNumber++;

            $("#mcqAnswerInputWrapper .answer-inputs-wrapper-inner").append(
                `<div class="d-flex align-items-center mcq-answer-input-wrapper" style="gap: 10px;">
                    <div id="option${optionEditorNumber}Editor" class="option-editor"></div>
                    <div class="d-flex">
                        <input type="radio" class="mcq-answer-radio" name="correct-mcq-answer">
                    </div>
                    <button class="btn btn-sm btn-outline-primary remove-mcq-answer-btn">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>`);
            
            initSample(`option${optionEditorNumber}Editor`);
        });

        $(document).on("click", ".remove-mcq-answer-btn", function(e) {
            $(this).closest(".mcq-answer-input-wrapper").remove();
        });
    });

    function base64EncodeUnicode(str) {
        return btoa(
            new TextEncoder().encode(str).reduce((data, byte) => data + String.fromCharCode(byte), '')
        );
    }

    function base64DecodeUnicode(str) {
        return new TextDecoder().decode(Uint8Array.from(atob(str), c => c.charCodeAt(0)));
    }
</script>
<?= $this->endSection() ?>