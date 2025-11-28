<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund Tracker</title>

    <!-- <script>

        document.addEventListener('DOMContentLoaded', function){
            document.querySelector('transactionForm').onsubmit = function{
                alert('Successfully Created a Transaction');
            }
        }

    </script> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // match form by id or by name
            const form = document.querySelector('#transactionForm') || document.querySelector('form[name="transactionForm"]');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                e.preventDefault(); // stop immediate navigation
                // show a simple success dialog, then submit the form
                Swal.fire({
                    title: 'Successfully created',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 800,
                    timerProgressBar: true
                }).then(() => {
                    // submit after dialog closes
                    form.submit();
                });
            });
        });
    </script>

    <script>



    </script>

</head>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<!-- keep navbar sticky and add a small CSS/placeholder so content isn't hidden -->
<style>
    /* Adjust the height if your navbar is taller than 56px */
    .sticky-navbar-placeholder {
        height: 56px;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary sticky-top shadow-sm" style="z-index:1030;">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="/Inventory-and-Sale-System/index.php">
            <img src="/Inventory-and-Sale-System/assets/icons/inventory.png" alt="Products" width="20" height="20"
                class="nav-icon inventory-icon">
            Fund Tracker
        </a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item mx-1">
                    <a class="nav-link btn btn-outline-dark px-3 py-1 rounded-pill d-flex align-items-center gap-2"
                        href="/fund_tracker/index.php">
                        <img src="/Inventory-and-Sale-System/assets/icons/dashboard.png" alt="Dashboard" width="20"
                            height="20" class="nav-icon dashboard-icon">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mx-1">
                    <a class="nav-link btn btn-outline-dark px-3 py-1 rounded-pill d-flex align-items-center gap-2"
                        href="/fund_tracker/views/index.php">
                        <img src="/Inventory-and-Sale-System/assets/icons/products.png" alt="Products" width="20"
                            height="20" class="nav-icon products-icon">
                        Transactions
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>

<body>
    <div class="sticky-navbar-placeholder"></div>