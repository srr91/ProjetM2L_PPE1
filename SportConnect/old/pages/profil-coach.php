<?php
// Compat MVC : cette page devient une route.
$coach_id = $_GET['id'] ?? 0;
header('Location: /SportConnect/index.php?route=coach/show&id=' . urlencode((string)$coach_id));
exit();
?>
