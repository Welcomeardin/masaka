<?php
$title = 'Dashboard';

ob_start();
?>
about


<?php
$content = ob_get_clean();

include __DIR__ . '/template/index.php';
?>