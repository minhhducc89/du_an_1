<?php
/** @var array $old */
/** @var string[] $errors */
/** @var Tour[] $tours */
/** @var Customer[] $customers */
$isEdit = isset($_GET['id']) && (int)$_GET['id'] > 0;
$bookingId = $isEdit ? (int)$_GET['id'] : 0;
?>