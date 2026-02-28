<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// SELF-STORAGE access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-storage') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get logged-in storage user ID
$account_id = $_SESSION['account_id'];
$storage_query = mysqli_query($conn, "SELECT storage_user_id FROM self_storage_users WHERE account_id = '$account_id' LIMIT 1");
$storage_data = mysqli_fetch_assoc($storage_query);

if (!$storage_data) {
    echo "<div class='container'><div class='message error'>Storage user record not found.</div></div>";
    include('../../../includes/footer.php');
    exit();
}

$storage_user_id = $storage_data['storage_user_id'];
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
    cursor: pointer;
}
.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }

.back-btn {
    display: inline-block;
    padding: 8px 15px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 15px;
}
.back-btn:hover { background: #333; }
</style>

<div class="container">
    <a href="SelfStorageAppointmentIndex.php" class="back-btn">← Back to Appointment Dashboard</a>
    <h2>Create Storage Appointment</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="SelfStorageAppointmentStore.php" method="POST">
        <input type="hidden" name="action" value="create_storage_appointment">
        <input type="hidden" name="storage_user_id" value="<?= $storage_user_id; ?>">

        <label>Appointment Date & Time</label>
        <input type="datetime-local" name="appointment_date" required>

        <button type="submit">Create Appointment</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>