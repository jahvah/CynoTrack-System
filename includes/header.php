<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; // 'admin' or 'staff'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <?php if ($role === 'admin'): ?>
                    <a href="AdminStaffCrud/AdminStaffIndex.php">Staff Accounts</a>
                    <a href="AdminDonorCrud/AdminDonorIndex.php">Donor Accounts</a>
                    <a href="AdminRecipientCrud/AdminRecipientIndex.php">Recipient Accounts</a>
                    <a href="AdminSelfStorageCrud/AdminSelfStorageIndex.php">Self-Storage Accounts</a>
                <?php elseif ($role === 'staff'): ?>
                    <a href="StaffSpecimenCrud/StaffSpecimenIndex.php">Specimens</a>
                    <a href="StaffAppointmentCrud/StaffAppointmentIndex.php">Appointments</a>
                <?php elseif ($role === 'donor'): ?>
                    <a href="DonorAppointmentCrud/DonorAppointmentIndex.php">My Appointments</a></li>
                <?php endif; ?>
                <a href="/cynotrack/user/logout.php">Logout</a>
            </ul>
        </nav>
    </header>
</body>
</html>