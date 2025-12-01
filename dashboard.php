<?php
include 'includes/auth.php';
checkAuth();

$role = getUserRole();
switch ($role) {
    case 'admin':
        header('Location: admin/index.php');
        break;
    case 'pemilik':
        header('Location: pemilik/index.php');
        break;
    case 'pencari':
        header('Location: pencari/index.php');
        break;
    default:
        header('Location:index.php');
}
exit();
