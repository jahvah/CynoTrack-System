<?php
session_start();
include('../../../includes/config.php');

// Admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

// Check if storage user ID is provided
if (!isset($_GET['id'])) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$storage_user_id = intval($_GET['id']);

/* =========================
   GET ACCOUNT ID + PROFILE IMAGE
========================= */
$stmt = $conn->prepare("SELECT account_id, profile_image FROM self_storage_users WHERE storage_user_id=?");
$stmt->bind_param("i", $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];
$image = $data['profile_image'];

/* =========================
   DELETE SELF-STORAGE USER RECORD
========================= */
$stmt = $conn->prepare("DELETE FROM self_storage_users WHERE storage_user_id=?");
$stmt->bind_param("i", $storage_user_id);
if (!$stmt->execute()) {
    die("Self-Storage user delete error: " . $stmt->error);
}

/* =========================
   DELETE ACCOUNT RECORD
========================= */
$stmt = $conn->prepare("DELETE FROM accounts WHERE account_id=?");
$stmt->bind_param("i", $account_id);
if (!$stmt->execute()) {
    die("Account delete error: " . $stmt->error);
}

/* =========================
   DELETE PROFILE IMAGE FILE
========================= */
if (!empty($image) && file_exists("../../../uploads/" . $image)) {
    unlink("../../../uploads/" . $image);
}

// Redirect back to self-storage user index
header("Location: AdminSelfStorageIndex.php?success=storage_user_deleted");
exit();
?>
