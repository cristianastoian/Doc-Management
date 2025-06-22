<?php
if (!isset($_GET['url']) || !isset($_GET['name'])) {
    die('Missing parameters');
}

$fileUrl = urldecode($_GET['url']);
$fileName = basename($_GET['name']); 

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
readfile($fileUrl);
exit;
