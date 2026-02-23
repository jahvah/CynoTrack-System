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

// Delete specimen
$stmt = $conn->prepare("DELETE FROM $table WHERE specimen_id = ?");
$stmt->bind_param("i", $specimen_id);
$stmt->execute();
$stmt->close();

// Redirect back to index
header("Location: StaffSpecimenIndex.php");
exit();