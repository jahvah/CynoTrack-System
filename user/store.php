<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("../includes/config.php");

/* =======================================
   ACCOUNT REGISTRATION FUNCTION
======================================= */

function registerUser($conn, $username, $email, $password, $role_id, $first_name, $last_name) {
    // 1️⃣ Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 2️⃣ Insert into accounts table
    $stmt = $conn->prepare("INSERT INTO accounts (username, email, password_hash, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);
    if (!$stmt->execute()) {
        die("Error creating account: " . $stmt->error);
    }
    $account_id = $stmt->insert_id;
    $_SESSION['account_id'] = $account_id;

    // 3️⃣ Get role name
    $stmt = $conn->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $role = strtolower($stmt->get_result()->fetch_assoc()['role_name']); // lowercase for uniformity
    $_SESSION['role'] = $role;

    // 4️⃣ Insert into the role-specific table and redirect
    switch ($role) {
        case 'donor':
            $stmt = $conn->prepare("INSERT INTO donors_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $account_id, $first_name, $last_name);
            $stmt->execute();
            $_SESSION['role_user_id'] = $conn->insert_id;
            header("Location: DonorUser/DonorProfile.php"); // go complete profile
            exit;

        case 'recipient':
            $stmt = $conn->prepare("INSERT INTO recipients_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $account_id, $first_name, $last_name);
            $stmt->execute();
            $_SESSION['role_user_id'] = $conn->insert_id;
            header("Location: RecipientUser/RecipientProfile.php"); // complete profile
            exit;

        case 'self-storage':
            $stmt = $conn->prepare("INSERT INTO self_storage_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $account_id, $first_name, $last_name);
            $stmt->execute();
            $_SESSION['role_user_id'] = $conn->insert_id;
            header("Location: SelfStorageUser/SelfStorageProfile.php"); // complete profile
            exit;

        default:
            die("Unknown role selected.");
    }
}


/* =======================================
   HANDLE POST REQUEST
======================================= */
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    registerUser(
        $conn,
        trim($_POST['username']),
        trim($_POST['email']),
        $_POST['password'],
        $_POST['role_id'],
        trim($_POST['first_name']),
        trim($_POST['last_name'])
    );
}
