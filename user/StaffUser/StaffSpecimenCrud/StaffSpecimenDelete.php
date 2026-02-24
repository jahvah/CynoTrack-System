<?php
session_start();
include('../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get parameters
$specimen_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($specimen_id <= 0 || !in_array($type, ['donor', 'storage'])) {
    header("Location: StaffSpecimenIndex.php");
    exit();
}

// Determine table
$table = $type === 'donor' ? 'donor_specimens' : 'storage_specimens';

// 1️⃣ Get current quantity before deleting
$get_stmt = $conn->prepare("SELECT quantity FROM $table WHERE specimen_id = ?");
$get_stmt->bind_param("i", $specimen_id);
$get_stmt->execute();
$result = $get_stmt->get_result();
$current = $result->fetch_assoc();
$get_stmt->close();

if ($current) {

    $quantity = (int)$current['quantity'];

    // 2️⃣ Insert inventory log as disposed
    $log_stmt = $conn->prepare("
        INSERT INTO inventory_logs (specimen_id, action, quantity) 
        VALUES (?, 'disposed', ?)
    ");
    $log_stmt->bind_param("ii", $specimen_id, $quantity);
    $log_stmt->execute();
    $log_stmt->close();

    // 3️⃣ Delete specimen
    $del_stmt = $conn->prepare("DELETE FROM $table WHERE specimen_id = ?");
    $del_stmt->bind_param("i", $specimen_id);
    $del_stmt->execute();
    $del_stmt->close();
}

// Redirect back to index
header("Location: StaffSpecimenIndex.php");
exit();