<?php
// ================================================================
// FRONT CONTROLLER (router)
// Task 4: Browse Posts, Search, Comments, Cost Estimate
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
    if ($remembered) {
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

    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $type = $_GET['type'] ?? '';

    // Search & Filter posts
    if ($type === 'search_posts') {
        $q         = trim($_GET['q'] ?? '');
        $country   = trim($_GET['country'] ?? '');
        $genre     = trim($_GET['genre'] ?? '');
        $costLevel = trim($_GET['cost_level'] ?? '');
        $rows = searchAndFilterPosts($conn, $q, $country, $genre, $costLevel);
        echo json_encode($rows);
        exit;
    }

    // Add comment (only verified general users)
    if ($type === 'add_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SESSION['user']['role'] !== 'user' || $_SESSION['user']['is_verified'] != 1) {
            http_response_code(403);
            echo json_encode(['error' => 'Only verified users can comment.']);
            exit;
        }
        $postId  = intval($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($postId <= 0) {
            echo json_encode(['error' => 'Invalid post.']);
            exit;
        }
        if ($content === '') {
            echo json_encode(['error' => 'Comment cannot be empty.']);
            exit;
        }
        if (mb_strlen($content) > 1000) {
            echo json_encode(['error' => 'Comment too long (max 1000 characters).']);
            exit;
        }

        $newId = addComment($conn, $postId, $_SESSION['user']['id'], $content);
        if ($newId) {
            echo json_encode([
                'success'   => true,
                'comment'   => [
                    'id'         => $newId,
                    'user_name'  => $_SESSION['user']['name'],
                    'content'    => htmlspecialchars($content),
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Failed to add comment.']);
        }
        exit;
    }

    // Delete own comment
    if ($type === 'delete_comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $commentId = intval($_POST['id'] ?? 0);
        $ok = deleteOwnComment($conn, $commentId, $_SESSION['user']['id']);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // Calculate cost
    if ($type === 'calculate_cost') {
        $postId    = intval($_GET['post_id'] ?? 0);
        $travelers = intval($_GET['travelers'] ?? 1);
        $days      = intval($_GET['days'] ?? 1);

        if ($postId <= 0 || $travelers < 1 || $travelers > 10 || $days < 1) {
            echo json_encode(['error' => 'Invalid input.']);
            exit;
        }

        $result = calculateCost($conn, $postId, $travelers, $days);
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['error' => 'Post not found.']);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

/* ------------- Auth Gates ------------- */
$publicPages = ['login'];

if ($page === 'login' && isset($_SESSION['user'])) {
    header('Location: index.php?page=browse');
    exit;
}

if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

// Must be verified to browse
if (!in_array($page, $publicPages) && isset($_SESSION['user']) && $_SESSION['user']['is_verified'] != 1) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Pending</title><link rel="stylesheet" href="style.css"></head><body class="app-body"><div class="main-content"><div class="alert alert-warning" style="margin-top:40px;">&#9888; Your account is pending admin verification.</div><a href="index.php?page=logout" class="btn btn-ghost">Logout</a></div></body></html>';
    exit;
}

/* ------------- Dispatch ------------- */
switch ($page) {
    case 'login':       loginCtrl($conn);       break;
    case 'browse':      browseCtrl($conn);      break;
    case 'post_detail': postDetailCtrl($conn);  break;
    default:
        header('Location: index.php?page=browse');
        exit;
}

mysqli_close($conn);
?>
