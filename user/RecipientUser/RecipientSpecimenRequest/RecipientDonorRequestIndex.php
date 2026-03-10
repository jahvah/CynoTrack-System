<?php
session_start();
include("../../../includes/config.php");
include("../../../includes/header.php");

// Ensure recipient is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../../login.php");
    exit;
}

$recipient_id = $_SESSION['role_user_id'];
$donor_id = $_GET['id'] ?? 0;

if (!$donor_id) {
    echo "<p class='text-danger'>Invalid donor.</p>";
    exit;
}

// Fetch donor info
$stmt = $conn->prepare("
    SELECT 
        first_name,
        last_name,
        profile_image,
        medical_history,
        height_cm,
        weight_kg,
        eye_color,
        hair_color,
        blood_type,
        ethnicity
    FROM donors_users
    WHERE donor_id = ?
");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

$stmt2 = $conn->prepare("
    SELECT SUM(quantity) AS available_quantity
    FROM specimens
    WHERE specimen_owner_type = 'donor'
    AND specimen_owner_id = ?
    AND status = 'stored'
");
$stmt2->bind_param("i", $donor_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$data = $result2->fetch_assoc();

$available_quantity = $data['available_quantity'] ?? 0;

if ($result->num_rows === 0) {
    echo "<p class='text-danger'>Donor not found.</p>";
    exit;
}

$donor = $result->fetch_assoc();
?>

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Donor Profile</h3>
            
        </div>

        <div class="card-body">
            <div class="row">

                <!-- Donor Image -->
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <?php if (!empty($donor['profile_image'])): ?>
                        <img src="../../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>"
                            class="img-fluid rounded-circle border border-2"
                            style="width:200px;height:200px;object-fit:cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light rounded-circle border"
                             style="width:200px;height:200px;">
                            <em>No Image</em>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Donor Info -->
                <div class="col-md-8">
                    <ul class="list-group list-group-flush">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <li class="list-group-item"><strong>Full Name:</strong> <?= htmlspecialchars($donor['first_name']." ".$donor['last_name']); ?></li>
                        <li class="list-group-item"><strong>Medical History:</strong><br><?= nl2br(htmlspecialchars($donor['medical_history'] ?? 'None')); ?></li>
                        <li class="list-group-item"><strong>Height:</strong> <?= htmlspecialchars($donor['height_cm'] ?? 'N/A'); ?> cm</li>
                        <li class="list-group-item"><strong>Weight:</strong> <?= htmlspecialchars($donor['weight_kg'] ?? 'N/A'); ?> kg</li>
                        <li class="list-group-item"><strong>Eye Color:</strong> <?= htmlspecialchars($donor['eye_color'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Hair Color:</strong> <?= htmlspecialchars($donor['hair_color'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Blood Type:</strong> <?= htmlspecialchars($donor['blood_type'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Ethnicity:</strong> <?= htmlspecialchars($donor['ethnicity'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Available Specimens:</strong> <?= htmlspecialchars($available_quantity); ?></li>
                    </ul>
                </div>
            </div>

            <hr class="my-4">

            <!-- Request Specimen Form -->
            <div class="card border-info shadow-sm p-4">
                <h5 class="mb-3">Request Specimen</h5>

                <form method="POST" action="RecipientDonorRequestStore.php" enctype="multipart/form-data">

                    <input type="hidden" name="recipient_id" value="<?= $recipient_id ?>">
                    <input type="hidden" name="donor_id" value="<?= $donor_id ?>">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="decreaseQty()">-</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1"
                               class="form-control text-center" style="width:80px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="increaseQty()">+</button>
                    </div>

                    <div id="receiptBox" style="display:none;" class="mb-3">
                        <label class="form-label"><strong>Upload Payment Receipt</strong></label>
                        <input type="file" name="receipt" class="form-control" accept="image/*,.pdf" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="showReceipt()">Request Specimen</button>
                        <button type="submit" id="submitBtn" class="btn btn-primary" style="display:none;">Submit Request</button>
                        <a href="../RecipientDashboard.php" class="btn btn-secondary">Back</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
function increaseQty() {
    let qty = document.getElementById("quantity");
    if (parseInt(qty.value) < <?= $available_quantity ?>) {
        qty.value = parseInt(qty.value) + 1;
    }
}

function decreaseQty() {
    let qty = document.getElementById("quantity");
    if (qty.value > 1) qty.value = parseInt(qty.value) - 1;
}

function showReceipt() {
    document.getElementById("receiptBox").style.display = "block";
    document.getElementById("submitBtn").style.display = "inline-block";
}
</script>

<?php include("../../../includes/footer.php"); ?>