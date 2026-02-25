<?php
session_start();
include('../../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ================= CREATE DONOR APPOINTMENT =================
if ($action === 'create_donor_appointment') {

    $donor_id = intval($_POST['donor_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $status = $_POST['status'] ?? 'scheduled';

    if ($donor_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();

    // 1️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // Extract hour and date
    $hour = intval(date('H', $appointment_datetime));
    $date_only = date('Y-m-d', $appointment_datetime);

    // 2️⃣ Operating hours check (7am to 7pm)
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments can only be booked between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // 3️⃣ Check if donor already has an appointment that day
    $stmt_day = $conn->prepare("
        SELECT * FROM appointments 
        WHERE donor_id = ? AND DATE(appointment_date) = ?
    ");
    $stmt_day->bind_param("is", $donor_id, $date_only);
    $stmt_day->execute();
    $result_day = $stmt_day->get_result();
    if ($result_day->num_rows > 0) {
        $_SESSION['error'] = "This donor already has an appointment booked for this day.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // 4️⃣ Check if the hour is already booked by ANY donor
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
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // ✅ Insert appointment if all checks pass
    $stmt = $conn->prepare("
        INSERT INTO appointments (donor_id, appointment_date, type, status)
        VALUES (?, ?, 'donation', ?)
    ");
    $stmt->bind_param("iss", $donor_id, $appointment_date, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Donor appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: StaffAppointmentDonorCreate.php");
    exit();
}


// ================= UPDATE DONOR APPOINTMENT =================
if ($action === 'update_donor_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($appointment_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();

    // 1️⃣ Cannot update to past date/time
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date/time.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Extract hour
    $hour = intval(date('H', $appointment_datetime));

    // 2️⃣ Operating hours 7am-7pm
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments can only be booked between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
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
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    // ✅ Update appointment if all checks pass
    $stmt = $conn->prepare("
        UPDATE appointments
        SET appointment_date = ?, status = ?
        WHERE appointment_id = ? AND donor_id IS NOT NULL
    ");
    $stmt->bind_param("ssi", $appointment_date, $status, $appointment_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Donor appointment updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    // Redirect BACK to the update page with ID
    header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
    exit();
}
?>