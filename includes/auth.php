<?php
session_start();

function checkAuth()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

function getUserRole()
{
    return $_SESSION['user_role'] ?? null;
}

function redirectBasedOnRole()
{
    $role = getUserRole();
    if ($role) {
        if ($role === 'pemilik') {
            header("Location: dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }
}

function getNamaUser()
{
    return $_SESSION['user_nama'] ?? 'User';
}

function getFotoProfil()
{
    return $_SESSION['user_foto'] ?? 'default-avatar.jpg';
}
