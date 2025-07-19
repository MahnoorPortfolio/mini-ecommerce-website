<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/db.php';

$inserted = importProductsFromFolders($conn, realpath(__DIR__.'/assets/images'));

echo "<h1>Product image import finished</h1>";
echo "<p>Inserted/Skipped: $inserted</p>"; 