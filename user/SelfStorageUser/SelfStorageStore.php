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


/* =========================================
   COMPLETE / UPDATE SELF STORAGE PROFILE
========================================= */
if ($action === 'update_profile') {

    if (!isset($_SESSION['account_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $account_id = $_SESSION['account_id'];

    $stmt = $conn->prepare("SELECT storage_user_id FROM self_storage_users WHERE account_id=?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        header("Location: SelfStorageProfile.php?error=Profile not found");
        exit();
    }

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

    addField($fields,$types,$values,"first_name", trim($_POST['first_name']), "s");
    addField($fields,$types,$values,"last_name", trim($_POST['last_name']), "s");
    addField($fields,$types,$values,"storage_details", trim($_POST['storage_details']), "s");

    // IMAGE
    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = "../../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array($ext, $allowed)) {
            header("Location: SelfStorageProfile.php?error=Invalid image type");
            exit();
        }

        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            header("Location: SelfStorageProfile.php?error=Image too large");
            exit();
        }

        $file_name = uniqid("storage_", true).".".$ext;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir.$file_name)) {
            addField($fields,$types,$values,"profile_image",$file_name,"s");
        }
    }

    if (count($fields) === 0) {
        header("Location: SelfStorageProfile.php?error=No changes detected");
        exit();
    }

    $sql = "UPDATE self_storage_users SET ".implode(", ", $fields)." WHERE storage_user_id=?";
    $types .= "i";
    $values[] = $user['storage_user_id'];

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        header("Location: SelfStorageDashboard.php?profile_completed=1");
    } else {
        header("Location: SelfStorageProfile.php?error=Update failed");
    }
    exit();
}



/* =========================================
   REGISTER SELF STORAGE USER
========================================= */
if ($action === 'register') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO accounts (username, email, password_hash, role_id)
        VALUES (?, ?, ?, (SELECT role_id FROM roles WHERE role_name='Self-Storage User'))
    ");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();

    $account_id = $stmt->insert_id;

    // Create empty profile row
    $stmt2 = $conn->prepare("INSERT INTO self_storage_users (account_id) VALUES (?)");
    $stmt2->bind_param("i", $account_id);
    $stmt2->execute();

    $_SESSION['account_id'] = $account_id;
    $_SESSION['role'] = 'self-storage';
    $_SESSION['role_user_id'] = $stmt2->insert_id;

    // 👉 SEND TO COMPLETE PROFILE PAGE FIRST
    header("Location: SelfStorageDashboard.php");
    exit();
}
?>
