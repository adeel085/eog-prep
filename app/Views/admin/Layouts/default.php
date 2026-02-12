<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME ?></title>

    <!-- favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('public/assets/images/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('public/assets/images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('public/assets/images/favicon-16x16.png') ?>">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Simple Notify -->
    <link rel="stylesheet" href="<?= base_url('public/libs/simplenotify/css/simple-notify.min.css') ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="<?= base_url('public/assets/css/admin.css?v=' . CSS_VERSION) ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/style.css?v=' . CSS_VERSION) ?>">

    <script>
        window.baseUrl = "<?= base_url() ?>";

        const appHeight = () => {
            const doc = document.documentElement;
            doc.style.setProperty('--app-height', `${window.innerHeight}px`);
            doc.style.setProperty('--admin-main-content-height', `${window.innerHeight - 70}px`);
        }
        window.addEventListener('resize', appHeight);
        appHeight();
    </script>

    <?= $this->renderSection('head') ?>
</head>

<body class="navbar-open">
    
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">

                    <div class="d-flex">
                        <div class="header-item">
                            <div class="topnav-hamburger" id="topnav-hamburger-icon">
                                <div class="hamburger-icon">
                                    <div class="bar arrow-top"></div>
                                    <div class="bar arrow-middle"></div>
                                    <div class="bar arrow-bottom"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">

                        <?php
                        if ($user['user_type'] == 'admin') {
                            ?>
                            <div class="header-item">
                                <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" title="Questions Basket" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-shopping-bag" style="font-size: 18px;"></i>
                                    <div id="questionsBaskerItemsCounter" style="display: none;">
                                        <span>0</span>
                                    </div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="<?= base_url('/admin/questions-basket') ?>">View Basket</a>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="header-item" style="background: #f3f3f9;">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?= $user['full_name'] ?>
                            </a>
                            <div style="right: 0; left: unset;" class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="<?= base_url('logout') ?>">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="mobile-menu-bg-overlay"></div>

        <div class="navbar-menu">
            <div class="navbar-brand-box">
                <a href="<?= base_url('/admin') ?>" style="text-decoration: none;" class="logo">
                    <img src="<?= base_url('public/assets/images/logo.jpg') ?>" alt="Logo" style="width: 122px; height: auto;">
                </a>
            </div>

            <div id="scrollbar" data-simplebar="init" class="h-100">
                <div style="padding: 13px 20px;padding-top: 0;font-size: 13px;font-weight: 600;color: #999;">
                    MENU
                </div>

                <ul id='navbar-menu-list'>
                    <li>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('/admin/dashboard') ?>">
                                <i class="fa fa-dashboard navbar-icon"></i>
                                <span>Dashboard</span>
                            </a>
                        </div>
                    </li>

                    <?php
                    if ($user['user_type'] == 'admin') {
                        ?>
                        <li>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="<?= base_url('/admin/grades') ?>">
                                    <i class="fa fa-graduation-cap navbar-icon"></i>
                                    <span>Grades</span>
                                </a>
                            </div>
                        </li>

                        <li>
                            <div class="d-flex justify-content-between align-items-center navbar-item-expandable">
                                <span>
                                    <i class="fa fa-user-tie navbar-icon"></i>Teachers
                                </span>
                                <i class="fa fa-caret-right navbar-item-expand-icon" style="transform: rotate(0deg);"></i>
                            </div>
                            <ul style="display: none;">
                                <li>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= base_url('/admin/teachers') ?>">
                                            <span>All Teachers</span>
                                        </a>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= base_url('/admin/teachers/new') ?>">
                                            <span>New Teacher</span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <div class="d-flex justify-content-between align-items-center navbar-item-expandable">
                                <span>
                                    <i class="fa fa-book navbar-icon"></i>Topics
                                </span>
                                <i class="fa fa-caret-right navbar-item-expand-icon" style="transform: rotate(0deg);"></i>
                            </div>
                            <ul style="display: none;">
                                <li>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= base_url('/admin/topics') ?>">
                                            <span>All Topics</span>
                                        </a>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= base_url('/admin/topics/new') ?>">
                                            <span>New Topic</span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <div class="d-flex justify-content-between align-items-center navbar-item-expandable">
                                <span>
                                    <i class="fa fa-question-circle navbar-icon"></i>Questions
                                </span>
                                <i class="fa fa-caret-right navbar-item-expand-icon" style="transform: rotate(0deg);"></i>
                            </div>
                            <ul style="display: none;">
                                <li>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= base_url('/admin/questions') ?>">
                                            <span>All Questions</span>
                                        </a>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= base_url('/admin/questions/new') ?>">
                                            <span>New Question</span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <?php
                    }
                    ?>

                    <li>
                        <div class="d-flex justify-content-between align-items-center navbar-item-expandable">
                            <span>
                                <i class="fa fa-user navbar-icon"></i>Students
                            </span>
                            <i class="fa fa-caret-right navbar-item-expand-icon" style="transform: rotate(0deg);"></i>
                        </div>
                        <ul style="display: none;">
                            <li>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?= base_url('/admin/students') ?>">
                                        <span>All Students</span>
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?= base_url('/admin/students/new') ?>">
                                        <span>New Student</span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('/admin/classes') ?>">
                                <i class="fa fa-users navbar-icon"></i>
                                <span>Classes</span>
                            </a>
                        </div>
                    </li>

                    <li>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= base_url('/admin/worksheets') ?>">
                                <i class="fa fa-file-alt navbar-icon"></i>
                                <span>Worksheets</span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?= $this->renderSection('content') ?>
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © <?= APP_NAME ?>.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Filling in the gaps
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

    <!-- Simple Notify -->
    <script src="<?= base_url('public/libs/simplenotify/js/simple-notify.min.js') ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?= base_url('public/assets/js/functions.js') ?>"></script>

    <script>
        $(() => {

            if ($(window).scrollTop() >= 42) {
                $("#page-topbar").addClass("topbar-shadow");
            }

            if ($(document).width() > 768) {
                $(".hamburger-icon").removeClass("open");
            } else {
                $("body").removeClass("navbar-open");
            }

            $(window).resize(() => {
                $(".hamburger-icon").removeClass("open");
            });

            $(window).scroll(() => {

                if ($(window).scrollTop() >= 42) {
                    $("#page-topbar").addClass("topbar-shadow");
                } else {
                    $("#page-topbar").removeClass("topbar-shadow");
                }
            });

            $("#topnav-hamburger-icon").click(function(e) {

                $(this).find(".hamburger-icon").toggleClass("open");
                $("body").toggleClass("navbar-open");
            });

            $(".navbar-item-expandable").click(function(e) {

                $(this).next().slideToggle("fast");

                if ($(this).hasClass("open")) {
                    $(this).find(".navbar-item-expand-icon").css("transform", "rotate(0deg)");
                    $(this).removeClass("open");
                } else {
                    $(this).find(".navbar-item-expand-icon").css("transform", "rotate(90deg)");
                    $(this).addClass("open");
                }
            });

            $(".mobile-menu-bg-overlay").click(function(e) {

                $("body").removeClass("navbar-open");
                $(".hamburger-icon").removeClass("open");
            });

            $('[data-toggle="tooltip"]').tooltip();

            let questionsBasket = localStorage.getItem('questionsBasket');
            if (questionsBasket) {
                questionsBasket = JSON.parse(questionsBasket);
                if (questionsBasket.length > 0) {
                    $("#questionsBaskerItemsCounter").html(questionsBasket.length);
                    $("#questionsBaskerItemsCounter").show();
                }
            }
        });

        function addQuestionToBasket(questionId) {

            let questionsBasket = localStorage.getItem('questionsBasket');

            if (!questionsBasket) {
                questionsBasket = [];
            }
            else {
                questionsBasket = JSON.parse(questionsBasket);
            }

            if (!questionsBasket.includes(questionId)) {
                questionsBasket.push(questionId);
                $("#questionsBaskerItemsCounter").html(questionsBasket.length);
                $("#questionsBaskerItemsCounter").show();

                new Notify({
                    title: 'Success',
                    text: 'Question added to basket successfully',
                    status: 'success',
                    autoclose: true,
                    autotimeout: 1000
                });
            }
            else {
                new Notify({
                    title: 'Information',
                    text: 'Question already in the basket',
                    status: 'info',
                    autoclose: true,
                    autotimeout: 1000
                });
            }

            localStorage.setItem('questionsBasket', JSON.stringify(questionsBasket));
        }

        function removeQuestionFromBasket(questionId) {
            let questionsBasket = localStorage.getItem('questionsBasket');
            if (questionsBasket) {
                questionsBasket = JSON.parse(questionsBasket);
                questionsBasket = questionsBasket.filter(id => id != questionId);
                console.log(questionsBasket);
                localStorage.setItem('questionsBasket', JSON.stringify(questionsBasket));
                $("#questionsBaskerItemsCounter").html(questionsBasket.length);
                $("#questionsBaskerItemsCounter").show();

                new Notify({
                    title: 'Success',
                    text: 'Question removed from basket successfully',
                    status: 'success',
                    autoclose: true,
                    autotimeout: 1000
                });
            }
            else {
                new Notify({
                    title: 'Information',
                    text: 'Question not in the basket',
                    status: 'info',
                    autoclose: true,
                    autotimeout: 1000
                });
            }
        }
    </script>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'user_logged_in'): ?>
    <script>
        new Notify({
            title: 'Welcome',
            text: 'You are logged in',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'student_created'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Student created successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'student_updated'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Student updated successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'student_deleted'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Student deleted successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'topic_created'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Topic created successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'topic_updated'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Topic updated successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'topic_deleted'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Topic deleted successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'route_updated'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Route updated successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'question_created'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Question created successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'question_updated'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Question updated successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'question_deleted'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Question deleted successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'class_created'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Class created successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'class_deleted'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Class deleted successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'class_updated'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Class updated successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'student_points_reset'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Student points reset successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'teacher_deleted'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Teacher deleted successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'teacher_created'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Teacher created successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'teacher_updated'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Teacher updated successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'teacher_registered'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Teacher account created successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'students_imported'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Students imported successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'last_year_data_cleared'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Last year data cleared successfully.',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'last_year_data_cleared_with_errors'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Last year data cleared successfully. However, some students were not deleted due to errors.',
            status: 'warning',
            autoclose: true,
            autotimeout: 4000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'grade_created'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Grade created successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'grade_deleted'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Grade deleted successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'grade_updated'): ?>
    <script>
        new Notify({
            title: 'Success',
            text: 'Grade updated successfully',
            status: 'success',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?php if (isset($flashData['status']) && $flashData['status'] == 'no_questions_found_worksheet'): ?>
    <script>
        new Notify({
            title: 'Warning',
            text: 'No questions found for the selected grade, topic and level',
            status: 'warning',
            autoclose: true,
            autotimeout: 3000
        });
    </script>
    <?php endif; ?>

    <?= $this->renderSection('foot') ?>
</body>

</html>