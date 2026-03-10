<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");

// Ensure user is logged in and is recipient
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

$recipient_id = $_SESSION['role_user_id'];

// Fetch recipient profile
$stmt = $conn->prepare("
    SELECT r.*, a.status AS account_status
    FROM recipients_users r
    INNER JOIN accounts a ON r.account_id = a.account_id
    WHERE r.recipient_id = ?
");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_message'] = "Account does not exist. Please contact admin.";
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}

$recipient = $result->fetch_assoc();

if ($recipient['account_status'] !== 'active') {
    if ($recipient['account_status'] === 'inactive') {
        $_SESSION['flash_message'] = "Your account is inactive. Please contact admin.";
    } elseif ($recipient['account_status'] === 'pending') {
        $_SESSION['flash_message'] = "Your account is pending approval.";
    }
    unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
    header("Location: ../login.php");
    exit;
}
?>

<div class="container mt-5">
<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
<h2 class="mb-0">
Welcome, <?= htmlspecialchars($recipient['first_name']); ?>!
</h2>
</div>

<div class="card-body">

<a href="RecipientEditProfile.php" class="btn btn-outline-primary mb-4">
Edit Profile
</a>

<div class="row">

<div class="col-md-4 text-center mb-4">
<?php if (!empty($recipient['profile_image'])): ?>
<img src="../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>"
class="img-fluid rounded-circle border border-secondary"
style="width:180px;height:180px;object-fit:cover;">
<?php else: ?>
<div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
style="width:180px;height:180px;border:1px solid #ccc;">
<em>No Image</em>
</div>
<?php endif; ?>
</div>

<div class="col-md-8">
<ul class="list-group list-group-flush">
<li class="list-group-item">
<strong>Full Name:</strong>
<?= htmlspecialchars($recipient['first_name']." ".$recipient['last_name']); ?>
</li>

<li class="list-group-item">
<strong>Preferences:</strong><br>
<?= nl2br(htmlspecialchars($recipient['preferences'] ?? 'None')); ?>
</li>
</ul>
</div>

</div>

<hr>

<h3 class="mt-4">Available Donors</h3>

<?php
$search = $_GET['search'] ?? '';

if (!empty($search)) {

    $sql = "SELECT donor_id, first_name, last_name, profile_image
        FROM donors_users
        WHERE evaluation_status = 'approved'
        AND (first_name LIKE ? OR last_name LIKE ?)
        ORDER BY donor_id ASC";

    $stmt = mysqli_prepare($conn,$sql);
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt,"ss",$like,$like);
    mysqli_stmt_execute($stmt);
    $results = mysqli_stmt_get_result($stmt);

    echo "<p>Search results for: <strong>".htmlspecialchars($search)."</strong></p>";

} else {

    $sql = "SELECT donor_id, first_name, last_name, profile_image
        FROM donors_users
        WHERE evaluation_status = 'approved'
        ORDER BY donor_id ASC";

    $results = mysqli_query($conn,$sql);
}

if ($results && mysqli_num_rows($results) > 0) {

echo '<ul class="products" style="list-style:none;padding:0;display:flex;flex-wrap:wrap;">';

while ($row = mysqli_fetch_assoc($results)) {

$image = "../../uploads/no-image.png";

if (!empty($row['profile_image'])) {
$image = "../../uploads/".htmlspecialchars($row['profile_image']);
}

echo '<li style="margin:10px;width:220px;border:1px solid #ddd;padding:10px;border-radius:5px;text-align:center;">';

echo '<h5>'.htmlspecialchars($row['first_name']." ".$row['last_name']).'</h5>';

echo '<div style="margin-bottom:10px;">';
echo '<img src="'.$image.'" style="width:150px;height:150px;object-fit:cover;border:1px solid #ccc;border-radius:5px;">';
echo '</div>';

echo '<a href="RecipientSpecimenRequest/RecipientDonorRequestIndex.php?id='.intval($row['donor_id']).'"
style="display:inline-block;padding:5px 10px;border:1px solid #007bff;border-radius:5px;text-decoration:none;color:#007bff;">
View
</a>';

echo '</li>';
}

echo '</ul>';

} else {
echo '<p class="text-muted">No donors available.</p>';
}
?>

</div>
</div>
</div>

<?php include("../../includes/footer.php"); ?>