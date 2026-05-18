<?php

function loginCtrl($conn) {
    $error = '';
    $prefill = $_COOKIE['remember_email'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']); 

        if ($email === '' || $password === '') {
            $error = 'Please fill in both fields.';
        } else {
            $user = getUserByEmail($conn, $email);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id'          => $user['id'],
                    'name'        => $user['name'],
                    'email'       => $user['email'],
                    'role'        => $user['role'],
                    'is_verified' => $user['is_verified']
                ];

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    setRememberToken($conn, $user['id'], $tokenHash);
                    setcookie('remember_token', $token, time() + 86400 * 30, '/'); 

                    setcookie('remember_email', $email, time() + 86400 * 30, '/');
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                header('Location: index.php?page=home');

                exit;
            }
            $error = 'Invalid email or password.';
        } 
    }

    require 'views/login.php';
}

function registerCtrl($conn) {
    $error = $success = '';
    $old = ['name' => '', 'email' => '', 'role' => 'user'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        $old = compact('name', 'email', 'role'); 

        if ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['user', 'scout'])) {
            $error = 'Invalid role selected.';
        } elseif (emailExists($conn, $email)) {
            $error = 'An account with this email already exists.';
        } else { 
            if (registerUser($conn, $name, $email, $password, $role)) {
                $success = 'Account created! Please wait for admin verification, then log in.';
                $old = ['name' => '', 'email' => '', 'role' => 'user'];
            } else {

                $error = 'Registration failed. Please try again.';
            }
        }
    }

    require 'views/register.php';
}

function homeCtrl($conn) {
    $posts = [];
    $isLoggedIn = isset($_SESSION['user']);
    $isVerified = $isLoggedIn && $_SESSION['user']['is_verified'] == 1;

    if ($isVerified) {
        $posts = getApprovedPosts($conn, 12);
        if ($_SESSION['user']['role'] === 'user') {
            foreach ($posts as &$post) {
                $post['in_wishlist'] = isInWishlist($conn, $_SESSION['user']['id'], $post['id']);
            }
            unset($post);
        }
    }

    require 'views/home.php';
}

function profileCtrl($conn) {
    $action  = $_GET['action'] ?? 'view';
    $error   = '';
    $success = '';
    $user    = getUserById($conn, $_SESSION['user']['id']);

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $error = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = 'Please enter a valid email address.';

        } elseif (emailExists($conn, $email, $_SESSION['user']['id'])) {
            $error = 'This email is already taken by another account.';
        } else {
            $picturePath = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['profile_picture']['tmp_name']);

                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    $error = 'Profile picture must be JPG, PNG, GIF or WebP.';
                } elseif ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
                    $error = 'Profile picture must be under 2MB.';
                } else {
                    $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $filename = 'profile_' . $_SESSION['user']['id'] . '_' . time() . '.' . $ext;
                    if (!is_dir('uploads')) {
                         mkdir('uploads', 0755, true);
                                                }
                        $dest = 'uploads/' . $filename;
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) { 
                        $picturePath = $dest;
                    } else {
                        $error = 'Failed to upload profile picture.';
                    }
                }

            }

            if ($error === '') { 
                if (updateProfile($conn, $_SESSION['user']['id'], $name, $email, $picturePath)) {
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    $success = 'Profile updated successfully.';
                    $user = getUserById($conn, $_SESSION['user']['id']); 
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    }

    if ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPwd = $_POST['current_password'] ?? '';
        $newPwd     = $_POST['new_password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';

        if ($currentPwd === '' || $newPwd === '' || $confirmPwd === '') {
            $error = 'All password fields are required.';
        } elseif (strlen($newPwd) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPwd !== $confirmPwd) {
            $error = 'New passwords do not match.';
        } else {

            $storedHash = getUserPassword($conn, $_SESSION['user']['id']);
            if (!password_verify($currentPwd, $storedHash)) {
                $error = 'Current password is incorrect.';
            } else {
                if (updatePassword($conn, $_SESSION['user']['id'], $newPwd)) {
                    $success = 'Password changed successfully.';
                } else {
                    $error = 'Failed to change password.';

                }
            }
        }
    }

    require 'views/profile.php';
}

function wishlistCtrl($conn) {
    $items = getWishlist($conn, $_SESSION['user']['id']);
    require 'views/wishlist.php';
} 
?>
