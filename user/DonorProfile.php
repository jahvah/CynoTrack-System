<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: register.php");
    exit;
}
?>

<div class="container">
    <h2>Complete Donor Profile</h2>
    <form action="store.php" method="POST">
        <input type="hidden" name="action" value="complete_profile">

        <label>Height (cm)</label>
        <input type="number" name="height_cm" required>

        <label>Weight (kg)</label>
        <input type="number" name="weight_kg" required>

        <label>Eye Color</label>
        <input type="text" name="eye_color">

        <label>Hair Color</label>
        <input type="text" name="hair_color">

        <label>Blood Type</label>
        <input type="text" name="blood_type">

        <label>Ethnicity</label>
        <input type="text" name="ethnicity">

        <label>Medical History</label>
        <textarea name="medical_history"></textarea>

        <button type="submit">Save Profile</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
