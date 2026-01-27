<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");

// Ensure user is logged in and is recipient
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

$recipient_id = $_SESSION['role_user_id'];

// Fetch recipient profile
$stmt = $conn->prepare("SELECT * FROM recipients_users WHERE recipient_id = ?");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();

// Optionally, you could fetch recipient-related data here
// For example, specimen requests or transactions, if needed
?>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($recipient['first_name']); ?>!</h2>

    <!-- RECIPIENT PROFILE -->
    <section>
        <h3>Your Profile</h3>

        <a href="RecipientEditProfile.php" class="btn btn-primary mb-3">
            Edit Profile
        </a>

        <!-- PROFILE IMAGE -->
        <div style="margin-bottom:15px;">
            <?php if (!empty($recipient['profile_image'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>"
                     alt="Profile Image"
                     width="150"
                     height="150"
                     style="object-fit:cover;border-radius:50%;border:1px solid #ccc;">
            <?php else: ?>
                <p><em>No profile image uploaded.</em></p>
            <?php endif; ?>
        </div>

        <p><strong>Full Name:</strong>
            <?= htmlspecialchars($recipient['first_name'] . " " . $recipient['last_name']); ?>
        </p>

        <p><strong>Preferences:</strong><br>
            <?= nl2br(htmlspecialchars($recipient['preferences'] ?? 'None')); ?>
        </p>
    </section>

    <!-- Optional Section: Specimen Requests / Transactions -->
    <!-- You can add tables here if you have specimen requests or transactions linked to the recipient -->
</div>

<?php include("../../includes/footer.php"); ?>
