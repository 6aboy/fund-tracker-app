<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Tracker</title>

    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">
    <link rel="manifest" href="assets/site.webmanifest">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>

    <!-- DataTables CSS -->
    <link
        href="https://cdn.datatables.net/v/ju/moment-2.29.4/jszip-3.10.1/dt-2.3.5/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/r-3.0.7/sl-3.1.3/datatables.min.css"
        rel="stylesheet">

    <!-- pdfmake (optional for exports) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" crossorigin="anonymous"></script>

    <!-- DataTables JS (after jQuery) -->
    <script
        src="https://cdn.datatables.net/v/ju/moment-2.29.4/jszip-3.10.1/dt-2.3.5/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/r-3.0.7/sl-3.1.3/datatables.min.js"
        crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
        /* Adjust the height if your navbar is taller than 56px */
        .sticky-navbar-placeholder {
            height: 56px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('#transactionForm') || document.querySelector('form[name="transactionForm"]');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Successfully created',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 800,
                    timerProgressBar: true
                }).then(() => form.submit());
            });
        });
    </script>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary sticky-top shadow-sm" style="z-index:1030;">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="/fund_tracker/index.php">Fund Tracker</a>

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
            </ul>
        </div>
    </div>
</nav>

<div class="sticky-navbar-placeholder"></div>