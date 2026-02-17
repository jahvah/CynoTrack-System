<?php
session_start();

include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../unauthorized.php");
    exit();
}

// fetch recipient data including preferences
$query = "SELECT 
            r.recipient_id, 
            r.account_id,
            r.first_name, 
            r.last_name, 
            r.profile_image, 
            r.preferences,
            a.username,
            a.email, 
            a.status
          FROM recipients_users r
          JOIN accounts a ON r.account_id = a.account_id
          ORDER BY r.recipient_id DESC";



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
        <h2>Recipient Management</h2>
        <a href="AdminRecipientCreate.php" class="create-btn">+ Add Recipient</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Profile</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Preferences</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['recipient_id']; ?></td>

                    <td>
                        <?php if (!empty($row['profile_image'])): ?>
                            <img src="../../../uploads/<?= htmlspecialchars($row['profile_image']); ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>

                    <!-- DISPLAY PREFERENCES -->
                    <td>
                        <?= !empty($row['preferences']) 
                            ? htmlspecialchars($row['preferences']) 
                            : 'N/A'; ?>
                    </td>

                    <td><?= ucfirst(htmlspecialchars($row['status'])); ?></td>

                    <td>
                        <a href="AdminRecipientUpdate.php?id=<?= $row['recipient_id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="AdminRecipientDelete.php?id=<?= $row['recipient_id']; ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this recipient?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No recipient records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php include('../../../includes/footer.php'); ?>
