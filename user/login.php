<?php
include('../includes/config.php');
include('../includes/header.php');
include('../includes/alert.php');

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password_hash'])){
        $_SESSION['user_id'] = $user['account_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['success'] = "Login successful!";
        header("Location: store.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password.";
    }
}
?>

<h2>Login</h2>
<form method="POST">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit" name="login">Login</button>
</form>

<?php include('../includes/footer.php'); ?>
