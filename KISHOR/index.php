<?php
session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'home';

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
    $type = $_GET['type'] ?? '';

    if ($type === 'check_email') {
        $email = trim($_GET['email'] ?? '');
        echo json_encode(['exists' => emailExists($conn, $email)]);

        exit;
    }

    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    if ($type === 'wishlist_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SESSION['user']['role'] !== 'user' || $_SESSION['user']['is_verified'] != 1) {

            http_response_code(403);
            echo json_encode(['error' => 'Only verified users can use wishlist']);
            exit;
        }
        $postId = intval($_POST['post_id'] ?? 0);
        if ($postId <= 0) {

            echo json_encode(['error' => 'Invalid post ID']);
            exit;
        }
        $ok = addToWishlist($conn, $_SESSION['user']['id'], $postId);
        echo json_encode(['success' => $ok]);
        exit;

    } 

    if ($type === 'wishlist_remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SESSION['user']['role'] !== 'user' || $_SESSION['user']['is_verified'] != 1) {
            http_response_code(403);
            echo json_encode(['error' => 'Only verified users can use wishlist']);
            exit;

        }
        $postId = intval($_POST['post_id'] ?? 0);
        if ($postId <= 0) {
            echo json_encode(['error' => 'Invalid post ID']);
            exit;

        }
        $ok = removeFromWishlist($conn, $_SESSION['user']['id'], $postId);
        echo json_encode(['success' => $ok]);

        exit;

    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$publicPages = ['login', 'register', 'home'];

if (in_array($page, ['login', 'register']) && isset($_SESSION['user'])) {
    header('Location: index.php?page=home');

    exit;
}

if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
} 

if ($page === 'wishlist') {
    if ($_SESSION['user']['role'] !== 'user' || $_SESSION['user']['is_verified'] != 1) {
        header('Location: index.php?page=home'); 
        exit;
    }
} 

switch ($page) {
    case 'login':     loginCtrl($conn);     break;
    case 'register':  registerCtrl($conn);  break;
    case 'home':      homeCtrl($conn);      break;
    case 'profile':   profileCtrl($conn);   break;
    case 'wishlist':  wishlistCtrl($conn);  break;

    default:
        header('Location: index.php?page=home');
        exit;
} 

mysqli_close($conn);
?>
