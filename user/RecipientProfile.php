<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: register.php");
    exit;
}
?>

<div class="container">
    <h2>Complete Recipient Profile</h2>
    <form action="store.php" method="POST">
        <input type="hidden" name="action" value="complete_profile">

        <label>Preferences</label>
        <textarea name="preferences"></textarea>

        <button type="submit">Save Profile</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
