<?php
$response = new stdClass();
$response->status = 'success';
session_unset();
echo (json_encode($response));
?>