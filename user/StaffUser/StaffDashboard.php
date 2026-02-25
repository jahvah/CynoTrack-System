<?php
session_start();

include('../../includes/config.php');
include('../../includes/header.php');

// Not logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

// Logged in but NOT staff
if ($_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}
?>

<style>
    .dashboard-container {
        padding: 40px;
        text-align: center;
    }

    .dashboard-title {
        margin-bottom: 40px;
    }

    .dashboard-btn {
        display: inline-block;
        padding: 15px 30px;
        font-size: 16px;
        text-decoration: none;
        color: white;
        background-color: #007bff;
        border-radius: 6px;
        transition: 0.3s;
        margin: 10px;
    }

    .dashboard-btn:hover {
        background-color: #0056b3;
    }
</style>

<div class="dashboard-container">
    <h1 class="dashboard-title">Staff Dashboard</h1>

    <!-- SPECIMEN MANAGEMENT -->
    <a href="StaffSpecimenCrud/StaffSpecimenIndex.php" class="dashboard-btn">
        Manage Specimen
    </a>

    <!-- APPOINTMENT MANAGEMENT -->
    <a href="StaffAppointmentCrud/StaffAppointmentIndex.php" class="dashboard-btn">
        Manage Appointments
    </a>

</div>

<?php include('../../includes/footer.php'); ?>