<?php
session_start();
include('../../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ================= CREATE RECIPIENT APPOINTMENT =================
if ($action === 'create_recipient_appointment') {

    $recipient_id = intval($_POST['recipient_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $status = $_POST['status'] ?? 'scheduled';

    if ($recipient_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();

    // 1️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // Extract hour and date
    $hour = intval(date('H', $appointment_datetime));
    $date_only = date('Y-m-d', $appointment_datetime);

    // 2️⃣ Operating hours check (7am to 7pm)
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments can only be booked between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // 3️⃣ Check if recipient already has an appointment that day
    $stmt_day = $conn->prepare("
        SELECT * FROM appointments 
        WHERE recipient_id = ? AND DATE(appointment_date) = ?
    ");
    $stmt_day->bind_param("is", $recipient_id, $date_only);
    $stmt_day->execute();
    $result_day = $stmt_day->get_result();
    if ($result_day->num_rows > 0) {
        $_SESSION['error'] = "This recipient already has an appointment booked for this day.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // 4️⃣ Check if the hour is already booked by ANY appointment
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE appointment_date BETWEEN ? AND ?
    ");
    $stmt_hour->bind_param("ss", $start_hour, $end_hour);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();
    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked. Please choose another hour.";
        header("Location: StaffAppointmentRecipientCreate.php");
        exit();
    }

    // ✅ Insert appointment if all checks pass
    $stmt = $conn->prepare("
        INSERT INTO appointments (recipient_id, appointment_date, type, status)
        VALUES (?, ?, 'consultation', ?)
    ");
    $stmt->bind_param("iss", $recipient_id, $appointment_date, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Recipient appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: StaffAppointmentRecipientCreate.php");
    exit();
}


// ================= UPDATE RECIPIENT APPOINTMENT =================
if ($action === 'update_recipient_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($appointment_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();

    // 1️⃣ Cannot update to past date/time
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date/time.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Extract hour
    $hour = intval(date('H', $appointment_datetime));

    // 2️⃣ Operating hours 7am-7pm
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments can only be booked between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 3️⃣ Check if the hour is already booked by ANY OTHER appointment
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE appointment_date BETWEEN ? AND ? AND appointment_id != ?
    ");
    $stmt_hour->bind_param("ssi", $start_hour, $end_hour, $appointment_id);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();
    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked. Please choose another hour.";
        header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
        exit();
    }

    // ✅ Update appointment if all checks pass
    $stmt = $conn->prepare("
        UPDATE appointments
        SET appointment_date = ?, status = ?
        WHERE appointment_id = ? AND recipient_id IS NOT NULL
    ");
    $stmt->bind_param("ssi", $appointment_date, $status, $appointment_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Recipient appointment updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    // Redirect BACK to the update page with ID
    header("Location: StaffAppointmentRecipientUpdate.php?id=" . $appointment_id);
    exit();
}
?>