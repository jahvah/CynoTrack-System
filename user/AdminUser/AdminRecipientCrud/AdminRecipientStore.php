<?php
session_start();
include('../../../includes/config.php');

//admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: AdminRecipientIndex.php");
    exit();
}

// create recipient

if ($_POST['action'] === 'AdminRecipientStore') {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = $_POST['status'] ?? 'active';
    $preferences = trim($_POST['preferences']);

    //PROFILE IMAGE
     $image_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../../uploads/";
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);
    } else {
        $image_name = "default.png"; // default placeholder if no image uploaded
    }

    // Check duplicates
    $stmtUser = $conn->prepare("SELECT account_id FROM accounts WHERE username=?");
    $stmtUser->bind_param("s", $username);
    $stmtUser->execute();
    $stmtUser->store_result();

    $stmtEmail = $conn->prepare("SELECT account_id FROM accounts WHERE email=?");
    $stmtEmail->bind_param("s", $email);
    $stmtEmail->execute();
    $stmtEmail->store_result();

    if ($stmtUser->num_rows > 0 && $stmtEmail->num_rows > 0) {
        $_SESSION['error'] = "Username and Email already exist";
        header("Location: AdminRecipientCreate.php");
        exit();
    } elseif ($stmtUser->num_rows > 0) {
        $_SESSION['error'] = "Username already exists";
        header("Location: AdminRecipientCreate.php");
        exit();
    } elseif ($stmtEmail->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: AdminRecipientCreate.php");
        exit();
    }

    // Get role_id for recipient
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='recipient'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    // Insert account
    $stmt1 = $conn->prepare("
        INSERT INTO accounts (username, email, password_hash, role_id, status)
        VALUES (?, ?, ?, ?, ?)
    ");
     $stmt1->bind_param("sssis", $username, $email, $password, $role_id, $status);
    $stmt1->execute();
    $account_id = $stmt1->insert_id;

    $stmt2 = $conn->prepare("
            INSERT INTO recipients_users (account_id, first_name, last_name, profile_image,preferences)
            VALUES (?, ?, ?, ?, ?)
    ");
    $stmt2->bind_param("issss", $account_id, $first_name, $last_name, $image_name, $preferences);
    $stmt2->execute();

    $_SESSION['success'] = "Recipient account created successfully";
    header("Location: AdminRecipientCreate.php");
    exit();

}

//update recipient
if ($_POST['action'] === 'AdminRecipientUpdate') {

    $recipient_id = intval($_POST['recipient_id']);
    $account_id   = intval($_POST['account_id']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = trim($_POST['status']);
    $preferences = trim($_POST['preferences']);

    $redirect = "AdminRecipientUpdate.php?id=" . $recipient_id;

    /* Update status */
    if (!empty($status) && in_array($status, ['active','inactive','pending'])) {
        $stmt = $conn->prepare("UPDATE accounts SET status=? WHERE account_id=?");
        $stmt->bind_param("si", $status, $account_id);
        $stmt->execute();
    }

    /* UPDATE FIRST NAME */
    if (!empty($first_name)) {
        $stmt = $conn->prepare("UPDATE recipients_users SET first_name=? WHERE recipient_id=?");
        $stmt->bind_param("si", $first_name, $recipient_id);
        $stmt->execute();
    }

   /* UPDATE LAST NAME */
    if (!empty($last_name)) {
        $stmt = $conn->prepare("UPDATE recipients_users SET last_name=? WHERE recipient_id=?");
        $stmt->bind_param("si", $last_name, $recipient_id);
        $stmt->execute();
    }

    /* preferences */
    if (!empty($preferences)) {
        $stmt = $conn->prepare("UPDATE recipients_users SET preferences=? WHERE recipient_id=?");
        $stmt->bind_param("si", $preferences, $recipient_id);
        $stmt->execute();
    }

    /* UPDATE PROFILE IMAGE */
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../../uploads/";
        $image_name = time() . "_" . basename($_FILES['profile_image']['name']);
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $image_name);

        $stmt = $conn->prepare("UPDATE recipients_users SET profile_image=? WHERE recipient_id=?");
        $stmt->bind_param("si", $image_name, $recipient_id);
        $stmt->execute();
    }
    

    $_SESSION['success'] = "Recipient updated successfully";
    header("Location: $redirect");    exit();
}
?>
