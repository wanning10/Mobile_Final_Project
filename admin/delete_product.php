<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Ensure admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Get product ID from query string
if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    if (deleteProduct($conn, $productId)) {
        $_SESSION['success'] = "Product deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete product.";
    }
}

header("Location: index.php");
exit();
