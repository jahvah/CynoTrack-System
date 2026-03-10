<?php
session_start();
include("../../../includes/config.php");

// Ensure recipient is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recipient_id = intval($_POST['recipient_id'] ?? 0);
    $donor_id     = intval($_POST['donor_id'] ?? 0);
    $quantity     = intval($_POST['quantity'] ?? 0);

    if ($recipient_id <= 0 || $donor_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = "Invalid input data.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

// Check if recipient already has any pending request
$stmt_check = $conn->prepare("
    SELECT COUNT(*) as pending_count
    FROM specimen_requests
    WHERE recipient_id = ? 
      AND status = 'pending'
");
$stmt_check->bind_param("i", $recipient_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();

if ($row_check['pending_count'] > 0) {
    $_SESSION['error'] = "You already have a pending request. Please wait until it is processed.";
    header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
    exit;
}

    // Fetch a stored specimen from this donor
    $stmt = $conn->prepare("
        SELECT specimen_id, quantity, price
        FROM specimens
        WHERE specimen_owner_type = 'donor'
        AND specimen_owner_id = ?
        AND status = 'stored'
        ORDER BY specimen_id ASC
        LIMIT 1
    ");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "No available specimens from this donor.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    $specimen = $result->fetch_assoc();

    if ($quantity > $specimen['quantity']) {
        $_SESSION['error'] = "Requested quantity exceeds available specimens.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    $specimen_id = $specimen['specimen_id'];
    $unit_price = $specimen['price'];
    $total_price = $unit_price * $quantity;

    // Handle receipt upload
    $receipt_image = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../../../uploads/receipts/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $file_name = "receipt_" . time() . "_" . rand(1000,9999) . "." . $file_ext;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_path)) {
            $receipt_image = "receipts/" . $file_name;
        } else {
            $_SESSION['error'] = "Failed to upload receipt.";
            header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
            exit;
        }
    } else {
        $_SESSION['error'] = "Receipt is required.";
        header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
        exit;
    }

    // Insert request
    $stmt_insert = $conn->prepare("
        INSERT INTO specimen_requests 
        (request_type, recipient_id, specimen_id, requested_quantity, payment_status, receipt_image, unit_price, total_price)
        VALUES ('recipient', ?, ?, ?, 'unpaid', ?, ?, ?)
    ");
    $stmt_insert->bind_param("iiisdd", $recipient_id, $specimen_id, $quantity, $receipt_image, $unit_price, $total_price);

    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "Specimen request submitted successfully.";
    } else {
        $_SESSION['error'] = "Failed to submit request. MySQL Error: " . $stmt_insert->error;
    }

    header("Location: RecipientDonorRequestIndex.php?id={$donor_id}");
    exit;

} else {
    header("Location: ../RecipientDashboard.php");
    exit;
}