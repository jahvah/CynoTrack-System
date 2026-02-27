<?php
session_start();
include('../../../includes/config.php');

// Recipient access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get appointment ID
$appointment_id = intval($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    $_SESSION['error'] = "Invalid appointment.";
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

// Get recipient_id from logged in account
$stmt = $conn->prepare("
    SELECT recipient_id 
    FROM recipients_users 
    WHERE account_id = ?
");
$stmt->bind_param("i", $_SESSION['account_id']);
$stmt->execute();
$result = $stmt->get_result();
$recipient = $result->fetch_assoc();

if (!$recipient) {
    $_SESSION['error'] = "Recipient record not found.";
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

$recipient_id = $recipient['recipient_id'];

// Check appointment ownership
$stmt = $conn->prepare("
    SELECT status 
    FROM appointments
    WHERE appointment_id = ?
    AND user_type = 'recipient'
    AND user_id = ?
");
$stmt->bind_param("ii", $appointment_id, $recipient_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

// Prevent cancelling completed
if ($appointment['status'] === 'completed') {
    $_SESSION['error'] = "Completed appointments cannot be cancelled.";
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

// Update status instead of deleting
$stmt = $conn->prepare("
    UPDATE appointments
    SET status = 'cancelled'
    WHERE appointment_id = ?
");
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Appointment cancelled successfully.";
} else {
    $_SESSION['error'] = "Failed to cancel appointment.";
}

header("Location: RecipientAppointmentIndex.php");
exit();
?>