<?php
session_start();
include('../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'create_donor_specimen') {
    $donor_id = $_POST['donor_id'] ?? '';
    $unique_code = trim($_POST['unique_code']);
    $quantity = (int)$_POST['quantity'];
    $status = $_POST['status'];
    $storage_location = trim($_POST['storage_location']);
    $expiration_date = $_POST['expiration_date'];

    // Check duplicates
    $code_check = mysqli_query($conn, "SELECT * FROM donor_specimens WHERE unique_code = '$unique_code'");
    $location_check = mysqli_query($conn, "SELECT * FROM donor_specimens WHERE storage_location = '$storage_location'");

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
        header("Location: StaffSpecimenDonorCreate.php");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO donor_specimens (donor_id, unique_code, quantity, status, storage_location, expiration_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $donor_id, $unique_code, $quantity, $status, $storage_location, $expiration_date);
    $_SESSION['success'] = $stmt->execute() ? 'Donor specimen added successfully' : 'Error adding donor specimen';
    $stmt->close();
    header("Location: StaffSpecimenDonorCreate.php");
    exit();

} elseif ($action === 'create_storage_specimen') {
    $storage_user_id = $_POST['storage_user_id'] ?? '';
    $unique_code = trim($_POST['unique_code']);
    $quantity = (int)$_POST['quantity'];
    $status = $_POST['status'];
    $storage_location = trim($_POST['storage_location']);
    $expiration_date = $_POST['expiration_date'];

    // Check duplicates
    $code_check = mysqli_query($conn, "SELECT * FROM storage_specimens WHERE unique_code = '$unique_code'");
    $location_check = mysqli_query($conn, "SELECT * FROM storage_specimens WHERE storage_location = '$storage_location'");

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
        header("Location: StaffSpecimenStorageCreate.php");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO storage_specimens (storage_user_id, unique_code, quantity, status, storage_location, expiration_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $storage_user_id, $unique_code, $quantity, $status, $storage_location, $expiration_date);
    $_SESSION['success'] = $stmt->execute() ? 'Storage specimen added successfully' : 'Error adding storage specimen';
    $stmt->close();
    header("Location: StaffSpecimenStorageCreate.php");
    exit();


    //update 
} elseif ($action === 'update_donor_specimen') {
    $specimen_id = intval($_POST['specimen_id']);

    // Fetch current data
    $result = mysqli_query($conn, "SELECT * FROM donor_specimens WHERE specimen_id = $specimen_id");
    $current = mysqli_fetch_assoc($result);

    // Use new values if provided, else keep existing
    $quantity = isset($_POST['quantity']) && $_POST['quantity'] !== '' ? (int)$_POST['quantity'] : $current['quantity'];
    $status = !empty($_POST['status']) ? $_POST['status'] : $current['status'];
    $storage_location = !empty($_POST['storage_location']) ? trim($_POST['storage_location']) : $current['storage_location'];
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : $current['expiration_date'];

    // Check duplicates only if storage location changed
    if (strcasecmp($storage_location, $current['storage_location']) !== 0) {
        $check = mysqli_query($conn, "SELECT * FROM donor_specimens WHERE TRIM(LOWER(storage_location)) = '".strtolower($storage_location)."'");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = 'Duplicate storage location';
            header("Location: StaffSpecimenDonorUpdate.php?id=$specimen_id");
            exit();
        }
    }

    $stmt = $conn->prepare("UPDATE donor_specimens SET quantity = ?, status = ?, storage_location = ?, expiration_date = ? WHERE specimen_id = ?");
    $stmt->bind_param("isssi", $quantity, $status, $storage_location, $expiration_date, $specimen_id);
    $_SESSION['success'] = $stmt->execute() ? 'Donor specimen updated successfully' : 'Error updating donor specimen';
    $stmt->close();
    header("Location: StaffSpecimenDonorUpdate.php?id=$specimen_id");
    exit();

} elseif ($action === 'update_storage_specimen') {
    $specimen_id = intval($_POST['specimen_id']);

    // Fetch current data
    $result = mysqli_query($conn, "SELECT * FROM storage_specimens WHERE specimen_id = $specimen_id");
    $current = mysqli_fetch_assoc($result);

    // Use new values if provided, else keep existing
    $quantity = isset($_POST['quantity']) && $_POST['quantity'] !== '' ? (int)$_POST['quantity'] : $current['quantity'];
    $status = !empty($_POST['status']) ? $_POST['status'] : $current['status'];
    $storage_location = !empty($_POST['storage_location']) ? trim($_POST['storage_location']) : $current['storage_location'];
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : $current['expiration_date'];

    // Check duplicates only if storage location changed
    if (strcasecmp($storage_location, $current['storage_location']) !== 0) {
        $check = mysqli_query($conn, "SELECT * FROM storage_specimens WHERE TRIM(LOWER(storage_location)) = '".strtolower($storage_location)."'");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = 'Duplicate storage location';
            header("Location: StaffSpecimenStorageUpdate.php?id=$specimen_id");
            exit();
        }
    }

    $stmt = $conn->prepare("UPDATE storage_specimens SET quantity = ?, status = ?, storage_location = ?, expiration_date = ? WHERE specimen_id = ?");
    $stmt->bind_param("isssi", $quantity, $status, $storage_location, $expiration_date, $specimen_id);
    $_SESSION['success'] = $stmt->execute() ? 'Storage specimen updated successfully' : 'Error updating storage specimen';
    $stmt->close();
    header("Location: StaffSpecimenStorageUpdate.php?id=$specimen_id");
    exit();

} else {
    $_SESSION['error'] = 'Invalid action';
    header("Location: StaffSpecimenIndex.php");
    exit();
}
?>