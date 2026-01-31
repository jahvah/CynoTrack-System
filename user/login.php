<?php
session_start();
include('../includes/config.php'); // mysqli connection
include('../includes/header.php');
include('../includes/alert.php');

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch account by email
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && password_verify($password, $user['password_hash'])){
        // Check account status
        $status = $user['status'];

        if($status === 'inactive'){
            // Account inactive
            echo "<script>alert('Your account is inactive. Please contact admin.'); window.location='login.php';</script>";
            exit();
        }
        elseif($status === 'pending'){
            // Account pending approval
            echo "<script>alert('Your account is pending approval. Please wait for admin activation.'); window.location='login.php';</script>";
            exit();
        }
        elseif($status === 'active'){
            // Account active, proceed to login
            $_SESSION['account_id'] = $user['account_id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['email'] = $user['email'];

            // Get role name
            $stmt_role = $conn->prepare("SELECT role_name FROM roles WHERE role_id = ?");
            $stmt_role->bind_param("i", $user['role_id']);
            $stmt_role->execute();
            $role = $stmt_role->get_result()->fetch_assoc()['role_name'];
            $_SESSION['role'] = strtolower($role);

            // Redirect based on role
            if($_SESSION['role'] === 'donor'){
                $stmt2 = $conn->prepare("SELECT donor_id FROM donors_users WHERE account_id = ?");
                $stmt2->bind_param("i", $user['account_id']);
                $stmt2->execute();
                $_SESSION['role_user_id'] = $stmt2->get_result()->fetch_assoc()['donor_id'];
                header("Location: DonorUser/DonorDashboard.php");
                exit;
            }
            elseif($_SESSION['role'] === 'recipient'){
                $stmt2 = $conn->prepare("SELECT recipient_id FROM recipients_users WHERE account_id = ?");
                $stmt2->bind_param("i", $user['account_id']);
                $stmt2->execute();
                $_SESSION['role_user_id'] = $stmt2->get_result()->fetch_assoc()['recipient_id'];
                header("Location: RecipientUser/RecipientDashboard.php");
                exit;
            }
            elseif($_SESSION['role'] === 'self-storage'){
                $stmt2 = $conn->prepare("SELECT storage_user_id FROM self_storage_users WHERE account_id = ?");
                $stmt2->bind_param("i", $user['account_id']);
                $stmt2->execute();
                $_SESSION['role_user_id'] = $stmt2->get_result()->fetch_assoc()['storage_user_id'];
                header("Location: SelfStorageUser/SelfStorageDashboard.php");
                exit;
            }
            elseif($_SESSION['role'] === 'admin'){
                $_SESSION['role_user_id'] = $user['account_id']; // for admin
                header("Location: AdminUser/AdminDashboard.php");
                exit;
            }
            else{
                header("Location: login.php?error=role_not_found");
                exit;
            }
        }
        else{
            // Unknown status
            echo "<script>alert('Your account status is invalid.'); window.location='login.php';</script>";
            exit();
        }
    } else {
        // Invalid email/password
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
}
?>

<h2>Login</h2>
<form method="POST">
    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit" name="login">Login</button>
</form>

<?php include('../includes/footer.php'); ?>
