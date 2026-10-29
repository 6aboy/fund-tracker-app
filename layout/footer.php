<?php

if (!empty($_SESSION['alert'])):
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: <?= json_encode($_SESSION['alert']['type'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
            title: <?= json_encode($_SESSION['alert']['message'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
            showConfirmButton: false,
            timer: 2000
        });

        //Alert for submit
    </script>



    <?php
    unset($_SESSION['alert']);
endif;
?>

</body>

</html>