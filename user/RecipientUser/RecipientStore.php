<?php
session_start();
include("../../includes/config.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if no action
if (!isset($_POST['action'])) {
    header("Location: ../login.php");
    exit();
}

$action = $_POST['action'];

// =======================================================
// COMPLETE PROFILE / UPDATE PROFILE
// =======================================================
if ($action === 'update_profile') {

    if (!isset($_SESSION['account_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $account_id = $_SESSION['account_id'];

    // Fetch recipient
    $stmt = $conn->prepare("SELECT recipient_id FROM recipients_users WHERE account_id=?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();

    $fields = [];
    $types  = "";
    $values = [];

    function addField(&$fields, &$types, &$values, $name, $value, $type) {
        if ($value !== "" && $value !== null) {
            $fields[] = "$name=?";
            $types .= $type;
            $values[] = $value;
        }
    }

    addField($fields, $types, $values, "first_name", trim($_POST['first_name']), "s");
    addField($fields, $types, $values, "last_name", trim($_POST['last_name']), "s");
    addField($fields, $types, $values, "preferences", trim($_POST['preferences']), "s");

    // IMAGE UPLOAD
    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = "../../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            header("Location: RecipientEditProfile.php?error=Invalid image type");
            exit();
        }

        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            header("Location: RecipientEditProfile.php?error=Image too large");
            exit();
        }

        $file_name = uniqid("recipient_", true).".".$ext;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir.$file_name)) {
            addField($fields, $types, $values, "profile_image", $file_name, "s");
        }
    }

    if (count($fields) === 0) {
        header("Location: RecipientEditProfile.php?error=No changes detected");
        exit();
    }

    $sql = "UPDATE recipients_users SET ".implode(", ", $fields)." WHERE recipient_id=?";
    $types .= "i";
    $values[] = $recipient['recipient_id'];

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        header("Location: RecipientDashboard.php?profile_updated=1");
    } else {
        header("Location: RecipientEditProfile.php?error=Update failed");
    }
    exit();
}

// =======================================================
// REGISTER RECIPIENT
// =======================================================
if ($action === 'register') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO accounts (username, email, password_hash, role_id)
        VALUES (?, ?, ?, (SELECT role_id FROM roles WHERE role_name='Recipient'))
    ");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();

    $account_id = $stmt->insert_id;

    // Create empty recipient profile row
    $stmt2 = $conn->prepare("INSERT INTO recipients_users (account_id) VALUES (?)");
    $stmt2->bind_param("i", $account_id);
    $stmt2->execute();

    $_SESSION['account_id'] = $account_id;
    $_SESSION['role'] = 'recipient';
    $_SESSION['role_user_id'] = $conn->insert_id;

    // Redirect to complete profile
    header("Location: RecipientProfile.php");
    exit();
}
