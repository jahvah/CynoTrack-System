<?php
session_start();
include('../../../includes/config.php');

// Admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

// Check if donor ID is provided
if (!isset($_GET['id'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$donor_id = intval($_GET['id']);

/* =========================
   GET ACCOUNT ID + IMAGE
========================= */
$stmt = $conn->prepare("SELECT account_id, profile_image FROM donors_users WHERE donor_id=?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$data = $result->fetch_assoc();
$account_id = $data['account_id'];
$image = $data['profile_image'];

/* =========================
   DELETE DONOR RECORD
========================= */
$stmt = $conn->prepare("DELETE FROM donors_users WHERE donor_id=?");
$stmt->bind_param("i", $donor_id);
if (!$stmt->execute()) {
    die("Donor delete error: " . $stmt->error);
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
if (!empty($image) && file_exists("../../uploads/" . $image)) {
    unlink("../../uploads/" . $image);
}

header("Location: AdminDonorIndex.php?success=donor_deleted");
exit();
?>
