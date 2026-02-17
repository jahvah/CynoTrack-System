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

//update profile
if ($action === 'update_profile') {

    if (!isset($_SESSION['account_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $account_id = $_SESSION['account_id'];

    // Get recipient data
    $stmt = $conn->prepare("SELECT * FROM recipients_users WHERE account_id=?");
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

    // Handle profile image (same validation as donor)
    if (!empty($_FILES['profile_image']['name'])) {

        $upload_dir = "../../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

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

        // First-time completion check
        $first_time = empty($recipient['profile_image']);

        if ($first_time) {

            $_SESSION['flash_message'] = "Your account is pending approval. Please login after admin approval.";

            unset($_SESSION['account_id']);
            unset($_SESSION['role']);

            header("Location: ../login.php");
            exit();

        } else {

            $_SESSION['flash_message'] = "Profile updated successfully!";
            header("Location: RecipientEditProfile.php");
            exit();
        }

    } else {
        header("Location: RecipientEditProfile.php?error=Update failed");
        exit();
     }
}
?>