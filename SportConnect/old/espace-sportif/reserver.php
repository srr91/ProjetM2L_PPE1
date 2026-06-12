<?php
// Compat MVC : ancien URL conservé
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = '/index.php?route=sportif/reserver' . ($qs ? '&' . $qs : '');
header('Location: ' . $target);
exit();
?>
