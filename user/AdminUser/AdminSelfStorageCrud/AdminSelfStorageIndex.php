<?php
session_start();

include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

//fetch self-storage user data including account status
$query = "SELECT 
            s.storage_user_id, 
            s.first_name, 
            s.last_name, 
            s.profile_image, 
            s.storage_details,
            a.username,
            a.email, 
            a.status
          FROM self_storage_users s
          JOIN accounts a ON s.account_id = a.account_id
          ORDER BY s.storage_user_id DESC";

$result = mysqli_query($conn, $query);
?>

<style>
.container { padding: 30px; }

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.create-btn {
    padding: 10px 18px;
    background: green;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: center;
}

th {
    background: #007bff;
    color: white;
}

img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 50%;
}

.action-btn {
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    color: white;
    font-size: 13px;
}

.edit-btn { background: orange; }
.delete-btn { background: red; }
</style>

<div class="container">

    <div class="top-bar">
        <h2>Self-Storage Users Management</h2>
        <!-- CREATE BUTTON -->
        <a href="AdminSelfStorageCreate.php" class="create-btn">+ Add Self-Storage User</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Profile</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Status</th>
            <th>Storage Details</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['storage_user_id']; ?></td>

                    <td>
                        <?php if (!empty($row['profile_image'])): ?>
                            <img src="../../../uploads/<?= htmlspecialchars($row['profile_image']); ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['status'])); ?></td>
                    <td><?= nl2br(htmlspecialchars($row['storage_details'])); ?></td>

                    <!-- ACTION BUTTONS -->
                    <td>
                        <a href="AdminSelfStorageUpdate.php?id=<?= $row['storage_user_id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="AdminSelfStorageDelete.php?id=<?= $row['storage_user_id']; ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this self-storage user?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No self-storage user records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php include('../../../includes/footer.php'); ?>
