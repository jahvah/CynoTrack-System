<?php
session_start();

/* ==============================
   INCLUDE FILES (OUTSIDE FOLDER)
============================== */
include('../../includes/config.php');
include('../../includes/header.php');

/* ==============================
   ADMIN ACCESS PROTECTION
============================== */

// Not logged in
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

// Logged in but NOT admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../unauthorized.php");
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
    <h1 class="dashboard-title">Admin Dashboard</h1>

    <!-- STAFF -->
    <a href="AdminStaffCrud/AdminStaffIndex.php" class="dashboard-btn">
        Manage Staff
    </a>

    <!-- DONORS -->
    <a href="AdminDonorCrud/AdminDonorIndex.php" class="dashboard-btn">
        Manage Donors
    </a>

    <!-- RECIPIENTS -->
    <a href="AdminRecipientCrud/AdminRecipientIndex.php" class="dashboard-btn">
        Manage Recipients
    </a>

    <!-- SELF-STORAGE USERS -->
    <a href="AdminSelfStorageCrud/AdminSelfStorageIndex.php" class="dashboard-btn">
        Manage Self-Storage Users
    </a>
</div>

<?php include('../../includes/footer.php'); ?>
