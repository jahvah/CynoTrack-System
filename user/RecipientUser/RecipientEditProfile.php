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

//fetch image only
$stmt = $conn->prepare("SELECT recipient_id, profile_image 
                        FROM recipients_users WHERE account_id=?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();

if (!$recipient) {
    die("<div class='alert alert-danger text-center'>Profile not found.</div>");
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Edit Recipient Profile</h3>
                </div>

                <div class="card-body">
                    
                    <?php if (isset($_GET['updated'])): ?>
                        <div class="alert alert-success text-center">
                            Profile updated successfully!
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" action="RecipientStore.php">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Profile Image -->
                        <div class="text-center mb-4">
                            <img src="<?= !empty($recipient['profile_image']) 
                                ? '../../uploads/'.$recipient['profile_image'] 
                                : '../../uploads/default.png' ?>"
                                class="rounded-circle border border-secondary mb-2"
                                width="140" height="140"
                                style="object-fit: cover;">

                            <input type="file" name="profile_image" class="form-control mt-2">
                        </div>

                        <!-- Name Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" 
                                       name="first_name" 
                                       class="form-control" 
                                       placeholder="First Name">
                            </div>

                            <div class="col-md-6">
                                <input type="text" 
                                       name="last_name" 
                                       class="form-control" 
                                       placeholder="Last Name">
                            </div>
                        </div>

                        <!-- Preferences -->
                        <div class="mb-4">
                            <textarea name="preferences" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Enter your preferences"></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-primary">
                                Update Profile
                            </button>
                            <a href="RecipientDashboard.php" class="btn btn-secondary">
                                Back
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>
