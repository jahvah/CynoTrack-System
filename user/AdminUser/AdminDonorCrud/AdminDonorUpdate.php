<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$donor_id = intval($_GET['id']);

// fetch donor + account info
$stmt = $conn->prepare("
    SELECT d.donor_id, d.account_id, d.first_name, d.last_name, d.profile_image, d.medical_history, d.evaluation_status, d.height_cm, d.weight_kg, d.eye_color, d.hair_color, d.blood_type, d.ethnicity,
    a.username, a.email, a.status

    FROM donors_users d
    JOIN accounts a ON d.account_id = a.account_id
    WHERE d.donor_id = ?
");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$donor = $result->fetch_assoc();
?>


<!--style toh para notif ng success or hindi-->
<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label, select { display: block; margin-top: 15px; }
input, select { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
}
.locked { background: #eee; }
img {
    width: 120px;
    border-radius: 8px;
    display: block;
}
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }

</style>

<div class="container">
    <h2>Update Donor</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="AdminDonorStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminDonorUpdate">
        <input type="hidden" name="donor_id" value="<?= $donor['donor_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $donor['account_id']; ?>">

        <!--ipapakita lang ang username at email, hindi pwedeng baguhin-->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($donor['username']); ?>" class="locked" disabled>

        <!--ipapakita lang ang username at email, hindi pwedeng baguhin-->
        <label>Email</label>
        <input type="text" value="<?= htmlspecialchars($donor['email']); ?>" class="locked" disabled>

        <label>Current Profile Image</label>
        <?php if (!empty($donor['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <!--fields to update-->
        <label>New First Name</label>
        <input type="text" name="first_name" placeholder="Enter new first name">

        <label>New Last Name</label>
        <input type="text" name="last_name" placeholder="Enter new last name">

        <label>Change Profile Image</label>
        <input type="file" name="profile_image">

        <label>New Medical History</label>
        <input type="text" name="medical_history" placeholder="Enter new medical history">

        <label>New Evaluation Status</label>
        <select name="evaluation_status_select">
            <option value="pending" <?= $donor['evaluation_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="approved" <?= $donor['evaluation_status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?= $donor['evaluation_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
         <label>New Height (cm)</label>
        <input type="number" name="height_cm" min="50" max="250" placeholder="Enter new height in cm"> 

        <label>New Weight (kg)</label>
        <input type="number" name="weight_kg" min="20" max="200" placeholder="Enter new weight in kg">

        <label>New Eye Color</label>
        <input type="text" name="eye_color" placeholder="Enter new eye color">

        <label>New Hair Color</label>
        <input type="text" name="hair_color" placeholder="Enter new hair color">

        <label>New Blood Type</label>
        <input type="text" name="blood_type" placeholder="Enter new blood type">
        <label>

        <label>New Ethnicity</label>
        <input type="text" name="ethnicity" placeholder="Enter new ethnicity">
        <label>

        <label>Account Status</label>
        <select name="status">
            <option value="active" <?= $donor['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= $donor['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="pending" <?= $donor['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
        </select>

        <button type="submit">Update Donor</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>