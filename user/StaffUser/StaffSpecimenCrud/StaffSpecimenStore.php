<?php
session_start();
include('../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: StaffSpecimenIndex.php");
    exit();
}

$action = $_POST['action'] ?? '';

// =========================================================
// BLOCK 1: CREATE DONOR SPECIMEN
// =========================================================
if ($action === 'create_donor_specimen') {
    $donor_id = intval($_POST['donor_id']);
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
    $storage_user_id = intval($_POST['storage_user_id']);
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

// =========================================================
// BLOCK 3: UPDATE DONOR SPECIMEN (optional fields safe)
// =========================================================
elseif ($action === 'update_donor_specimen') {
    $specimen_id = intval($_POST['specimen_id']);

    // Get current values
    $current_stmt = $conn->prepare("SELECT * FROM donor_specimens WHERE specimen_id = ?");
    $current_stmt->bind_param("i", $specimen_id);
    $current_stmt->execute();
    $current = $current_stmt->get_result()->fetch_assoc();
    $current_stmt->close();

    $unique_code = !empty($_POST['unique_code']) ? trim($_POST['unique_code']) : $current['unique_code'];
    $quantity = !empty($_POST['quantity']) ? $_POST['quantity'] : $current['quantity'];
    $status = !empty($_POST['status']) ? $_POST['status'] : $current['status'];
    $storage_location = !empty($_POST['storage_location']) ? trim($_POST['storage_location']) : $current['storage_location'];
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : $current['expiration_date'];

    $check_stmt = $conn->prepare("
    SELECT unique_code, storage_location 
    FROM donor_specimens 
    WHERE (unique_code = ? OR storage_location = ?) 
    AND specimen_id != ?
");
$check_stmt->bind_param("ssi", $unique_code, $storage_location, $specimen_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

$errors = [];

while ($row = $check_result->fetch_assoc()) {

    if ($row['unique_code'] === $unique_code) {
        $errors[] = "Unique Code '$unique_code' already exists!";
    }

    if ($row['storage_location'] === $storage_location) {
        $errors[] = "Storage Location '$storage_location' is already occupied!";
    }
}

$check_stmt->close();

if (!empty($errors)) {
    $_SESSION['error'] = implode(" ", $errors);
    header("Location: StaffSpecimenUpdateDonor.php?id=" . $specimen_id);
    exit();
}

    $stmt = $conn->prepare("UPDATE donor_specimens SET unique_code=?, quantity=?, status=?, storage_location=?, expiration_date=? WHERE specimen_id=?");
    $stmt->bind_param("sisssi", $unique_code, $quantity, $status, $storage_location, $expiration_date, $specimen_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Donor specimen updated successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: StaffSpecimenUpdateDonor.php?id=" . $specimen_id);
    exit();
}

// =========================================================
// BLOCK 4: UPDATE STORAGE SPECIMEN (optional fields safe)
// =========================================================
elseif ($action === 'update_storage_specimen') {
    $specimen_id = intval($_POST['specimen_id']);

    $current_stmt = $conn->prepare("SELECT * FROM storage_specimens WHERE specimen_id = ?");
    $current_stmt->bind_param("i", $specimen_id);
    $current_stmt->execute();
    $current = $current_stmt->get_result()->fetch_assoc();
    $current_stmt->close();

    $unique_code = !empty($_POST['unique_code']) ? trim($_POST['unique_code']) : $current['unique_code'];
    $quantity = !empty($_POST['quantity']) ? $_POST['quantity'] : $current['quantity'];
    $status = !empty($_POST['status']) ? $_POST['status'] : $current['status'];
    $storage_location = !empty($_POST['storage_location']) ? trim($_POST['storage_location']) : $current['storage_location'];
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : $current['expiration_date'];

    $check_stmt = $conn->prepare("
    SELECT unique_code, storage_location 
    FROM storage_specimens 
    WHERE (unique_code = ? OR storage_location = ?) 
    AND specimen_id != ?
");
$check_stmt->bind_param("ssi", $unique_code, $storage_location, $specimen_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

$errors = [];

while ($row = $check_result->fetch_assoc()) {

    if ($row['unique_code'] === $unique_code) {
        $errors[] = "Unique Code '$unique_code' already exists!";
    }

    if ($row['storage_location'] === $storage_location) {
        $errors[] = "Storage Location '$storage_location' is already occupied!";
    }
}

$check_stmt->close();

if (!empty($errors)) {
    $_SESSION['error'] = implode(" ", $errors);
    header("Location: StaffSpecimenUpdateDonor.php?id=" . $specimen_id);
    exit();
}

    $stmt = $conn->prepare("UPDATE storage_specimens SET unique_code=?, quantity=?, status=?, storage_location=?, expiration_date=? WHERE specimen_id=?");
    $stmt->bind_param("sisssi", $unique_code, $quantity, $status, $storage_location, $expiration_date, $specimen_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Storage specimen updated successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: StaffSpecimenUpdateStorage.php?id=" . $specimen_id);
    exit();
}

// =========================================================
// FALLBACK REDIRECT
// =========================================================
header("Location: StaffSpecimenIndex.php");
exit();