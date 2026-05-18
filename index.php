<?php

// FRONT CONTROLLER (router)
//  Scout Post Requests & Information Submission

session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $tokenHash = hash('sha256', $_COOKIE['remember_token']);
    $remembered = getUserByRememberToken($conn, $tokenHash);
    if ($remembered && $remembered['role'] === 'scout') {
        $_SESSION['user'] = [
            'id'          => $remembered['id'],
            'name'        => $remembered['name'],
            'email'       => $remembered['email'],
            'role'        => $remembered['role'],
            'is_verified' => $remembered['is_verified']
        ];
    }
}

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

if ($page === 'ajax') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'scout') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $type = $_GET['type'] ?? '';

    if ($type === 'delete_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid request ID']);
            exit;
        }
        $ok = deletePostRequest($conn, $id, $_SESSION['user']['id']);
        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($type === 'search_requests') {
        $q = trim($_GET['q'] ?? '');
        $rows = $q === '' ? getScoutRequests($conn, $_SESSION['user']['id']) : searchScoutRequests($conn, $_SESSION['user']['id'], $q);
        // Decode post_data for each row
        $result = [];
        foreach ($rows as $row) {
            $data = json_decode($row['post_data'], true);
            $data['id'] = $row['id'];
            $data['status'] = $row['status'];
            $data['requested_at'] = $row['requested_at'];
            $result[] = $data;
        }
        echo json_encode($result);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$publicPages = ['login'];

if ($page === 'login' && isset($_SESSION['user'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

if (!in_array($page, $publicPages) && isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] !== 'scout') {
        header('Location: index.php?page=login');
        exit;
    }
    if ($_SESSION['user']['is_verified'] != 1) {
        // Show pending verification page
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Pending Verification</title><link rel="stylesheet" href="style.css"></head><body class="app-body"><div class="main-content"><div class="alert alert-warning" style="margin-top:40px;">&#9888; Your scout account is pending admin verification. Please check back later.</div><a href="index.php?page=logout" class="btn btn-ghost">Logout</a></div></body></html>';
        exit;
    }
}

switch ($page) {
    case 'login':          loginCtrl($conn);          break;
    case 'dashboard':      dashboardCtrl($conn);      break;
    case 'create_request': createRequestCtrl($conn);  break;
    case 'my_requests':    myRequestsCtrl($conn);     break;
    case 'edit_request':   editRequestCtrl($conn);    break;
    case 'approved_posts': approvedPostsCtrl($conn);  break;
    default:
        header('Location: index.php?page=dashboard');
        exit;
}

mysqli_close($conn);
?>
