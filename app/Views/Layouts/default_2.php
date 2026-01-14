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

    <link rel="stylesheet" href="<?= base_url('public/assets/css/style.css') ?>">

    <script>
        window.baseUrl = "<?= base_url() ?>";

        const appHeight = () => {
            const doc = document.documentElement;
            doc.style.setProperty('--app-height', `${window.innerHeight}px`);
        }
        window.addEventListener('resize', appHeight);
        appHeight();
    </script>

    <style>
        body {
            background-color: #eee;
        }

        .bg-light {
            background-color: #fff !important;
        }

        .navbar-wrapper {
            background-color: #fff;
        }
    </style>

    <?= $this->renderSection('head') ?>
</head>

<body>
        
    <div class="navbar-wrapper">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-md bg-light navbar-light">
                <!-- Brand -->
                <a class="navbar-brand" href="<?= base_url('/') ?>">
                    <!-- <img src="<?= base_url('public/assets/images/Transparent_Logo.png') ?>" alt="Logo" style="width: 100px; max-width: 100%;"> -->
                    <span class="navbar-brand-text">EOG Prep</span>
                </a>

                <!-- Toggler/collapsibe Button -->
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navbar links -->
                <div class="collapse navbar-collapse" id="collapsibleNavbar">
                    <ul class="navbar-nav">
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li> -->
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    
    <?= $this->renderSection('content') ?>

    <!-- Simple Notify -->
    <script src="<?= base_url('public/libs/simplenotify/js/simple-notify.min.js') ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?= base_url('public/assets/js/functions.js') ?>"></script>

    <?= $this->renderSection('foot') ?>
</body>

</html>