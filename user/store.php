<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("../includes/config.php");

/* =======================================
   ACCOUNT REGISTRATION
======================================= */
if (isset($_POST['action']) && $_POST['action'] === 'register') {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id    = $_POST['role_id'];
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);

    // Insert account
    $stmt = $conn->prepare("INSERT INTO accounts (username, email, password_hash, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);
    if (!$stmt->execute()) {
        die("Error creating account: " . $stmt->error);
    }

    $account_id = $stmt->insert_id;
    $_SESSION['account_id'] = $account_id;

    // Get role name (lowercase)
    $stmt = $conn->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $role = strtolower($stmt->get_result()->fetch_assoc()['role_name']); // make lowercase
    $_SESSION['role'] = $role;

    // Insert into role table and redirect correctly
    if ($role === 'donor') {
        $stmt = $conn->prepare("INSERT INTO donors_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $account_id, $first_name, $last_name);
        $stmt->execute();
        $_SESSION['role_user_id'] = $conn->insert_id;
        header("Location: DonorUser/DonorProfile.php");
        exit;
    }

    elseif ($role === 'recipient') {
        $stmt = $conn->prepare("INSERT INTO recipients_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $account_id, $first_name, $last_name);
        $stmt->execute();
        $_SESSION['role_user_id'] = $conn->insert_id;
        header("Location: RecipientProfile.php");
        exit;
    }

    elseif ($role === 'self-storage user') {
        $stmt = $conn->prepare("INSERT INTO self_storage_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $account_id, $first_name, $last_name);
        $stmt->execute();
        $_SESSION['role_user_id'] = $conn->insert_id;
        header("Location: StorageProfile.php");
        exit;
    }
}

/* =======================================
   PROFILE COMPLETION
======================================= */
if (isset($_POST['action']) && $_POST['action'] === 'complete_profile') {

    $role = $_SESSION['role'] ?? '';
    $id   = $_SESSION['role_user_id'] ?? 0;

    if ($role === 'donor') {
        $stmt = $conn->prepare("
            UPDATE donors_users
            SET height_cm=?, weight_kg=?, eye_color=?, hair_color=?,
                blood_type=?, ethnicity=?, medical_history=?
            WHERE donor_id=?
        ");
        $stmt->bind_param(
            "iisssssi",
            $_POST['height_cm'], $_POST['weight_kg'],
            $_POST['eye_color'], $_POST['hair_color'],
            $_POST['blood_type'], $_POST['ethnicity'],
            $_POST['medical_history'], $id
        );
        $stmt->execute();
        header("Location: DonorDashboard.php");
        exit;
    }

    elseif ($role === 'recipient') {
        $stmt = $conn->prepare("UPDATE recipients_users SET preferences=? WHERE recipient_id=?");
        $stmt->bind_param("si", $_POST['preferences'], $id);
        $stmt->execute();
        header("Location: RecipientDashboard.php");
        exit;
    }

    elseif ($role === 'self-storage user') {
        $stmt = $conn->prepare("UPDATE self_storage_users SET storage_details=? WHERE storage_user_id=?");
        $stmt->bind_param("si", $_POST['storage_details'], $id);
        $stmt->execute();
        header("Location: StorageDashboard.php");
        exit;
    }
}
