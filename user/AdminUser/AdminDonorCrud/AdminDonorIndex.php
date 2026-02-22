<?php
session_start();

include('../../../includes/config.php');
include('../../../includes/header.php');

// admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// fetch donor data including account + donor details
$query = "SELECT 
            d.donor_id, 
            d.account_id,
            d.first_name, 
            d.last_name, 
            d.profile_image,
            d.medical_document,
            d.medical_history,
            d.evaluation_status,
            d.height_cm,
            d.weight_kg,
            d.blood_type,
            d.ethnicity,
            d.eye_color,       -- added
            d.hair_color,      -- added
            a.username,
            a.email, 
            a.status
          FROM donors_users d
          JOIN accounts a ON d.account_id = a.account_id
          ORDER BY d.donor_id DESC";


$result = mysqli_query($conn, $query);
?>


<!-- style para sa table at layout -->
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
</style>

<div class="container">


    <div class="top-bar">
        <h2>Donor Management</h2>
        <a href="AdminDonorCreate.php" class="create-btn">+ Add Donor</a>
    </div>

    <table>
    <tr>
        <th>ID</th>
        <th>Profile</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Username</th>
        <th>Medical File</th>
        <th>Medical History</th>
        <th>Evaluation</th>
        <th>Height</th>
        <th>Weight</th>
        <th>Eye Color</th>
        <th>Hair Color</th>
        <th>Blood Type</th>
        <th>Ethnicity</th>
        <th>Account Status</th>
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
                <td><?= htmlspecialchars($row['username']); ?></td>
             <td>
                 <?php if (!empty($row['medical_document'])): ?>
                   <a href="../../../medical_docs/<?= htmlspecialchars($row['medical_document']); ?>" 
                    target="_blank" 
                    class="action-btn edit-btn"
                    style="background:#007bff;">
                    View PDF
                  </a>
                        <?php else: ?>
                     N/A
                       <?php endif; ?>
            </td>
                <td><?= htmlspecialchars($row['medical_history'] ?? 'N/A'); ?></td>

                <!-- Evaluation Badge -->
                <td>
                    <?php
                    $eval = $row['evaluation_status'];
                    if ($eval == 'approved') {
                        echo "<span class='badge green'>Approved</span>";
                    } elseif ($eval == 'rejected') {
                        echo "<span class='badge red'>Rejected</span>";
                    } else {
                        echo "<span class='badge yellow'>Pending</span>";
                    }
                    ?>
                </td>

                <td><?= $row['height_cm'] ? $row['height_cm'] . ' cm' : 'N/A'; ?></td>
                <td><?= $row['weight_kg'] ? $row['weight_kg'] . ' kg' : 'N/A'; ?></td>
                <td><?= htmlspecialchars($row['eye_color'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($row['hair_color'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($row['blood_type'] ?? 'N/A'); ?></td>
                <td><?= htmlspecialchars($row['ethnicity'] ?? 'N/A'); ?></td>

                <!-- Account Status Badge -->
                <td>
                    <?php
                    if ($row['status'] == 'active') {
                        echo "<span class='badge green'>Active</span>";
                    } elseif ($row['status'] == 'inactive') {
                        echo "<span class='badge red'>Inactive</span>";
                    } else {
                        echo "<span class='badge yellow'>Pending</span>";
                    }
                    ?>
                </td>

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
            <td colspan="15">No donor records found.</td>
        </tr>
    <?php endif; ?>
</table>


</div>

<?php include('../../../includes/footer.php'); ?>
