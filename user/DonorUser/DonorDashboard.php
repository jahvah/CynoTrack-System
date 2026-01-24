<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");

// Ensure user is logged in and is donor
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit;
}

$donor_id = $_SESSION['role_user_id'];

// Fetch donor profile
$stmt = $conn->prepare("SELECT * FROM donors_users WHERE donor_id = ?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();

// Fetch scheduled appointments
$stmt = $conn->prepare("
    SELECT * FROM appointments 
    WHERE donor_id = ? AND status = 'scheduled' 
    ORDER BY appointment_date ASC
");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$appointments = $stmt->get_result();

// Fetch past donations (completed appointments)
$stmt = $conn->prepare("
    SELECT a.*, s.unique_code, s.status AS specimen_status
    FROM appointments a
    LEFT JOIN specimens s ON s.donor_id = a.donor_id
    WHERE a.donor_id = ? AND a.status = 'completed'
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$history = $stmt->get_result();

// Fetch specimens linked to donor
$stmt = $conn->prepare("SELECT * FROM specimens WHERE donor_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$specimens = $stmt->get_result();

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

<?php include("../../includes/footer.php"); ?>
