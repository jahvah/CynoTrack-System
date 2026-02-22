<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// Admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}
?>

<!--style toh para notif ng success or hindi-->
<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input, select { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
    cursor: pointer;
}
.message {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 5px;
}
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
.back-btn:hover {
    background: #333;
}
</style>

<div class="container">

    <a href="AdminDonorIndex.php" class="back-btn">← Back to Dashboard</a>
    <h2>Add New Donor</h2>

        <!-- Display messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="AdminDonorStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminDonorStore">

        <!-- Account Info -->
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <!-- Donor Info -->
        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>Medical History</label>
        <textarea name="medical_history" rows="4"></textarea>

        <label>Evaluation Status</label>
        <select name="evaluation_status" required>
            <option value="pending" selected>Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>

        <label>Height (cm)</label>
        <input type="number" name="height_cm" min="50" max="250">

        <label>Weight (kg)</label>
        <input type="number" name="weight_kg" min="20" max="200">

        <label>Eye Color</label>
        <input type="text" name="eye_color">

        <label>Hair Color</label>
        <input type="text" name="hair_color">

        <label>Blood Type</label>
        <input type="text" name="blood_type">

        <label>Ethnicity</label>
        <input type="text" name="ethnicity">

        <label>Profile Image</label>
        <input type="file" name="profile_image">

        <label>Medical History PDF</label>
        <input type="file" name="medical_document" accept="application/pdf">
        <small>Upload PDF file only (optional)</small>

        <button type="submit">Create Donor</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
