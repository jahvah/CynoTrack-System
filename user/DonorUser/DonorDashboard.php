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
    // Log out donor
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}

// Now you can safely show the dashboard for active accounts
?>


<div class="container">
    <h2>Welcome, <?= htmlspecialchars($donor['first_name']); ?>!</h2>

    <!-- DONOR PROFILE -->
    <section>
        <h3>Your Profile</h3>

        <a href="DonorEditProfile.php" class="btn btn-primary" style="margin-bottom:10px;">
            Edit Profile
        </a>

        <!-- PROFILE IMAGE -->
        <div style="margin-bottom:15px;">
            <?php if (!empty($donor['profile_image'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>"
                     alt="Profile Image"
                     width="150"
                     height="150"
                     style="object-fit:cover;border-radius:50%;border:1px solid #ccc;">
            <?php else: ?>
                <p><em>No profile image uploaded.</em></p>
            <?php endif; ?>
        </div>

        <p><strong>Full Name:</strong>
            <?= htmlspecialchars($donor['first_name'] . " " . $donor['last_name']); ?>
        </p>

        <p><strong>Height:</strong> <?= htmlspecialchars($donor['height_cm'] ?? 'N/A'); ?> cm</p>
        <p><strong>Weight:</strong> <?= htmlspecialchars($donor['weight_kg'] ?? 'N/A'); ?> kg</p>
        <p><strong>Eye Color:</strong> <?= htmlspecialchars($donor['eye_color'] ?? 'N/A'); ?></p>
        <p><strong>Hair Color:</strong> <?= htmlspecialchars($donor['hair_color'] ?? 'N/A'); ?></p>
        <p><strong>Blood Type:</strong> <?= htmlspecialchars($donor['blood_type'] ?? 'N/A'); ?></p>
        <p><strong>Ethnicity:</strong> <?= htmlspecialchars($donor['ethnicity'] ?? 'N/A'); ?></p>

        <p><strong>Medical History:</strong><br>
            <?= nl2br(htmlspecialchars($donor['medical_history'] ?? 'None')); ?>
        </p>

        <p><strong>Evaluation Status:</strong>
            <?= ucfirst($donor['evaluation_status']); ?>
        </p>

        <p><strong>Active Status:</strong>
            <?= $donor['active_status'] ? 'Active' : 'Inactive'; ?>
        </p>
    </section>
</div>

<?php include("../../includes/footer.php"); ?>
