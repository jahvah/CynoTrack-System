<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

/* ==============================
   CREATE SELF-STORAGE USER
============================== */
if ($_POST['action'] === 'AdminSelfStorageStore') {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = $_POST['status'] ?? 'active';

    // Get role_id for self-storage
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='self-storage'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    // Handle profile image upload
    $image_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../../uploads/";
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);
    }

    // Insert into accounts table
    $stmt1 = $conn->prepare("INSERT INTO accounts (username, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssis", $username, $email, $password, $role_id, $status);

    if ($stmt1->execute()) {

        $account_id = $stmt1->insert_id;

        // Insert into self_storage_users table
        $stmt2 = $conn->prepare("INSERT INTO self_storage_users (account_id, first_name, last_name, profile_image) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $account_id, $first_name, $last_name, $image_name);

        if ($stmt2->execute()) {
            header("Location: AdminSelfStorageIndex.php?success=user_added");
            exit();
        } else {
            echo "Self-storage user insert error: " . $stmt2->error;
        }

    } else {
        echo "Account insert error: " . $stmt1->error;
    }
}

/* ==============================
   UPDATE SELF-STORAGE USER
============================== */
if ($_POST['action'] === 'AdminSelfStorageUpdate') {

    $storage_user_id = intval($_POST['storage_user_id']);
    $account_id      = intval($_POST['account_id']);

    $email      = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = trim($_POST['status']);

    /* Update email */
    if (!empty($email)) {
        $stmt = $conn->prepare("UPDATE accounts SET email=? WHERE account_id=?");
        $stmt->bind_param("si", $email, $account_id);
        if (!$stmt->execute()) {
            die("Email update error: " . $stmt->error);
        }
    }

    /* Update status */
    if (!empty($status) && in_array($status, ['active','inactive','pending'])) {
        $stmt = $conn->prepare("UPDATE accounts SET status=? WHERE account_id=?");
        $stmt->bind_param("si", $status, $account_id);
        if (!$stmt->execute()) {
            die("Status update error: " . $stmt->error);
        }
    }

    /* Update first name */
    if (!empty($first_name)) {
        $stmt = $conn->prepare("UPDATE self_storage_users SET first_name=? WHERE storage_user_id=?");
        $stmt->bind_param("si", $first_name, $storage_user_id);
        if (!$stmt->execute()) {
            die("First name update error: " . $stmt->error);
        }
    }

    /* Update last name */
    if (!empty($last_name)) {
        $stmt = $conn->prepare("UPDATE self_storage_users SET last_name=? WHERE storage_user_id=?");
        $stmt->bind_param("si", $last_name, $storage_user_id);
        if (!$stmt->execute()) {
            die("Last name update error: " . $stmt->error);
        }
    }

    /* Update profile image */
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../../uploads/";
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);

        $stmt = $conn->prepare("UPDATE self_storage_users SET profile_image=? WHERE storage_user_id=?");
        $stmt->bind_param("si", $image_name, $storage_user_id);
        if (!$stmt->execute()) {
            die("Image update error: " . $stmt->error);
        }
    }

    header("Location: AdminSelfStorageIndex.php?success=user_updated");
    exit();
}
?>
