<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// RECIPIENT access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get logged-in recipient ID
$account_id = $_SESSION['account_id'];
$recipient_query = mysqli_query($conn, "SELECT recipient_id FROM recipients_users WHERE account_id = '$account_id' LIMIT 1");
$recipient_data = mysqli_fetch_assoc($recipient_query);

if (!$recipient_data) {
    echo "<div class='container'><div class='message error'>Recipient record not found.</div></div>";
    include('../../../includes/footer.php');
    exit();
}

$recipient_id = $recipient_data['recipient_id'];
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input, select { 
    width: 100%; 
    padding: 10px; 
    margin: 10px 0; 
    border: 1px solid #ccc; 
    border-radius: 4px; 
    box-sizing: border-box; 
}
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
    <a href="RecipientAppointmentIndex.php" class="back-btn">← Back to Appointment Dashboard</a>
    <h2>Create Appointment</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="RecipientAppointmentStore.php" method="POST">
        <input type="hidden" name="action" value="create_recipient_appointment">
        <input type="hidden" name="recipient_id" value="<?= $recipient_id; ?>">

        <label>Appointment Type</label>
        <select name="type" required>
            <option value="">-- Select Appointment Type --</option>
            <option value="consultation">Consultation</option>
            <option value="release">Release</option>
        </select>

        <label>Appointment Date & Time</label>
        <input type="datetime-local" name="appointment_date" required>

        <button type="submit">Create Appointment</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>