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
$stmt = $conn->prepare("
    SELECT r.*, a.status AS account_status
    FROM recipients_users r
    INNER JOIN accounts a ON r.account_id = a.account_id
    WHERE r.recipient_id = ?
");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Check if account does not exist
if ($result->num_rows === 0) {
    $_SESSION['flash_message'] = "Account does not exist. Please contact admin.";
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}

$recipient = $result->fetch_assoc();

// ✅ Check if account is inactive or pending
if ($recipient['account_status'] !== 'active') {
    if ($recipient['account_status'] === 'inactive') {
        $_SESSION['flash_message'] = "Your account is inactive. Please contact admin.";
    } elseif ($recipient['account_status'] === 'pending') {
        $_SESSION['flash_message'] = "Your account is pending approval. Please wait for admin approval.";
    }
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}
?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">
                Welcome, <?= htmlspecialchars($recipient['first_name']); ?>!
            </h2>
        </div>

        <div class="card-body">

            <!-- Edit Profile Button -->
            <div class="mb-4">
                <a href="RecipientEditProfile.php" class="btn btn-outline-primary">
                    Edit Profile
                </a>
            </div>

            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-4 text-center mb-4">
                    <?php if (!empty($recipient['profile_image'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>"
                             alt="Profile Image"
                             class="img-fluid rounded-circle border border-secondary"
                             style="width:180px; height:180px; object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                             style="width:180px; height:180px; border:1px solid #ccc;">
                            <em>No Image</em>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Profile Details -->
                <div class="col-md-8">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Full Name:</strong>
                            <?= htmlspecialchars($recipient['first_name'] . " " . $recipient['last_name']); ?>
                        </li>

                        <li class="list-group-item">
                            <strong>Preferences:</strong><br>
                            <?= nl2br(htmlspecialchars($recipient['preferences'] ?? 'None')); ?>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
