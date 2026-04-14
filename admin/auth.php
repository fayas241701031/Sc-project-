<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db_connect.php';

if (!isAdmin()) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}
