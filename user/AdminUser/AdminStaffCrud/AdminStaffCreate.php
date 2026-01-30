<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}
?>

<style>
    .container { padding: 30px; }
    form { max-width: 500px; margin: auto; }
    input { width: 100%; padding: 10px; margin: 10px 0; }
    button {
        padding: 10px 15px;
        background: green;
        color: white;
        border: none;
    }
</style>

<div class="container">
    <h2>Add New Staff</h2>

    <form action="AdminStaffStore.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="AdminStaffStore">

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

        <label>Profile Image</label>
        <input type="file" name="profile_image">

        <button type="submit">Create Staff</button>
    </form>
</div>

<?php include('../../../includes/footer.php'); ?>
