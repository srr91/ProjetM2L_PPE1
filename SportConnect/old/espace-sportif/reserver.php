<?php
// Compat MVC : ancien URL conservé
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = '/SportConnect/index.php?route=sportif/reserver' . ($qs ? '&' . $qs : '');
header('Location: ' . $target);
exit();
?>
