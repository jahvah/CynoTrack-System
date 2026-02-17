<?php 
session_start(); // MUST be at the very top
include('../includes/header.php'); 
include('../includes/alert.php'); 
?>

<h2>Login</h2>

<?php
// Show flash message from DonorStore.php (pending, inactive, registration)
if (isset($_SESSION['flash_message'])) {
    echo "<p style='color:blue'>".htmlspecialchars($_SESSION['flash_message'])."</p>";
    unset($_SESSION['flash_message']); // show only once
}

// Optional GET error messages for login failures
if(isset($_GET['error'])){
    switch($_GET['error']){
        case 'invalid_credentials':
            echo "<p style='color:red'>Invalid email or password</p>";
            break;
        case 'inactive':
            echo "<p style='color:red'>Account inactive. Contact admin.</p>";
            break;
        case 'pending':
            echo "<p style='color:red'>Account pending approval.</p>";
            break;
        case 'role_not_found':
            echo "<p style='color:red'>Role error. Contact admin.</p>";
            break;
    }
}
?>

<form method="POST" action="store.php">
    <input type="hidden" name="action" value="login">

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<?php include('../includes/footer.php'); ?>
