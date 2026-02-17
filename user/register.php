<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");
include("../includes/alert.php");

// Fetch roles for dropdown
$roles = $conn->query("SELECT role_id, role_name FROM roles");
?>

<div class="container">
    <h2>Register Account</h2>

    <?php
    if(isset($_GET['error'])){
        if($_GET['error'] == 'invalid_email') echo "<p style='color:red'>Only Gmail addresses are allowed.</p>";
        if($_GET['error'] == 'username_exists') echo "<p style='color:red'>Username already taken.</p>";
        if($_GET['error'] == 'email_exists') echo "<p style='color:red'>Email already registered.</p>";
        if($_GET['error'] == 'system_error') echo "<p style='color:red'>System error. Try again.</p>";
        if($_GET['error'] == 'role_error') echo "<p style='color:red'>Role selection error.</p>";
    }
    ?>

    <form action="store.php" method="POST">
        <input type="hidden" name="action" value="register">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>First Name</label>
        <input type="text" name="first_name" required>

        <label>Last Name</label>
        <input type="text" name="last_name" required>

        <label>Role</label>
        <select name="role_id" required>
            <option value="">-- Select Role --</option>
            <?php while($role = $roles->fetch_assoc()): ?>
                <option value="<?= $role['role_id']; ?>">
                    <?= ucfirst($role['role_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Register</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
