<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");

// Ensure user is logged in and is Self Storage user
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-storage') {
    header("Location: ../login.php");
    exit;
}

$account_id = $_SESSION['account_id'] ?? null;
$storage_user_id = $_SESSION['role_user_id'] ?? null;

if (!$account_id || !$storage_user_id) {
    header("Location: ../login.php");
    exit;
}

// Fetch self storage profile and account status
$stmt = $conn->prepare("
    SELECT s.*, a.status AS account_status, a.username, a.email
    FROM self_storage_users s
    INNER JOIN accounts a ON s.account_id = a.account_id
    WHERE s.storage_user_id = ?
");
$stmt->bind_param("i", $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if account does not exist
if ($result->num_rows === 0) {
    $_SESSION['flash_message'] = "Account does not exist. Please contact admin.";
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}

$user = $result->fetch_assoc();

// Check if account is inactive or pending
if ($user['account_status'] !== 'active') {
    if ($user['account_status'] === 'inactive') {
        $_SESSION['flash_message'] = "Your account is inactive. Please contact admin.";
    } elseif ($user['account_status'] === 'pending') {
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
            <h2 class="mb-0">Welcome, <?= htmlspecialchars($user['first_name']); ?>!</h2>
        </div>
        <div class="card-body">
            <!-- Edit Profile Button -->
            <div class="mb-4">
                <a href="SelfStorageEditProfile.php" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </a>
            </div>

            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-4 text-center mb-4">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($user['profile_image']); ?>"
                             alt="Profile Image"
                             class="img-fluid rounded-circle border border-secondary"
                             style="width:180px; height:180px; object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                             style="width:180px; height:180px; border:1px solid #ccc;">
                            <em>No image</em>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Profile Details -->
                <div class="col-md-8">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Full Name:</strong> <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></li>
                        <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></li>
                        <li class="list-group-item"><strong>Storage Details:</strong><br><?= nl2br(htmlspecialchars($user['storage_details'] ?? 'Not provided')); ?></li>
                       
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
