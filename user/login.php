<?php include('../includes/header.php'); ?>
<?php include('../includes/alert.php'); ?>

<h2>Login</h2>

<?php
// Optional error messages
if(isset($_GET['error'])){
    if($_GET['error'] == 'invalid_credentials') echo "<p style='color:red'>Invalid email or password</p>";
    if($_GET['error'] == 'inactive') echo "<p style='color:red'>Account inactive. Contact admin.</p>";
    if($_GET['error'] == 'pending') echo "<p style='color:red'>Account pending approval.</p>";
    if($_GET['error'] == 'role_not_found') echo "<p style='color:red'>Role error. Contact admin.</p>";
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
