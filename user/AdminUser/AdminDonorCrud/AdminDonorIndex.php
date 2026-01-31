<?php
session_start();

include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

//fetch donor data including account status
$query = "SELECT d.donor_id, d.first_name, d.last_name, d.profile_image, a.email, a.status
          FROM donors_users d
          JOIN accounts a ON d.account_id = a.account_id
          ORDER BY d.donor_id DESC";

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
        <h2>Donor Management</h2>
        <!-- CREATE BUTTON -->
        <a href="AdminDonorCreate.php" class="create-btn">+ Add Donor</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Profile</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Status</th> <!-- ACCOUNT STATUS -->
            <th>Actions</th>
        </tr>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['donor_id']; ?></td>

                    <td>
                        <?php if (!empty($row['profile_image'])): ?>
                            <img src="../../../uploads/<?= htmlspecialchars($row['profile_image']); ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['status'])); ?></td> <!-- SHOW ACCOUNT STATUS -->

                    <!-- ACTION BUTTONS -->
                    <td>
                        <a href="AdminDonorUpdate.php?id=<?= $row['donor_id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="AdminDonorDelete.php?id=<?= $row['donor_id']; ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this donor?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No donor records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php include('../../../includes/footer.php'); ?>
