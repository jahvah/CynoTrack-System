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

/* Fetch donor + account info */
$stmt = $conn->prepare("
    SELECT d.donor_id, d.account_id, d.first_name, d.last_name, d.profile_image, a.username, a.status
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
</style>

<div class="container">
    <h2>Update Donor</h2>

    <form action="AdminDonorStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminDonorUpdate">
        <input type="hidden" name="donor_id" value="<?= $donor['donor_id']; ?>">
        <input type="hidden" name="account_id" value="<?= $donor['account_id']; ?>">

        <!-- Username (locked) -->
        <label>Username</label>
        <input type="text" value="<?= htmlspecialchars($donor['username']); ?>" class="locked" disabled>

        <!-- Current Profile Picture -->
        <label>Current Profile Image</label>
        <?php if (!empty($donor['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>" alt="Profile Image">
        <?php else: ?>
            <p>No image uploaded</p>
        <?php endif; ?>

        <!-- Empty fields to update -->
        <label>New First Name</label>
        <input type="text" name="first_name" placeholder="Enter new first name">

        <label>New Last Name</label>
        <input type="text" name="last_name" placeholder="Enter new last name">

        <label>Change Profile Image</label>
        <input type="file" name="profile_image">

        <!-- New Account Status -->
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
