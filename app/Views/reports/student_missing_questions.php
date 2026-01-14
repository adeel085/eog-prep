<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= "Incorrect Attempts Report" . ' | ' . APP_NAME ?></title>

    <!-- favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('public/assets/images/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('public/assets/images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('public/assets/images/favicon-16x16.png') ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .answer-item p {
            margin-bottom: 0;
        }
    </style>
</head>
<body>

    <div class="container py-5">
        <h3 class="mb-1">Incorrect Attempts Report</h3>
        <span class="d-block mb-3" style="font-size: 18px;"><?= $startDate . ' - ' . $endDate ?></span>
        
        <p class="text-muted"><b>Student Name:</b> <?= $student['full_name'] ?></p>

        <p class="mt-3">Your child has not answered the following questions correctly. Please help them to work on these questions.</p>

        <div class="mt-4">
            <?php foreach ($missingQuestions as $question) : ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?= $question['question_html'] ?></h5>
                        <?php
                            foreach ($question['student_answers'] as $answer) {
                                ?>
                                <div class="d-flex align-items-center mb-1 answer-item" style="gap: 10px;">
                                    <span class="text-danger">❌</span>
                                    <div style="display: inline-block;margin: 0;background: #dcdcdc;padding: 1px;white-space: pre;"><?= $answer['student_answer'] ?></div>
                                </div>
                                <div class="mb-3">
                                    <span style="font-size: 15px;" class="text-muted">[<?= date('M d, Y h:i A', strtotime($answer['created_at'])) ?>]</span>
                                </div>
                                <?php
                            }
                        ?>
                        <div class="d-flex align-items-center mb-3 answer-item" style="gap: 10px;">
                            <span class="text-success">✅</span>
                            <div style="display: inline-block;margin: 0;background: #dcdcdc;padding: 1px;white-space: pre;"><?= $question['correct_answer'] ?></div>
                        </div>
                        <p class="card-text text-muted">Incorrect Attempts: <?= $question['incorrect_count'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script src="//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.4/MathJax.js?config=TeX-AMS_HTML"></script>
</body>
</html>