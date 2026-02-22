<?php
session_start();
include("../../includes/header.php");
include("../../includes/config.php");

//session flash message for success or error
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success'>".htmlspecialchars($_SESSION['flash_message'])."</div>";
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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Edit Donor Profile</h3>
                </div>
                <div class="card-body">

                    <?php if (isset($_GET['updated'])): ?>
                        <div class="alert alert-success">Profile updated successfully!</div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" action="DonorStore.php">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Profile Image -->
                        <div class="text-center mb-4">
                            <img src="<?= !empty($donor['profile_image']) 
                                ? '../../uploads/'.$donor['profile_image'] 
                                : '../../uploads/default.png' ?>"
                                class="rounded-circle border border-secondary mb-2"
                                width="140" height="140"
                                style="object-fit: cover;">
                            <input type="file" name="profile_image" class="form-control mt-2">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" name="first_name" class="form-control" placeholder="First Name">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="last_name" class="form-control" placeholder="Last Name">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="number" name="height_cm" class="form-control" placeholder="Height (cm)">
                            </div>
                            <div class="col-md-6">
                                <input type="number" name="weight_kg" class="form-control" placeholder="Weight (kg)">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" name="eye_color" class="form-control" placeholder="Eye Color">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="hair_color" class="form-control" placeholder="Hair Color">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <input type="text" name="blood_type" class="form-control" placeholder="Blood Type">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="ethnicity" class="form-control" placeholder="Ethnicity">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Profile</button>
                            <a href="DonorDashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
