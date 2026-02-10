<?php
session_start();

include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

//fetch staff data including account status
$query = "SELECT s.staff_id, s.first_name, s.last_name, s.profile_image, a.email, a.status
          FROM staff s
          JOIN accounts a ON s.account_id = a.account_id
          ORDER BY s.staff_id DESC";

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
        <h2>Staff Management</h2>
        <!-- CREATE BUTTON -->
        <a href="AdminStaffCreate.php" class="create-btn">+ Add Staff</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Profile</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Status</th> <!-- NEW STATUS COLUMN -->
            <th>Actions</th>
        </tr>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['staff_id']; ?></td>

                    <td>
                        <?php if (!empty($row['profile_image'])): ?>
                            <img src="../../uploads/<?= htmlspecialchars($row['profile_image']); ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['status'])); ?></td> <!-- SHOW STATUS -->

                    <!-- ACTION BUTTONS -->
                    <td>
                        <a href="AdminStaffUpdate.php?id=<?= $row['staff_id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="AdminStaffDelete.php?id=<?= $row['staff_id']; ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this staff?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No staff records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php include('../../../includes/footer.php'); ?>