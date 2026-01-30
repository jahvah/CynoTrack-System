<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'AdminStaffStore') {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);

    // STAFF ROLE ID (make sure role "staff" exists)
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='staff'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    /* =======================
       IMAGE UPLOAD
    ======================= */
    $image_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../../uploads/";
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);
    }

    /* =======================
       INSERT INTO ACCOUNTS
    ======================= */
    $stmt1 = $conn->prepare("INSERT INTO accounts (username, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt1->bind_param("sssi", $username, $email, $password, $role_id);

    if ($stmt1->execute()) {

        $account_id = $stmt1->insert_id; // 🔥 AUTO GENERATED

        /* =======================
           INSERT INTO STAFF
        ======================= */
        $stmt2 = $conn->prepare("INSERT INTO staff (account_id, first_name, last_name, profile_image) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $account_id, $first_name, $last_name, $image_name);

        if ($stmt2->execute()) {
            header("Location: AdminStaffIndex.php?success=staff_added");
            exit();
        } else {
            echo "Staff insert error: " . $stmt2->error;
        }

    } else {
        echo "Account insert error: " . $stmt1->error;
    }
}
?>
