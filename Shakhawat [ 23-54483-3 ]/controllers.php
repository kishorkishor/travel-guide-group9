<?php
// ================================================================
// CONTROLLERS - request handling + role-based logic
// Task 3: Admin Dashboard - Users, Posts, Comments
// ================================================================

/* ============== Login ============== */
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
                if ($user['role'] !== 'admin') {
                    $error = 'Access restricted to admin accounts only.';
                } else {
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
                    }
                    header('Location: index.php?page=dashboard');
                    exit;
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
    require 'views/login.php';
}

/* ============== Admin Dashboard ============== */
function adminDashboardCtrl($conn) {
    $counts = getDashboardCounts($conn);
    require 'views/admin_dashboard.php';
}

/* ============== User Management ============== */
function usersCtrl($conn) {
    $action  = $_GET['action'] ?? 'list';
    $error   = '';

    /* --- Add User (POST) --- */
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        $verified = isset($_POST['is_verified']) ? 1 : 0;

        if ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (!in_array($role, ['admin', 'scout', 'user'])) {
            $error = 'Invalid role.';
        } elseif (emailExists($conn, $email)) {
            $error = 'This email is already registered.';
        } else {
            if (addUser($conn, $name, $email, $password, $role, $verified)) {
                header('Location: index.php?page=users&msg=added');
                exit;
            }
            $error = 'Failed to add user.';
        }
    }

    /* --- Delete User (GET) --- */
    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0 && $id !== $_SESSION['user']['id']) {
            deleteUser($conn, $id);
        }
        header('Location: index.php?page=users&msg=deleted');
        exit;
    }

    $users = getUsers($conn);
    require 'views/users.php';
}

/* ============== Post Moderation ============== */
function postModerationCtrl($conn) {
    $action  = $_GET['action'] ?? 'list';
    $error   = '';
    $editing = null;

    /* --- Edit Post (POST) --- */
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id        = intval($_GET['id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $history   = trim($_POST['short_history'] ?? '');
        $country   = trim($_POST['country'] ?? '');
        $genre     = trim($_POST['genre'] ?? '');
        $costLevel = trim($_POST['cost_level'] ?? '');
        $travelInfo = trim($_POST['travel_medium_info'] ?? '');

        if ($title === '' || $history === '' || $country === '' || $genre === '' || $costLevel === '' || $travelInfo === '') {
            $error = 'All fields are required.';
            $editing = ['id' => $id, 'title' => $title, 'short_history' => $history,
                        'country' => $country, 'genre' => $genre, 'cost_level' => $costLevel,
                        'travel_medium_info' => $travelInfo];
        } else {
            if (updatePost($conn, $id, $title, $history, $country, $genre, $costLevel, $travelInfo)) {
                header('Location: index.php?page=post_moderation&msg=updated');
                exit;
            }
            $error = 'Failed to update post.';
        }
    }

    /* --- Show Edit Form --- */
    if ($action === 'edit' && !$editing) {
        $id = intval($_GET['id'] ?? 0);
        $editing = getPost($conn, $id);
    }

    /* --- Delete Post --- */
    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deletePost($conn, $id);
        header('Location: index.php?page=post_moderation&msg=deleted');
        exit;
    }

    $pendingRequests = getPendingRequests($conn);
    $posts = getAllPosts($conn);
    require 'views/post_moderation.php';
}

/* ============== Comment Moderation ============== */
function commentModerationCtrl($conn) {
    $comments = getAllComments($conn);
    require 'views/comment_moderation.php';
}
?>
