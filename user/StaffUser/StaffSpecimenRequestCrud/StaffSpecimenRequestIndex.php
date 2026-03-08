<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

/* ================= RECIPIENT SPECIMEN REQUESTS ================= */
$request_query = "
SELECT sr.request_id, sr.request_date, sr.status, sr.fulfilled_date,
       sr.requested_quantity, sr.payment_status,
       ru.first_name, ru.last_name,
       s.unique_code
FROM specimen_requests sr
INNER JOIN recipients_users ru 
       ON sr.recipient_id = ru.recipient_id
INNER JOIN specimens s 
       ON sr.specimen_id = s.specimen_id
WHERE sr.request_type = 'recipient'
ORDER BY sr.request_id DESC
";
$request_result_recipient = mysqli_query($conn, $request_query);

/* ================= SELF STORAGE SPECIMEN REQUESTS ================= */
$storage_query = "
SELECT sr.request_id, sr.request_date, sr.status, sr.fulfilled_date,
       sr.requested_quantity, sr.payment_status,
       ssu.first_name, ssu.last_name,
       s.unique_code
FROM specimen_requests sr
INNER JOIN self_storage_users ssu 
       ON sr.storage_user_id = ssu.storage_user_id
INNER JOIN specimens s 
       ON sr.specimen_id = s.specimen_id
WHERE sr.request_type = 'storage'
ORDER BY sr.request_id DESC
";
$storage_result_storage = mysqli_query($conn, $storage_query);
?>

<style>
.container { padding:30px; }

.top-bar{
display:flex;
justify-content:space-between;
align-items:center;
}

table{
width:100%;
border-collapse:collapse;
margin-top:20px;
font-size:13px;
}

th,td{
padding:8px;
border:1px solid #ccc;
text-align:center;
}

th{
background:#007bff;
color:white;
}

.badge{
padding:4px 8px;
border-radius:4px;
color:white;
font-size:12px;
}

.green{background:green;}
.red{background:red;}
.yellow{background:orange;}
.blue{background:#17a2b8;}

.action-btn{
padding:6px 10px;
text-decoration:none;
border-radius:4px;
color:white;
font-size:12px;
}

.edit-btn{background:orange;}
.delete-btn{background:red;}

.message{padding:12px;margin-bottom:15px;border-radius:5px;}
.error{background:#f8d7da;color:#721c24;}
.success{background:#d4edda;color:#155724;}

.back-btn{
padding:10px 18px;
background:#555;
color:white;
text-decoration:none;
border-radius:5px;
}

.create-btn{
padding:10px 18px;
background:green;
color:white;
text-decoration:none;
border-radius:5px;
}
</style>

<div class="container">

<?php if (isset($_SESSION['error'])): ?>
<div class="message error"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="message success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- ================= RECIPIENT REQUEST TABLE ================= -->
<div class="top-bar">
<h2>Recipient Specimen Requests</h2>
<div>
<a href="../StaffDashboard.php" class="back-btn">← Back to Dashboard</a>
<a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientCreate.php" class="create-btn">+ Create Request</a>
</div>
</div>

<table>
<tr>
<th>ID</th>
<th>Recipient</th>
<th>Specimen Code</th>
<th>Requested Quantity</th>
<th>Request Date</th>
<th>Request Status</th>
<th>Payment Status</th>
<th>Actions</th>
</tr>

<?php if ($request_result_recipient && mysqli_num_rows($request_result_recipient) > 0): ?>
<?php while ($row = mysqli_fetch_assoc($request_result_recipient)): ?>
<tr>
<td><?= $row['request_id']; ?></td>
<td><?= $row['first_name']." ".$row['last_name']; ?></td>
<td><?= $row['unique_code']; ?></td>
<td><?= $row['requested_quantity']; ?></td>
<td><?= date("M d, Y h:i A", strtotime($row['request_date'])); ?></td>
<td>
<?php
$status = $row['status'];
$class = ($status == 'approved') ? 'green' :
        (($status == 'rejected') ? 'red' :
        (($status == 'fulfilled') ? 'blue' : 'yellow'));
echo "<span class='badge $class'>".ucfirst($status)."</span>";
?>
</td>
<td>
<?php
$payment = $row['payment_status'];
$pclass = ($payment == 'paid') ? 'green' :
        (($payment == 'refunded') ? 'blue' :
        (($payment == 'waiting_payment') ? 'yellow' : 'red'));
echo "<span class='badge $pclass'>".ucfirst(str_replace('_',' ',$payment))."</span>";
?>
</td>
<td>
<?php if ($status === 'pending'): ?>
<a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientUpdate.php?id=<?= $row['request_id']; ?>" class="action-btn edit-btn">Edit</a>
<a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientDelete.php?id=<?= $row['request_id']; ?>" 
class="action-btn delete-btn"
onclick="return confirm('Are you sure you want to delete this request?');">Delete</a>
<?php else: ?> - <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8">No recipient specimen requests found.</td></tr>
<?php endif; ?>
</table>

<!-- ================= SELF STORAGE REQUEST TABLE ================= -->
<div class="top-bar" style="margin-top:40px;">
<h2>Self Storage Specimen Requests</h2>
<div>
<a href="StaffSpecimenRequestSelfStorageCrud/StaffSpecimenRequestSelfStorageCreate.php" class="create-btn">+ Create Request</a>
</div>
</div>

<table>
<tr>
<th>ID</th>
<th>Storage User</th>
<th>Specimen Code</th>
<th>Requested Quantity</th>
<th>Request Date</th>
<th>Request Status</th>
<th>Payment Status</th>
<th>Actions</th>
</tr>

<?php if ($storage_result_storage && mysqli_num_rows($storage_result_storage) > 0): ?>
<?php while ($row = mysqli_fetch_assoc($storage_result_storage)): ?>
<tr>
<td><?= $row['request_id']; ?></td>
<td><?= $row['first_name']." ".$row['last_name']; ?></td>
<td><?= $row['unique_code']; ?></td>
<td><?= $row['requested_quantity']; ?></td>
<td><?= date("M d, Y h:i A", strtotime($row['request_date'])); ?></td>
<td>
<?php
$status = $row['status'];
$class = ($status == 'approved') ? 'green' :
        (($status == 'rejected') ? 'red' :
        (($status == 'fulfilled') ? 'blue' : 'yellow'));
echo "<span class='badge $class'>".ucfirst($status)."</span>";
?>
</td>
<td>
<?php
$payment = $row['payment_status'];
$pclass = ($payment == 'paid') ? 'green' :
        (($payment == 'refunded') ? 'blue' :
        (($payment == 'waiting_payment') ? 'yellow' : 'red'));
echo "<span class='badge $pclass'>".ucfirst(str_replace('_',' ',$payment))."</span>";
?>
</td>
<td>
<?php if ($status === 'pending'): ?>
<a href="StaffSpecimenRequestSelfStorageCrud/StaffSpecimenRequestSelfStorageUpdate.php?id=<?= $row['request_id']; ?>" class="action-btn edit-btn">Edit</a>
<a href="StaffSpecimenRequestSelfStorageCrud/StaffSpecimenRequestSelfStorageDelete.php?id=<?= $row['request_id']; ?>" 
class="action-btn delete-btn"
onclick="return confirm('Are you sure you want to delete this request?');">Delete</a>
<?php else: ?> - <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8">No self-storage specimen requests found.</td></tr>
<?php endif; ?>
</table>

</div>

<?php include('../../../includes/footer.php'); ?>