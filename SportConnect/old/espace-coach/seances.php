<?php
// Compat MVC : ancien URL conservé
$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = '/index.php?route=coach/seances' . ($qs ? '&' . $qs : '');
header('Location: ' . $target);
exit();
?>
