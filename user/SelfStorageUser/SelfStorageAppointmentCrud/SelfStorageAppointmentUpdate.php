<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// SELF-STORAGE access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-gitstorage') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Check for appointment ID
if (!isset($_GET['id'])) {
    header("Location: ../SelfStorageAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);
$account_id = $_SESSION['account_id'];

// Get logged-in storage user ID
$storage_stmt = $conn->prepare("SELECT storage_user_id, first_name, last_name FROM self_storage_users WHERE account_id = ? LIMIT 1");
$storage_stmt->bind_param("i", $account_id);
$storage_stmt->execute();
$storage_result = $storage_stmt->get_result();

if ($storage_result->num_rows === 0) {
    header("Location: ../SelfStorageAppointmentIndex.php");
    exit();
}

$storage_data = $storage_result->fetch_assoc();
$storage_user_id = $storage_data['storage_user_id'];

// Fetch ONLY this storage user's appointment
$stmt = $conn->prepare("
    SELECT appointment_date, status, type
    FROM appointments
    WHERE appointment_id = ? 
      AND user_type = 'storage'
      AND user_id = ?
");
$stmt->bind_param("ii", $appointment_id, $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../SelfStorageAppointmentIndex.php");
    exit();
}

$appointment = $result->fetch_assoc();
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label { display: block; margin-top: 15px; }
input, select { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
}
.locked { background:#eee; }

.error { background:#f8d7da; color:#721c24; padding:10px; }
.success { background:#d4edda; color:#155724; padding:10px; }

.back-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 12px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.back-btn:hover { background: #333; }
</style>

<div class="container">
    <h2>Update My Appointment</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="SelfStorageAppointmentStore.php" method="POST">
        <input type="hidden" name="action" value="update_storage_appointment">
        <input type="hidden" name="appointment_id" value="<?= $appointment_id; ?>">

        <label>Storage User Name</label>
        <input type="text" 
               value="<?= htmlspecialchars($storage_data['first_name'] . ' ' . $storage_data['last_name']); ?>" 
               class="locked" 
               disabled>

        <label>Appointment Type</label>
        <select class="locked" disabled>
            <option value="storage" <?= ($appointment['type'] === 'storage') ? 'selected' : ''; ?>>
                Storage
            </option>
            <option value="release" <?= ($appointment['type'] === 'release') ? 'selected' : ''; ?>>
                Release
            </option>
        </select>

        <label>Appointment Date & Time</label>
        <input type="datetime-local" 
               name="appointment_date"
               value="<?= date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" 
               required>

        <label>Appointment Status</label>
        <input type="text" 
               value="<?= ucfirst(htmlspecialchars($appointment['status'])); ?>" 
               class="locked" 
               disabled>

        <button type="submit">Update Appointment</button>
        <br>
        <a href="SelfStorageAppointmentIndex.php" class="back-btn">← Back to My Appointments</a>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>