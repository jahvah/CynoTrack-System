<?php
session_start();
include("../../includes/header.php");
include("../../includes/config.php");

if (isset($_SESSION['flash_message'])) {
    echo "<p style='color:green'>".htmlspecialchars($_SESSION['flash_message'])."</p>";
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}


$account_id = $_SESSION['account_id'];

// Fetch donor image only
$stmt = $conn->prepare("SELECT donor_id, profile_image FROM donors_users WHERE account_id=?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();

if (!$donor) {
    die("<div class='alert alert-danger'>Profile not found.</div>");
}
?>

<div class="container mt-5">
    <h2>Edit Donor Profile</h2>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Profile updated successfully!</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm" style="max-width:700px">
        <form method="POST" enctype="multipart/form-data" action="DonorStore.php">

            <input type="hidden" name="action" value="update_profile">

            <!-- Profile Image -->
            <div class="text-center mb-3">
                <img src="<?= !empty($donor['profile_image']) 
                    ? '../../uploads/'.$donor['profile_image'] 
                    : '../../uploads/default.png' ?>"
                    class="rounded-circle mb-2" width="120" height="120">
                <input type="file" name="profile_image" class="form-control mt-2">
            </div>

            <!-- EMPTY INPUTS (PARTIAL UPDATE) -->
            <input type="text" name="first_name" class="form-control mb-2" placeholder="First Name">
            <input type="text" name="last_name" class="form-control mb-2" placeholder="Last Name">

            <textarea name="medical_history" class="form-control mb-2" placeholder="Medical History"></textarea>

            <div class="row">
                <div class="col">
                    <input type="number" name="height_cm" class="form-control mb-2" placeholder="Height (cm)">
                </div>
                <div class="col">
                    <input type="number" name="weight_kg" class="form-control mb-2" placeholder="Weight (kg)">
                </div>
            </div>

            <input type="text" name="eye_color" class="form-control mb-2" placeholder="Eye Color">
            <input type="text" name="hair_color" class="form-control mb-2" placeholder="Hair Color">
            <input type="text" name="blood_type" class="form-control mb-2" placeholder="Blood Type">
            <input type="text" name="ethnicity" class="form-control mb-3" placeholder="Ethnicity">

            <button class="btn btn-primary">Update Profile</button>
            <a href="DonorDashboard.php" class="btn btn-secondary">Back</a>
            
        </form>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
