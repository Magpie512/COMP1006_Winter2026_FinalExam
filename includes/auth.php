<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (empty($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION["isadmin"] == 1) {
    header("Location: admin.php");
    exit();
}