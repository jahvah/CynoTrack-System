<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");
include("../../includes/alert.php");

// Ensure donor logged in
if (!isset($_SESSION['account_id'])) {
    die("Invalid session. Please login.");
}

$account_id = $_SESSION['account_id'];

// Fetch donor data using account_id (STANDARDIZED)
$stmt = $conn->prepare("SELECT * FROM donors_users WHERE account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();
?>

<h2>Complete Your Donor Profile</h2>

<form method="POST" action="DonorStore.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="update_profile">

    <label>Profile Image</label>
    <input type="file" name="profile_image" accept="image/*">
    <?php if (!empty($donor['profile_image'])): ?>
        <img src="../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>" width="120">
    <?php endif; ?>

    <label>First Name</label>
    <input type="text" name="first_name" value="<?= htmlspecialchars($donor['first_name'] ?? '') ?>">

    <label>Last Name</label>
    <input type="text" name="last_name" value="<?= htmlspecialchars($donor['last_name'] ?? '') ?>">

    <label>Height (cm)</label>
    <input type="number" name="height_cm" value="<?= htmlspecialchars($donor['height_cm'] ?? '') ?>">

    <label>Weight (kg)</label>
    <input type="number" name="weight_kg" value="<?= htmlspecialchars($donor['weight_kg'] ?? '') ?>">

    <label>Eye Color</label>
    <input type="text" name="eye_color" value="<?= htmlspecialchars($donor['eye_color'] ?? '') ?>">

    <label>Hair Color</label>
    <input type="text" name="hair_color" value="<?= htmlspecialchars($donor['hair_color'] ?? '') ?>">

    <label>Blood Type</label>
    <input type="text" name="blood_type" value="<?= htmlspecialchars($donor['blood_type'] ?? '') ?>">

    <label>Ethnicity</label>
    <input type="text" name="ethnicity" value="<?= htmlspecialchars($donor['ethnicity'] ?? '') ?>">

    <label>Medical History</label>
    <textarea name="medical_history"><?= htmlspecialchars($donor['medical_history'] ?? '') ?></textarea>

    <button type="submit">Save Profile</button>
</form>

<?php include("../../includes/footer.php"); ?>
