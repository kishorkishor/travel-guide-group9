<?php
// ================================================================
// FRONT CONTROLLER (router)
// Task 3: Admin Dashboard - Users, Posts, Comments
// ================================================================
session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

/* ------------- Remember Me Auto-Login ------------- */
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $tokenHash = hash('sha256', $_COOKIE['remember_token']);
    $remembered = getUserByRememberToken($conn, $tokenHash);
    if ($remembered && $remembered['role'] === 'admin') {
        $_SESSION['user'] = [
            'id'          => $remembered['id'],
            'name'        => $remembered['name'],
            'email'       => $remembered['email'],
            'role'        => $remembered['role'],
            'is_verified' => $remembered['is_verified']
        ];
    }
}

/* ------------- Logout ------------- */
if ($page === 'logout') {
    if (isset($_SESSION['user'])) {
        clearRememberToken($conn, $_SESSION['user']['id']);
    }
    $_SESSION = [];
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('remember_email', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}

/* ------------- AJAX Endpoints ------------- */
if ($page === 'ajax') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $type = $_GET['type'] ?? '';

    // Toggle user verification
    if ($type === 'verify_toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $isVerified = intval($_POST['is_verified'] ?? 0);
        $ok = toggleUserVerification($conn, $id, $isVerified);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // Approve post request
    if ($type === 'approve_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $ok = approvePostRequest($conn, $id);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // Reject post request
    if ($type === 'reject_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $ok = rejectPostRequest($conn, $id);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // Delete comment
    if ($type === 'delete_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $ok = deleteComment($conn, $id);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // Delete user
    if ($type === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id === $_SESSION['user']['id']) {
            echo json_encode(['error' => 'Cannot delete your own admin account.']);
            exit;
        }
        $ok = deleteUser($conn, $id);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // Search users
    if ($type === 'search_users') {
        $q = trim($_GET['q'] ?? '');
        echo json_encode($q === '' ? getUsers($conn) : searchUsers($conn, $q));
        exit;
    }

    // Search comments
    if ($type === 'search_comments') {
        $q = trim($_GET['q'] ?? '');
        echo json_encode($q === '' ? getAllComments($conn) : searchComments($conn, $q));
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

/* ------------- Auth Gates ------------- */
$publicPages = ['login'];

if ($page === 'login' && isset($_SESSION['user'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

// Admin gate
if (!in_array($page, $publicPages) && $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?page=login');
    exit;
}

/* ------------- Dispatch ------------- */
switch ($page) {
    case 'login':               loginCtrl($conn);              break;
    case 'dashboard':           adminDashboardCtrl($conn);     break;
    case 'users':               usersCtrl($conn);              break;
    case 'post_moderation':     postModerationCtrl($conn);     break;
    case 'comment_moderation':  commentModerationCtrl($conn);  break;
    default:
        header('Location: index.php?page=dashboard');
        exit;
}

mysqli_close($conn);
?>
