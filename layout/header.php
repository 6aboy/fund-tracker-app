<?php require_once __DIR__ . '/../auth/session.php'; require_auth(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Tracker</title>

    <link rel="apple-touch-icon" sizes="180x180" href="/fund_tracker/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/fund_tracker/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/fund_tracker/assets/favicon-16x16.png">
    <link rel="manifest" href="/fund_tracker/assets/site.webmanifest">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>

    <!-- DataTables CSS -->
    <link
        href="https://cdn.datatables.net/v/dt/moment-2.29.4/jszip-3.10.1/dt-2.3.5/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/r-3.0.7/sl-3.1.3/datatables.min.css"
        rel="stylesheet">

    <!-- pdfmake (optional for exports) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" crossorigin="anonymous"></script>

    <!-- DataTables JS (after jQuery) -->
    <script
        src="https://cdn.datatables.net/v/dt/moment-2.29.4/jszip-3.10.1/dt-2.3.5/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/r-3.0.7/sl-3.1.3/datatables.min.js"
        crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/fund_tracker/assets/css/bootstrap.min.css">


    <link rel="stylesheet" href="/fund_tracker/assets/css/app.css">
    <script src="/fund_tracker/assets/js/tables.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.navbar .nav-link').forEach(link => {
            if (link.pathname === location.pathname) { link.classList.add('active'); link.setAttribute('aria-current', 'page'); }
        });
        document.querySelector('.navbar-toggler')?.addEventListener('click', function () {
            const menu = document.getElementById('navbarNav');
            const open = menu.classList.toggle('show');
            this.setAttribute('aria-expanded', String(open));
        });
    });
    </script>
</head>

<body>
    <nav id="navigationBar" class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm"
        style="z-index:1030;">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="/fund_tracker/index.php"><span class="brand-mark" aria-hidden="true">F</span> Fund Tracker</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item mx-1">
                        <a class="nav-link" href="/fund_tracker/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item mx-1">
                        <a class="nav-link" href="/fund_tracker/views/index.php">Transactions</a>
                    </li>
                    <li class="nav-item mx-1"><a class="nav-link" href="/fund_tracker/views/analytics.php">Analytics</a></li>
                    <li class="nav-item nav-account"><span class="account-name"><?= htmlspecialchars($_SESSION['user']['username']) ?></span><form action="/fund_tracker/logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><button class="btn btn-secondary" type="submit">Sign out</button></form></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sticky-navbar-placeholder"></div>