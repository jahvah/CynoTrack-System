<?php
include('./includes/config.php');
include('./includes/header.php');

?>

<?php
$password = "12345";
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo $hashed;
?>
<?php
include('./includes/footer.php');
?>
