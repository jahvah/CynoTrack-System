<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");
include("../../includes/alert.php");

// Ensure recipient logged in
if (!isset($_SESSION['account_id'])) {
    die("Invalid session. Please login.");
}

$account_id = $_SESSION['account_id'];
?>

<style>
form {
    max-width: 600px;
    margin: 30px auto;
    padding: 25px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9f9;
    font-family: Arial, sans-serif;
}

form h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
    color: #555;
}

input[type="text"],
input[type="file"],
textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

textarea {
    resize: vertical;
    min-height: 80px;
}

img {
    margin-top: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

button[type="submit"] {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    background-color: #007BFF;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #0056b3;
}
</style>

<form method="POST" action="RecipientStore.php" enctype="multipart/form-data">
    <h2>Complete Your Recipient Profile</h2>
    <input type="hidden" name="action" value="update_profile">

    <label>Profile Image</label>
    <input type="file" name="profile_image" accept="image/*" required>
    <?php if (!empty($recipient['profile_image'])): ?>
        <img src="../../uploads/<?= htmlspecialchars($recipient['profile_image']); ?>" width="120">
    <?php endif; ?>

    <label>Preferences</label>
    <textarea name="preferences" required><?= htmlspecialchars($recipient['preferences'] ?? '') ?></textarea>

    <button type="submit">Save Profile</button>
</form>

<?php include("../../includes/footer.php"); ?>
