<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: AdminStaffIndex.php");
    exit();
}

/* =====================================================
   🟢 CREATE STAFF
===================================================== */
if ($_POST['action'] === 'AdminStaffStore') {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = $_POST['status'] ?? 'active';

    // Check duplicates
    $stmtUser = $conn->prepare("SELECT account_id FROM accounts WHERE username=?");
    $stmtUser->bind_param("s", $username);
    $stmtUser->execute();
    $stmtUser->store_result();
    $userExists = $stmtUser->num_rows > 0;

    $stmtEmail = $conn->prepare("SELECT account_id FROM accounts WHERE email=?");
    $stmtEmail->bind_param("s", $email);
    $stmtEmail->execute();
    $stmtEmail->store_result();
    $emailExists = $stmtEmail->num_rows > 0;

    if ($userExists && $emailExists) {
        $_SESSION['error'] = "Username and Email already exist";
        header("Location: AdminStaffCreate.php");
        exit();
    } elseif ($userExists) {
        $_SESSION['error'] = "Username already exists";
        header("Location: AdminStaffCreate.php");
        exit();
    } elseif ($emailExists) {
        $_SESSION['error'] = "Email already exists";
        header("Location: AdminStaffCreate.php");
        exit();
    }

    // Get role_id for staff
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='staff'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    // Handle profile image
    $image_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../uploads/";
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);
    }

    // Insert account
    $stmt1 = $conn->prepare("INSERT INTO accounts (username, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssis", $username, $email, $password, $role_id, $status);

    if ($stmt1->execute()) {
        $account_id = $stmt1->insert_id;

        // Insert staff
        $stmt2 = $conn->prepare("INSERT INTO staff (account_id, first_name, last_name, profile_image) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $account_id, $first_name, $last_name, $image_name);

        if ($stmt2->execute()) {
            $_SESSION['success'] = "Staff account created successfully";
            header("Location: AdminStaffCreate.php");
            exit();
        } else {
            $_SESSION['error'] = "Staff insert error";
            header("Location: AdminStaffCreate.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Account insert error";
        header("Location: AdminStaffCreate.php");
        exit();
    }
}

/* =====================================================
   🟡 UPDATE STAFF
===================================================== */
if ($_POST['action'] === 'AdminStaffUpdate') {

    $staff_id   = intval($_POST['staff_id']);
    $account_id = intval($_POST['account_id']);

    $email      = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = trim($_POST['status']);

    // Store current page for redirect
    $redirect = "AdminStaffUpdate.php?id=" . $staff_id;

    /* 🔎 CHECK EMAIL DUPLICATE (EXCLUDE CURRENT USER) */
    if (!empty($email)) {
        $stmtEmail = $conn->prepare("SELECT account_id FROM accounts WHERE email=? AND account_id!=?");
        $stmtEmail->bind_param("si", $email, $account_id);
        $stmtEmail->execute();
        $stmtEmail->store_result();

        if ($stmtEmail->num_rows > 0) {
            $_SESSION['error'] = "Email already exists";
            header("Location: $redirect");
            exit();
        }

        // Update email
        $stmt = $conn->prepare("UPDATE accounts SET email=? WHERE account_id=?");
        $stmt->bind_param("si", $email, $account_id);
        $stmt->execute();
    }

    /* UPDATE STATUS */
    if (!empty($status) && in_array($status, ['active','inactive','pending'])) {
        $stmt = $conn->prepare("UPDATE accounts SET status=? WHERE account_id=?");
        $stmt->bind_param("si", $status, $account_id);
        $stmt->execute();
    }

    /* UPDATE FIRST NAME */
    if (!empty($first_name)) {
        $stmt = $conn->prepare("UPDATE staff SET first_name=? WHERE staff_id=?");
        $stmt->bind_param("si", $first_name, $staff_id);
        $stmt->execute();
    }

    /* UPDATE LAST NAME */
    if (!empty($last_name)) {
        $stmt = $conn->prepare("UPDATE staff SET last_name=? WHERE staff_id=?");
        $stmt->bind_param("si", $last_name, $staff_id);
        $stmt->execute();
    }

    /* UPDATE IMAGE */
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../uploads/";
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);

        $stmt = $conn->prepare("UPDATE staff SET profile_image=? WHERE staff_id=?");
        $stmt->bind_param("si", $image_name, $staff_id);
        $stmt->execute();
    }

    $_SESSION['success'] = "Staff updated successfully";
    header("Location: $redirect");
    exit();
}

