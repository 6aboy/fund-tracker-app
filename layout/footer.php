<?php

if (!empty($_SESSION['alert'])):
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: '<?php echo $_SESSION['alert']['type']; ?>',
            title: '<?php echo $_SESSION['alert']['message']; ?>',
            showConfirmButton: false,
            timer: 2000
        });

        //Alert for submit
    </script>

    <script>

        document.addEventListener('DOMContentLoaded', function){
            document.querySelector('transactionForm').onsubmit = function{
                alert('Successfully Created a Transaction');
            }
        }

    </script>

    <?php
    unset($_SESSION['alert']);
endif;
?>

</body>

</html>