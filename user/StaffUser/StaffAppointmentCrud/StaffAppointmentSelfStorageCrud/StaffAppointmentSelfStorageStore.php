<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ================= CREATE SELF-STORAGE APPOINTMENT =================
if ($action === 'create_storage_appointment') {

    $storage_user_id = intval($_POST['storage_user_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $status = $_POST['status'] ?? 'scheduled';

    if ($storage_user_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();

    // 1️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // Extract hour and date
    $hour = intval(date('H', $appointment_datetime));
    $date_only = date('Y-m-d', $appointment_datetime);

    // 2️⃣ Operating hours check (7am to 7pm)
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments can only be booked between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // 3️⃣ Check if user already has an appointment that day
    $stmt_day = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'storage' AND user_id = ? AND DATE(appointment_date) = ?
    ");
    $stmt_day->bind_param("is", $storage_user_id, $date_only);
    $stmt_day->execute();
    $result_day = $stmt_day->get_result();
    if ($result_day->num_rows > 0) {
        $_SESSION['error'] = "This user already has an appointment booked for this day.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // 4️⃣ Check if the hour is already booked by ANY storage user
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'storage' AND appointment_date BETWEEN ? AND ?
    ");
    $stmt_hour->bind_param("ss", $start_hour, $end_hour);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();
    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked for a storage user. Please choose another hour.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // ✅ Insert appointment if all checks pass
    $stmt = $conn->prepare("
        INSERT INTO appointments (user_type, user_id, appointment_date, type, status)
        VALUES ('storage', ?, ?, 'storage', ?)
    ");
    $stmt->bind_param("iss", $storage_user_id, $appointment_date, $status);

     if ($stmt->execute()) {
        $_SESSION['success'] = "Donor appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: StaffAppointmentSelfStorageCreate.php");
    exit();
}

// ================= UPDATE SELF-STORAGE APPOINTMENT =================
if ($action === 'update_storage_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_date = $_POST['appointment_date'] ?? '';
    $new_status = $_POST['status'] ?? '';

    if ($appointment_id <= 0 || empty($new_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Fetch current appointment
    $stmt_curr = $conn->prepare("
        SELECT appointment_date, status 
        FROM appointments 
        WHERE appointment_id = ? AND user_type = 'storage'
    ");
    $stmt_curr->bind_param("i", $appointment_id);
    $stmt_curr->execute();
    $result_curr = $stmt_curr->get_result();

    if ($result_curr->num_rows === 0) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    $current = $result_curr->fetch_assoc();

    // Check if there are any actual changes
    $current_date = date('Y-m-d H:i', strtotime($current['appointment_date']));
    $new_date_normalized = date('Y-m-d H:i', strtotime($new_date));

    if ($current_date === $new_date_normalized && $current['status'] === $new_status) {
        $_SESSION['error'] = "No changes detected.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Proceed with the same validations as before (past date, operating hours, hourly conflict)
    $appointment_datetime = strtotime($new_date);
    $now = time();

    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date/time.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments can only be booked between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Check if the hour is already booked by another appointment
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE appointment_date BETWEEN ? AND ? 
        AND appointment_id != ? 
        AND user_type = 'storage'
    ");
    $stmt_hour->bind_param("ssi", $start_hour, $end_hour, $appointment_id);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();

    // Update appointment if all checks pass
    $stmt = $conn->prepare("
        UPDATE appointments
        SET appointment_date = ?, status = ?
        WHERE appointment_id = ? AND user_type = 'storage'
    ");
    $stmt->bind_param("ssi", $new_date, $new_status, $appointment_id);

    $stmt->execute();

    header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
    exit();
}
?>