<?php
session_start();
include('../../../includes/config.php');

//admin protection
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

//create donor with duplicate checks for email and username
if ($_POST['action'] === 'AdminDonorStore') {

    // REQUIRED FIELDS
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name       = trim($_POST['first_name']);
    $last_name        = trim($_POST['last_name']);
    $status           = $_POST['status'] ?? 'active';
    $medical_history  = trim($_POST['medical_history']);
    $evaluation_status= $_POST['evaluation_status'] ?? 'pending'; // can be 'pending', 'approved', 'rejected'
    $active_status    = isset($_POST['active_status']) ? (int)$_POST['active_status'] : 1;
    $height_cm        = (int)$_POST['height_cm'];
    $weight_kg        = (int)$_POST['weight_kg'];
    $eye_color        = trim($_POST['eye_color']);
    $hair_color       = trim($_POST['hair_color']);
    $blood_type       = trim($_POST['blood_type']);
    $ethnicity        = trim($_POST['ethnicity']);

    // PROFILE IMAGE
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
    header("Location: AdminDonorCreate.php");
    exit();
} elseif ($stmtUser->num_rows > 0) {
    $_SESSION['error'] = "Username already exists";
    header("Location: AdminDonorCreate.php");
    exit();
} elseif ($stmtEmail->num_rows > 0) {
    $_SESSION['error'] = "Email already exists";
    header("Location: AdminDonorCreate.php");
    exit();
}


    // GET role_id for donor
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='donor'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    // INSERT ACCOUNT
    $stmt1 = $conn->prepare("
        INSERT INTO accounts (username, email, password_hash, role_id, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt1->bind_param("sssis", $username, $email, $password, $role_id, $status);
    $stmt1->execute();
    $account_id = $stmt1->insert_id;

    // INSERT DONOR
    $stmt2 = $conn->prepare("
        INSERT INTO donors_users 
        (account_id, first_name, last_name, profile_image, medical_history, evaluation_status, active_status, height_cm, weight_kg, eye_color, hair_color, blood_type, ethnicity)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt2->bind_param(
        "isssssiisssss",
        $account_id,
        $first_name,
        $last_name,
        $image_name,
        $medical_history,
        $evaluation_status,
        $active_status,
        $height_cm,
        $weight_kg,
        $eye_color,
        $hair_color,
        $blood_type,
        $ethnicity
    );
    $stmt2->execute();

    $_SESSION['success'] = "Donor account created successfully";
    header("Location: AdminDonorCreate.php");
    exit();
}

//update donor with duplicate checks for email and username
if ($_POST['action'] === 'AdminDonorUpdate') {

    $donor_id   = intval($_POST['donor_id']);
    $account_id = intval($_POST['account_id']);

    $first_name        = trim($_POST['first_name']);
    $last_name         = trim($_POST['last_name']);
    $medical_history   = trim($_POST['medical_history']);
    $evaluation_status = !empty($_POST['evaluation_status_select']) 
                            ? $_POST['evaluation_status_select'] 
                            : trim($_POST['evaluation_status']);
    $active_status     = isset($_POST['active_status_select']) 
                            ? intval($_POST['active_status_select']) 
                            : trim($_POST['active_status']);
    $height_cm   = trim($_POST['height_cm']);
    $weight_kg   = trim($_POST['weight_kg']);
    $eye_color   = trim($_POST['eye_color']);
    $hair_color  = trim($_POST['hair_color']);
    $blood_type  = trim($_POST['blood_type']);
    $ethnicity   = trim($_POST['ethnicity']);
    $status      = trim($_POST['status']);

    $redirect = "AdminDonorUpdate.php?id=" . $donor_id;

    //update account status 
    if (!empty($status)) {
        $stmt = $conn->prepare("UPDATE accounts SET status=? WHERE account_id=?");
        $stmt->bind_param("si", $status, $account_id);
        $stmt->execute();
    }


    //update first name
    if (!empty($first_name)) {
        $stmt = $conn->prepare("UPDATE donors_users SET first_name=? WHERE donor_id=?");
        $stmt->bind_param("si", $first_name, $donor_id);
        $stmt->execute();
    }

    //update last name
    if (!empty($last_name)) {
        $stmt = $conn->prepare("UPDATE donors_users SET last_name=? WHERE donor_id=?");
        $stmt->bind_param("si", $last_name, $donor_id);
        $stmt->execute();
    }

    //upadteq medical history
    if (!empty($medical_history)) {
        $stmt = $conn->prepare("UPDATE donors_users SET medical_history=? WHERE donor_id=?");
        $stmt->bind_param("si", $medical_history, $donor_id);
        $stmt->execute();
    }

    //update evaluation status
    if (!empty($evaluation_status)) {
        $stmt = $conn->prepare("UPDATE donors_users SET evaluation_status=? WHERE donor_id=?");
        $stmt->bind_param("si", $evaluation_status, $donor_id);
        $stmt->execute();
    }

    //update active status
    if ($active_status !== "") {
        $stmt = $conn->prepare("UPDATE donors_users SET active_status=? WHERE donor_id=?");
        $stmt->bind_param("ii", $active_status, $donor_id);
        $stmt->execute();
    }

    //update height
    if (!empty($height_cm)) {
        $stmt = $conn->prepare("UPDATE donors_users SET height_cm=? WHERE donor_id=?");
        $stmt->bind_param("ii", $height_cm, $donor_id);
        $stmt->execute();
    }

    //update weight
    if (!empty($weight_kg)) {
        $stmt = $conn->prepare("UPDATE donors_users SET weight_kg=? WHERE donor_id=?");
        $stmt->bind_param("ii", $weight_kg, $donor_id);
        $stmt->execute();
    }

    //update eye color
    if (!empty($eye_color)) {
        $stmt = $conn->prepare("UPDATE donors_users SET eye_color=? WHERE donor_id=?");
        $stmt->bind_param("si", $eye_color, $donor_id);
        $stmt->execute();
    }

    //update hair color
    if (!empty($hair_color)) {
        $stmt = $conn->prepare("UPDATE donors_users SET hair_color=? WHERE donor_id=?");
        $stmt->bind_param("si", $hair_color, $donor_id);
        $stmt->execute();
    }

    //update blood type
    if (!empty($blood_type)) {
        $stmt = $conn->prepare("UPDATE donors_users SET blood_type=? WHERE donor_id=?");
        $stmt->bind_param("si", $blood_type, $donor_id);
        $stmt->execute();
    }

    //update ethnicity
    if (!empty($ethnicity)) {
        $stmt = $conn->prepare("UPDATE donors_users SET ethnicity=? WHERE donor_id=?");
        $stmt->bind_param("si", $ethnicity, $donor_id);
        $stmt->execute();
    }

    /* ================= PROFILE IMAGE ================= */
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../../uploads/";
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);

        $stmt = $conn->prepare("UPDATE donors_users SET profile_image=? WHERE donor_id=?");
        $stmt->bind_param("si", $image_name, $donor_id);
        $stmt->execute();
    }

    $_SESSION['success'] = "Donor updated successfully!";
    header("Location: $redirect");
    exit();
}


