<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'create_self_storage_specimen') {

    $storage_user_id = $_POST['storage_user_id'] ?? '';
    $unique_code = trim($_POST['unique_code']);
    $quantity = (int)$_POST['quantity'];
    $status = 'screening'; // force default
    $storage_location = trim($_POST['storage_location']);
    $expiration_date = $_POST['expiration_date'];

        // ✅ CHECK IF DONOR EXISTS
    $donor_check_stmt = $conn->prepare("SELECT storage_user_id FROM self_storage_users WHERE storage_user_id = ?");
    $donor_check_stmt->bind_param("i", $storage_user_id);
    $donor_check_stmt->execute();
    $donor_check_stmt->store_result();

    if ($donor_check_stmt->num_rows === 0) {
        $_SESSION['error'] = 'User does not exist';
        $donor_check_stmt->close();
        header("Location: StaffSpecimenSelfStorageCreate.php");
        exit();
    }
    $donor_check_stmt->close();

    // Prevent past expiration dates
    if (!empty($expiration_date) && strtotime($expiration_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = 'Expiration date cannot be in the past';
        header("Location: StaffSpecimenSelfStorageCreate.php");
        exit();
    }

    // Check duplicates in unified table
    $code_check = mysqli_query($conn, "SELECT 1 FROM specimens WHERE unique_code = '$unique_code'");
    $location_check = mysqli_query($conn, "SELECT 1 FROM specimens WHERE specimen_owner_type = 'storage' AND specimen_owner_id = $storage_user_id AND storage_location = '$storage_location'");

    $error_message = '';
    if (mysqli_num_rows($code_check) > 0 && mysqli_num_rows($location_check) > 0) {
        $error_message = 'Duplicate storage location and unique code';
    } elseif (mysqli_num_rows($code_check) > 0) {
        $error_message = 'Duplicate unique code';
    } elseif (mysqli_num_rows($location_check) > 0) {
        $error_message = 'Duplicate storage location';
    }

    if ($error_message) {
        $_SESSION['error'] = $error_message;
        header("Location: StaffSpecimenSelfStorageCreate.php");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO specimens 
        (specimen_owner_type, specimen_owner_id, unique_code, quantity, status, storage_location, expiration_date) 
        VALUES ('storage', ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $storage_user_id, $unique_code, $quantity, $status, $storage_location, $expiration_date);

    $_SESSION['success'] = $stmt->execute() 
        ? 'Self-storage specimen added successfully' 
        : 'Error adding self-storage specimen';

    $stmt->close();
    header("Location: StaffSpecimenSelfStorageCreate.php");
    exit();

} elseif ($action === 'update_self_storage_specimen') {

    $specimen_id = intval($_POST['specimen_id']);

    // Fetch current data
    $result = mysqli_query($conn, "SELECT * FROM specimens WHERE specimen_id = $specimen_id AND specimen_owner_type = 'storage'");
    $current = mysqli_fetch_assoc($result);

    $old_quantity = (int)$current['quantity'];
    $old_status   = $current['status'];

    // Use new values if provided
    $quantity = isset($_POST['quantity']) && $_POST['quantity'] !== '' 
        ? (int)$_POST['quantity'] 
        : $old_quantity;

    $status = !empty($_POST['status']) 
        ? $_POST['status'] 
        : $old_status;

    $storage_location = !empty($_POST['storage_location']) 
        ? trim($_POST['storage_location']) 
        : $current['storage_location'];

    $expiration_date = !empty($_POST['expiration_date']) 
        ? $_POST['expiration_date'] 
        : $current['expiration_date'];

    // Prevent past expiration dates
    if (!empty($expiration_date) && strtotime($expiration_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = 'Expiration date cannot be in the past';
        header("Location: StaffSpecimenSelfStorageUpdate.php?id=$specimen_id");
        exit();
    }

    // Duplicate check (exclude current record)
    if (strcasecmp($storage_location, $current['storage_location']) !== 0) {
        $check = mysqli_query($conn, "
            SELECT 1 FROM specimens 
            WHERE specimen_owner_type = 'storage'
            AND TRIM(LOWER(storage_location)) = '".strtolower($storage_location)."'
            AND specimen_id != $specimen_id
        ");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = 'Duplicate storage location';
            header("Location: StaffSpecimenSelfStorageUpdate.php?id=$specimen_id");
            exit();
        }
    }

    $stmt = $conn->prepare("
        UPDATE specimens 
        SET quantity = ?, status = ?, storage_location = ?, expiration_date = ? 
        WHERE specimen_id = ?
    ");
    $stmt->bind_param("isssi", $quantity, $status, $storage_location, $expiration_date, $specimen_id);

    if ($stmt->execute()) {

        // 1️⃣ Quantity change logging
        if ($quantity != $old_quantity) {

            $difference = $quantity - $old_quantity;

            if ($difference > 0) {
                $action_type = 'added';
                $log_qty = $difference;
            } else {
                $action_type = 'used';
                $log_qty = abs($difference);
            }

            $log_stmt = $conn->prepare("
                INSERT INTO inventory_logs (specimen_id, action, quantity) 
                VALUES (?, ?, ?)
            ");
            $log_stmt->bind_param("isi", $specimen_id, $action_type, $log_qty);
            $log_stmt->execute();
            $log_stmt->close();
        }

        // 2️⃣ Status-based logging
        if ($status !== $old_status) {

            if ($status === 'stored') {

                $action_type = 'added';
                $log_qty = $quantity;

            } elseif ($status === 'used') {

                $action_type = 'used';
                $log_qty = $quantity;

            } elseif ($status === 'disposed') {

                $action_type = 'disposed';
                $log_qty = $quantity;

            } else {
                // approved, disapproved, expired → NO inventory log
                $action_type = null;
            }

            if ($action_type !== null) {
                $log_stmt = $conn->prepare("
                    INSERT INTO inventory_logs (specimen_id, action, quantity) 
                    VALUES (?, ?, ?)
                ");
                $log_stmt->bind_param("isi", $specimen_id, $action_type, $log_qty);
                $log_stmt->execute();
                $log_stmt->close();
            }
        }

        $_SESSION['success'] = 'Self-storage specimen updated successfully';

    } else {
        $_SESSION['error'] = 'Error updating self-storage specimen';
    }
    $stmt->close();
    header("Location: StaffSpecimenSelfStorageUpdate.php?id=$specimen_id");
    exit();
}