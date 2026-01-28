<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");

// Ensure user is logged in and is Self Storage user
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-storage') {
    header("Location: ../login.php");
    exit;
}

$storage_user_id = $_SESSION['role_user_id'];

// Fetch self storage user profile
$stmt = $conn->prepare("SELECT * FROM self_storage_users WHERE storage_user_id = ?");
$stmt->bind_param("i", $storage_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("<div class='alert alert-danger'>Profile not found.</div>");
}
?>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($user['first_name']); ?>!</h2>

    <!-- SELF STORAGE PROFILE -->
    <section>
        <h3>Your Profile</h3>

        <a href="SelfStorageEditProfile.php" class="btn btn-primary" style="margin-bottom:10px;">
            Edit Profile
        </a>

        <!-- PROFILE IMAGE -->
        <div style="margin-bottom:15px;">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($user['profile_image']); ?>"
                     alt="Profile Image"
                     width="150"
                     height="150"
                     style="object-fit:cover;border-radius:50%;border:1px solid #ccc;">
            <?php else: ?>
                <p><em>No profile image uploaded.</em></p>
            <?php endif; ?>
        </div>

        <p><strong>Full Name:</strong>
            <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>
        </p>

        <p><strong>Storage Details:</strong><br>
            <?= nl2br(htmlspecialchars($user['storage_details'] ?? 'Not provided')); ?>
        </p>
    </section>
</div>

<?php include("../../includes/footer.php"); ?>
