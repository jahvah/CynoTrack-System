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
// Session check
if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

$account_id = $_SESSION['account_id'];

// Fetch self-storage image only
$stmt = $conn->prepare("SELECT storage_user_id, profile_image FROM self_storage_users WHERE account_id=?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("<div class='alert alert-danger'>Profile not found.</div>");
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Edit Self Storage Profile</h3>
                </div>
                <div class="card-body">

                    <?php if (isset($_GET['updated'])): ?>
                        <div class="alert alert-success">Profile updated successfully!</div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" action="SelfStorageStore.php">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Profile Image -->
                        <div class="text-center mb-4">
                            <img src="<?= !empty($user['profile_image']) 
                                ? '../../uploads/'.$user['profile_image'] 
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

                        <!-- Storage Details -->
                        <div class="mb-3">
                            <textarea name="storage_details" class="form-control" rows="4" placeholder="Storage details or preferences"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Profile</button>
                            <a href="SelfStorageDashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Back</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
