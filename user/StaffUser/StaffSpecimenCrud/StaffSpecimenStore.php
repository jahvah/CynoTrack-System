<?php
session_start();
include('../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =========================================================
    // BLOCK 1: CREATE DONOR SPECIMEN
    // =========================================================
    if ($action === 'create_donor_specimen') {
        $donor_id = $_POST['donor_id'];
        $unique_code = trim($_POST['unique_code']);
        $quantity = $_POST['quantity'];
        $status = $_POST['status'];
        $storage_location = trim($_POST['storage_location']);
        $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : NULL;

        // Check for existing unique_code and storage_location
        $check_stmt = $conn->prepare("SELECT * FROM donor_specimens WHERE unique_code = ? OR storage_location = ?");
        $check_stmt->bind_param("ss", $unique_code, $storage_location);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        $errors = [];
        while ($row = $check_result->fetch_assoc()) {
            if ($row['unique_code'] === $unique_code) $errors[] = "Unique Code '$unique_code' already exists!";
            if ($row['storage_location'] === $storage_location) $errors[] = "Storage Location '$storage_location' is already occupied!";
        }
        $check_stmt->close();

        if (!empty($errors)) {
            $_SESSION['error'] = implode(" ", $errors);
            header("Location: StaffSpecimenCreateDonor.php");
            exit();
        }

        // Insert if no conflicts
        $stmt = $conn->prepare("INSERT INTO donor_specimens (donor_id, unique_code, quantity, status, storage_location, expiration_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisss", $donor_id, $unique_code, $quantity, $status, $storage_location, $expiration_date);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Donor specimen added successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }
        $stmt->close();
        header("Location: StaffSpecimenCreateDonor.php");
        exit();
    }

    // =========================================================
    // BLOCK 2: CREATE STORAGE SPECIMEN
    // =========================================================
    elseif ($action === 'create_storage_specimen') {
        $storage_user_id = $_POST['storage_user_id'];
        $unique_code = trim($_POST['unique_code']);
        $quantity = $_POST['quantity'];
        $status = $_POST['status'];
        $storage_location = trim($_POST['storage_location']);
        $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : NULL;

        // Check for existing unique_code and storage_location
        $check_stmt = $conn->prepare("SELECT * FROM storage_specimens WHERE unique_code = ? OR storage_location = ?");
        $check_stmt->bind_param("ss", $unique_code, $storage_location);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        $errors = [];
        while ($row = $check_result->fetch_assoc()) {
            if ($row['unique_code'] === $unique_code) $errors[] = "Unique Code '$unique_code' already exists!";
            if ($row['storage_location'] === $storage_location) $errors[] = "Storage Location '$storage_location' is already occupied!";
        }
        $check_stmt->close();

        if (!empty($errors)) {
            $_SESSION['error'] = implode(" ", $errors);
            header("Location: StaffSpecimenCreateStorage.php");
            exit();
        }

        // Insert if no conflicts
        $stmt = $conn->prepare("INSERT INTO storage_specimens (storage_user_id, unique_code, quantity, status, storage_location, expiration_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisss", $storage_user_id, $unique_code, $quantity, $status, $storage_location, $expiration_date);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Storage specimen added successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }
        $stmt->close();
        header("Location: StaffSpecimenCreateStorage.php");
        exit();
    }
}

$conn->close();
?>