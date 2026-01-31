<?php
session_start();
include('../../../includes/config.php');

/* ==============================
   ADMIN PROTECTION
============================== */
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

/* ======================================================
   CREATE DONOR
====================================================== */
if ($_POST['action'] === 'AdminDonorStore') {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']); // ✅ FIXED
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = $_POST['status'] ?? 'active';

    // Get role_id for donor
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='donor'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    /* IMAGE UPLOAD */
    $image_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../../uploads/"; // ✅ FIXED PATH
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);
    }

    /* INSERT ACCOUNT */
    $stmt1 = $conn->prepare("INSERT INTO accounts (username, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssis", $username, $email, $password, $role_id, $status);

    if ($stmt1->execute()) {

        $account_id = $stmt1->insert_id;

        /* INSERT DONOR */
        $stmt2 = $conn->prepare("INSERT INTO donors_users (account_id, first_name, last_name, profile_image) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $account_id, $first_name, $last_name, $image_name);

        if ($stmt2->execute()) {
            header("Location: AdminDonorIndex.php?success=donor_added");
            exit();
        } else {
            die("Donor insert error: " . $stmt2->error);
        }

    } else {
        die("Account insert error: " . $stmt1->error);
    }
}

/* ======================================================
   UPDATE DONOR
====================================================== */
if ($_POST['action'] === 'AdminDonorUpdate') {

    $donor_id   = intval($_POST['donor_id']);
    $account_id = intval($_POST['account_id']);

    $email      = trim($_POST['email']); // ✅ Added
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = trim($_POST['status']);

    /* UPDATE EMAIL */
    if (!empty($email)) {
        $stmt = $conn->prepare("UPDATE accounts SET email=? WHERE account_id=?");
        $stmt->bind_param("si", $email, $account_id);
        if (!$stmt->execute()) {
            die("Email update error: " . $stmt->error);
        }
    }

    /* UPDATE ACCOUNT STATUS */
    if (!empty($status) && in_array($status, ['active','inactive','pending'])) {
        $stmt = $conn->prepare("UPDATE accounts SET status=? WHERE account_id=?");
        $stmt->bind_param("si", $status, $account_id);
        if (!$stmt->execute()) {
            die("Status update error: " . $stmt->error);
        }
    }

    /* UPDATE FIRST NAME */
    if (!empty($first_name)) {
        $stmt = $conn->prepare("UPDATE donors_users SET first_name=? WHERE donor_id=?");
        $stmt->bind_param("si", $first_name, $donor_id);
        if (!$stmt->execute()) {
            die("First name update error: " . $stmt->error);
        }
    }

    /* UPDATE LAST NAME */
    if (!empty($last_name)) {
        $stmt = $conn->prepare("UPDATE donors_users SET last_name=? WHERE donor_id=?");
        $stmt->bind_param("si", $last_name, $donor_id);
        if (!$stmt->execute()) {
            die("Last name update error: " . $stmt->error);
        }
    }

    /* UPDATE PROFILE IMAGE */
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../../uploads/"; // ✅ Correct path
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);

        $stmt = $conn->prepare("UPDATE donors_users SET profile_image=? WHERE donor_id=?");
        $stmt->bind_param("si", $image_name, $donor_id);
        if (!$stmt->execute()) {
            die("Image update error: " . $stmt->error);
        }
    }

    header("Location: AdminDonorIndex.php?success=donor_updated");
    exit();
}
?>
