<?php
// Compat MVC : cette page devient une route.
// Ancien URL conservé pour ne rien casser.
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = '/SportConnect/index.php?route=coachs' . ($qs ? '&' . $qs : '');
header('Location: ' . $target);
exit();
?>
