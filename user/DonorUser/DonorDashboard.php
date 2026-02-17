<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");

// Ensure user is logged in and is donor
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: ../login.php");
    exit;
}

$account_id = $_SESSION['account_id'] ?? null;

if (!$account_id) {
    header("Location: ../login.php");
    exit;
}

// Fetch donor profile and account status
$stmt = $conn->prepare("
    SELECT d.*, a.status AS account_status
    FROM donors_users d
    INNER JOIN accounts a ON d.account_id = a.account_id
    WHERE d.account_id = ?
");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();

// Check if account is inactive or pending
if ($donor['account_status'] !== 'active') {
    if ($donor['account_status'] === 'inactive') {
        $_SESSION['flash_message'] = "Your account is inactive. Please contact admin.";
    } elseif ($donor['account_status'] === 'pending') {
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
            <h2 class="mb-0">Welcome, <?= htmlspecialchars($donor['first_name']); ?>!</h2>
        </div>
        <div class="card-body">
            <!-- Edit Profile Button -->
            <div class="mb-4">
                <a href="DonorEditProfile.php" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </a>
            </div>

            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-4 text-center mb-4">
                    <?php if (!empty($donor['profile_image'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>"
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
                        <li class="list-group-item"><strong>Full Name:</strong> <?= htmlspecialchars($donor['first_name'] . " " . $donor['last_name']); ?></li>
                        <li class="list-group-item"><strong>Height:</strong> <?= htmlspecialchars($donor['height_cm'] ?? 'N/A'); ?> cm</li>
                        <li class="list-group-item"><strong>Weight:</strong> <?= htmlspecialchars($donor['weight_kg'] ?? 'N/A'); ?> kg</li>
                        <li class="list-group-item"><strong>Eye Color:</strong> <?= htmlspecialchars($donor['eye_color'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Hair Color:</strong> <?= htmlspecialchars($donor['hair_color'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Blood Type:</strong> <?= htmlspecialchars($donor['blood_type'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Ethnicity:</strong> <?= htmlspecialchars($donor['ethnicity'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Medical History:</strong><br><?= nl2br(htmlspecialchars($donor['medical_history'] ?? 'None')); ?></li>
                        <li class="list-group-item"><strong>Evaluation Status:</strong> <?= ucfirst($donor['evaluation_status']); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
