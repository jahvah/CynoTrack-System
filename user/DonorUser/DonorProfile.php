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
?>

<style>
/* Container styling */
form {
    max-width: 600px;
    margin: 30px auto;
    padding: 25px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9f9;
    font-family: Arial, sans-serif;
}

/* Form elements */
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
input[type="number"],
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

/* Submit button */
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

<form method="POST" action="DonorStore.php" enctype="multipart/form-data">
    <h2>Complete Your Donor Profile</h2>
    <input type="hidden" name="action" value="update_profile">

    <label>Profile Image</label>
    <input type="file" name="profile_image" accept="image/*" required>
    <?php if (!empty($donor['profile_image'])): ?>
        <img src="../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>" width="120">
    <?php endif; ?>

    <label>Height (cm)</label>
    <input type="number" name="height_cm" value="<?= htmlspecialchars($donor['height_cm'] ?? '') ?>" required>

    <label>Weight (kg)</label>
    <input type="number" name="weight_kg" value="<?= htmlspecialchars($donor['weight_kg'] ?? '') ?>" required>

    <label>Eye Color</label>
    <input type="text" name="eye_color" value="<?= htmlspecialchars($donor['eye_color'] ?? '') ?>" required>

    <label>Hair Color</label>
    <input type="text" name="hair_color" value="<?= htmlspecialchars($donor['hair_color'] ?? '') ?>" required>

    <label>Blood Type</label>
    <input type="text" name="blood_type" value="<?= htmlspecialchars($donor['blood_type'] ?? '') ?>" required>

    <label>Ethnicity</label>
    <input type="text" name="ethnicity" value="<?= htmlspecialchars($donor['ethnicity'] ?? '') ?>" required>

    <label>Upload Medical Document (PDF)</label>
    <input type="file" name="medical_document" accept="application/pdf" <?= empty($donor['medical_document']) ? 'required' : '' ?>>

    <?php if (!empty($donor['medical_document'])): ?>
        <p>Current File:
            <a href="../../medical_docs/<?= htmlspecialchars($donor['medical_document']); ?>" target="_blank" >
             View Uploaded Medical Document
         </a>
        </p>
    <?php endif; ?>
</form>

<?php include("../../includes/footer.php"); ?>
