<?php
session_start();
include("../../includes/header.php");
include("../../includes/config.php");

if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}
$account_id = $_SESSION['account_id'];

$stmt = $conn->prepare("SELECT recipient_id, profile_image FROM recipients_users WHERE account_id=?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();

if (!$recipient) {
    die("<div class='alert alert-danger'>Profile not found.</div>");
}
?>

<div class="container mt-5">
    <h2>Edit Recipient Profile</h2>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Profile updated successfully!</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm" style="max-width:700px">
        <form method="POST" enctype="multipart/form-data" action="RecipientStore.php">

            <input type="hidden" name="action" value="update_profile">

            <!-- Profile Image -->
            <div class="text-center mb-3">
                <img src="<?= !empty($recipient['profile_image']) 
                    ? '../../uploads/'.$recipient['profile_image'] 
                    : '../../uploads/default.png' ?>"
                    class="rounded-circle mb-2" width="120" height="120">
                <input type="file" name="profile_image" class="form-control mt-2">
            </div>

            <!-- First Name -->
            <input type="text" name="first_name" class="form-control mb-2" placeholder="First Name" value="<?= htmlspecialchars($recipient['first_name'] ?? '') ?>">

            <!-- Last Name -->
            <input type="text" name="last_name" class="form-control mb-2" placeholder="Last Name" value="<?= htmlspecialchars($recipient['last_name'] ?? '') ?>">

            <!-- Preferences -->
            <label>Preferences</label>
            <textarea name="preferences" class="form-control mb-3" placeholder="Enter your preferences"><?= htmlspecialchars($recipient['preferences'] ?? '') ?></textarea>

            <button class="btn btn-primary">Update Profile</button>
            <a href="RecipientDashboard.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
