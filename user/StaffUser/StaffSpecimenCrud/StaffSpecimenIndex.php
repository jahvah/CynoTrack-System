<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// fetch donor specimens linked with donor names
$donor_query = "SELECT 
            ds.specimen_id,
            ds.unique_code,
            ds.quantity,
            ds.status,
            ds.storage_location,
            ds.expiration_date,
            d.first_name,
            d.last_name
          FROM donor_specimens ds
          JOIN donors_users d ON ds.donor_id = d.donor_id
          ORDER BY ds.specimen_id DESC";
$donor_result = mysqli_query($conn, $donor_query);

// fetch storage specimens linked with self-storage user names
$storage_query = "SELECT 
            ss.specimen_id,
            ss.unique_code,
            ss.quantity,
            ss.status,
            ss.storage_location,
            ss.expiration_date,
            su.first_name,
            su.last_name
          FROM storage_specimens ss
          JOIN self_storage_users su ON ss.storage_user_id = su.storage_user_id
          ORDER BY ss.specimen_id DESC";
$storage_result = mysqli_query($conn, $storage_query);
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
    font-size: 13px;
}

th, td {
    padding: 8px;
    border: 1px solid #ccc;
    text-align: center;
}

th {
    background: #007bff;
    color: white;
}

.action-btn {
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    color: white;
    font-size: 12px;
}

.edit-btn { background: orange; }
.delete-btn { background: red; }

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 12px;
}

.green { background: green; }
.red { background: red; }
.yellow { background: orange; }

.back-btn {
    padding: 10px 18px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-right: 10px;
}

.back-btn:hover {
    background: #333;
}

.section-divider {
    margin-top: 40px;
    border-top: 2px solid #eee;
    padding-top: 20px;
}
</style>

<div class="container">

    <div class="top-bar">
        <h2>Specimen Management</h2>
        <div>
            <a href="../StaffDashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
    </div>

    <div class="top-bar" style="margin-top: 20px;">
        <h3>Donor Specimens</h3>
        <a href="StaffSpecimenCreateDonor.php" class="create-btn">+ Add Donor Specimen</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Unique Code</th>
            <th>Donor Name</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Location</th>
            <th>Expiration</th>
            <th>Actions</th>
        </tr>

        <?php if ($donor_result && mysqli_num_rows($donor_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($donor_result)): ?>
                <tr>
                    <td><?= $row['specimen_id']; ?></td>
                    <td><strong><?= htmlspecialchars($row['unique_code']); ?></strong></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= $row['quantity']; ?></td>
                    <td>
                        <?php
                        $status = $row['status'];
                        $class = 'yellow';
                        if ($status == 'approved' || $status == 'stored') $class = 'green';
                        if ($status == 'expired' || $status == 'disposed') $class = 'red';
                        echo "<span class='badge $class'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['storage_location'] ?? 'N/A'); ?></td>
                    <td><?= $row['expiration_date'] ? date("M d, Y", strtotime($row['expiration_date'])) : 'N/A'; ?></td>
                    <td>
                        <a href="StaffSpecimenUpdateDonor.php?type=donor&id=<?= $row['specimen_id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="StaffSpecimenDelete.php?type=donor&id=<?= $row['specimen_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No donor specimens found.</td></tr>
        <?php endif; ?>
    </table>

    <div class="section-divider"></div>

    <div class="top-bar">
        <h3>Self-Storage Specimens</h3>
        <a href="StaffSpecimenCreateStorage.php" class="create-btn">+ Add Storage Specimen</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Unique Code</th>
            <th>User Name</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Location</th>
            <th>Expiration</th>
            <th>Actions</th>
        </tr>

        <?php if ($storage_result && mysqli_num_rows($storage_result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($storage_result)): ?>
                <tr>
                    <td><?= $row['specimen_id']; ?></td>
                    <td><strong><?= htmlspecialchars($row['unique_code']); ?></strong></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= $row['quantity']; ?></td>
                    <td>
                        <?php
                        $status = $row['status'];
                        $class = ($status == 'stored') ? 'green' : (($status == 'used') ? 'yellow' : 'red');
                        echo "<span class='badge $class'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['storage_location'] ?? 'N/A'); ?></td>
                    <td><?= $row['expiration_date'] ? date("M d, Y", strtotime($row['expiration_date'])) : 'N/A'; ?></td>
                    <td>
                        <a href="StaffSpecimenUpdateStorage.php?type=storage&id=<?= $row['specimen_id']; ?>" class="action-btn edit-btn">Edit</a>
                        <a href="StaffSpecimenDelete.php?type=storage&id=<?= $row['specimen_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No storage specimens found.</td></tr>
        <?php endif; ?>
    </table>

</div>

<?php include('../../../includes/footer.php'); ?>