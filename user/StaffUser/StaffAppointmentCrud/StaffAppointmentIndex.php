<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

/* ================= DONOR APPOINTMENTS ================= */
$donor_query = "SELECT 
        a.appointment_id,
        a.appointment_date,
        a.type,
        a.status,
        u.first_name,
        u.last_name
    FROM appointments a
    JOIN donors_users u ON a.user_id = u.donor_id
    WHERE a.user_type = 'donor'
    ORDER BY a.appointment_id DESC";
$donor_result = mysqli_query($conn, $donor_query);

/* ================= RECIPIENT APPOINTMENTS ================= */
$recipient_query = "SELECT 
        a.appointment_id,
        a.appointment_date,
        a.type,
        a.status,
        u.first_name,
        u.last_name
    FROM appointments a
    JOIN recipients_users u ON a.user_id = u.recipient_id
    WHERE a.user_type = 'recipient'
    ORDER BY a.appointment_id DESC";
$recipient_result = mysqli_query($conn, $recipient_query);

/* ================= SELF STORAGE APPOINTMENTS ================= */
$storage_query = "SELECT 
        a.appointment_id,
        a.appointment_date,
        a.type,
        a.status,
        u.first_name,
        u.last_name
    FROM appointments a
    JOIN self_storage_users u ON a.user_id = u.storage_user_id
    WHERE a.user_type = 'storage'
    ORDER BY a.appointment_id DESC";
$storage_result = mysqli_query($conn, $storage_query);
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
.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }
.action-btn {
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    color: white;
    font-size: 12px;
}

.edit-btn { background: orange; }
.delete-btn { background: red; }

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 12px;
}

.green { background: green; }
.red { background: red; }
.yellow { background: orange; }

.section-divider {
    margin-top: 40px;
    border-top: 2px solid #eee;
    padding-top: 20px;
}

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
        <h2>Appointment Management</h2>
        <a href="../StaffDashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <!-- ================= DONOR APPOINTMENTS ================= -->
    <div class="top-bar" style="margin-top: 20px;">
        <h3>Donor Appointments</h3>
        <a href="StaffAppointmentDonorCrud/StaffAppointmentDonorCreate.php" class="create-btn">
            + Add Donor Appointment
        </a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Donor Name</th>
            <th>Date</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($donor_result && mysqli_num_rows($donor_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($donor_result)): ?>
                <tr>
                    <td><?= $row['appointment_id']; ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
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
    <?php if ($status === 'scheduled'): ?>
        <a href="StaffAppointmentDonorCrud/StaffAppointmentDonorUpdate.php?id=<?= $row['appointment_id']; ?>" 
           class="action-btn edit-btn">Edit</a>

        <a href="StaffAppointmentDonorCrud/StaffAppointmentDonorDelete.php?id=<?= $row['appointment_id']; ?>" 
           class="action-btn delete-btn"
           onclick="return confirm('Are you sure you want to delete this appointment?');">
           Delete
        </a>
    <?php else: ?>
        <!-- No actions for cancelled or completed appointments -->
        -
    <?php endif; ?>
</td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No donor appointments found.</td></tr>
        <?php endif; ?>
    </table>

    <div class="section-divider"></div>

    <!-- ================= RECIPIENT APPOINTMENTS ================= -->
    <div class="top-bar">
        <h3>Recipient Appointments</h3>
        <a href="StaffAppointmentRecipientCrud/StaffAppointmentRecipientCreate.php" class="create-btn">
            + Add Recipient Appointment
        </a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Recipient Name</th>
            <th>Date</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($recipient_result && mysqli_num_rows($recipient_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($recipient_result)): ?>
                <tr>
                    <td><?= $row['appointment_id']; ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
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
    <?php if ($status === 'scheduled'): ?>
        <a href="StaffAppointmentRecipientCrud/StaffAppointmentRecipientUpdate.php?id=<?= $row['appointment_id']; ?>" 
           class="action-btn edit-btn">Edit</a>

        <a href="StaffAppointmentRecipientCrud/StaffAppointmentRecipientDelete.php?id=<?= $row['appointment_id']; ?>" 
           class="action-btn delete-btn"
           onclick="return confirm('Are you sure you want to delete this appointment?');">
           Delete
        </a>
    <?php else: ?>
        <!-- No actions for cancelled or completed appointments -->
        -
    <?php endif; ?>
</td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No recipient appointments found.</td></tr>
        <?php endif; ?>
    </table>

    <div class="section-divider"></div>

    <!-- ================= SELF STORAGE APPOINTMENTS ================= -->
    <div class="top-bar">
        <h3>Self Storage Appointments</h3>
        <a href="StaffAppointmentSelfStorageCrud/StaffAppointmentSelfStorageCreate.php" class="create-btn">
            + Add Storage Appointment
        </a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>User Name</th>
            <th>Date</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($storage_result && mysqli_num_rows($storage_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($storage_result)): ?>
                <tr>
                    <td><?= $row['appointment_id']; ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
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
    <?php if ($status === 'scheduled'): ?>
        <a href="StaffAppointmentSelfStorageCrud/StaffAppointmentSelfStorageUpdate.php?id=<?= $row['appointment_id']; ?>" 
           class="action-btn edit-btn">Edit</a>

        <a href="StaffAppointmentSelfStorageCrud/StaffAppointmentSelfStorageDelete.php?id=<?= $row['appointment_id']; ?>" 
           class="action-btn delete-btn"
           onclick="return confirm('Are you sure you want to delete this appointment?');">
           Delete
        </a>
    <?php else: ?>
        <!-- No actions for cancelled or completed appointments -->
        -
    <?php endif; ?>
</td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No storage appointments found.</td></tr>
        <?php endif; ?>
    </table>

</div>

<?php include('../../../includes/footer.php'); ?>