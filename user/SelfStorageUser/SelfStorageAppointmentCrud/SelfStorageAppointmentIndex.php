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
$storage_query = mysqli_query($conn, "SELECT storage_user_id 
                                      FROM self_storage_users 
                                      WHERE account_id = '$account_id' 
                                      LIMIT 1");
$storage_data = mysqli_fetch_assoc($storage_query);

if (!$storage_data) {
    echo "<div class='container'><div class='message error'>Storage user record not found.</div></div>";
    include('../../../includes/footer.php');
    exit();
}

$storage_user_id = $storage_data['storage_user_id'];

/* ================= STORAGE USER OWN APPOINTMENTS ================= */
$appointment_query = "SELECT 
        appointment_id,
        appointment_date,
        type,
        status
    FROM appointments
    WHERE user_type = 'storage' 
      AND user_id = '$storage_user_id'
    ORDER BY appointment_id DESC";

$appointment_result = mysqli_query($conn, $appointment_query);
?>

<style>
.container { padding: 30px; }

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.create-btn {
    padding: 10px 18px;
    background: green;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 13px;
}

th, td {
    padding: 8px;
    border: 1px solid #ccc;
    text-align: center;
}

th {
    background: #007bff;
    color: white;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 12px;
}

.green { background: green; }
.red { background: red; }
.yellow { background: orange; }

.action-btn {
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    color: white;
    font-size: 12px;
}

.edit-btn { background: orange; }
.delete-btn { background: red; }

.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }

.back-btn {
    padding: 10px 18px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-right: 10px;
}
</style>

<div class="container">

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="top-bar">
        <h2>My Storage Appointments</h2>
        <div>
            <a href="../SelfStorageDashboard.php" class="back-btn">← Back to Dashboard</a>
            <a href="SelfStorageAppointmentCreate.php" class="create-btn">
                + Create Appointment
            </a>
        </div>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($appointment_result && mysqli_num_rows($appointment_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($appointment_result)): ?>
                <tr>
                    <td><?= $row['appointment_id']; ?></td>
                    <td><?= date("M d, Y h:i A", strtotime($row['appointment_date'])); ?></td>
                    <td><?= ucfirst($row['type']); ?></td>
                    <td>
                        <?php
                        $status = $row['status'];
                        $class = ($status == 'completed') ? 'green' : (($status == 'cancelled') ? 'red' : 'yellow');
                        echo "<span class='badge $class'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td>
                        <a href="SelfStorageAppointmentUpdate.php?id=<?= $row['appointment_id']; ?>" 
                           class="action-btn edit-btn">Edit</a>

                        <a href="SelfStorageAppointmentDelete.php?id=<?= $row['appointment_id']; ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this appointment?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No appointments found.</td></tr>
        <?php endif; ?>
    </table>

</div>

<?php include('../../../includes/footer.php'); ?>