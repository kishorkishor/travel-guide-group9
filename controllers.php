<?php
// ================================================================
// CONTROLLERS - request handling + role-based logic
// Task 4: Browse Posts, Search, Comments, Cost Estimate
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
                header('Location: index.php?page=browse');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }
    require 'views/login.php';
}

/* ============== Browse Published Posts ============== */
function browseCtrl($conn) {
    $posts = getApprovedPosts($conn);
    $countries = getDistinctCountries($conn);
    require 'views/browse.php';
}

/* ============== Post Detail ============== */
function postDetailCtrl($conn) {
    $id = intval($_GET['id'] ?? 0);
    $post = getApprovedPost($conn, $id);

    if (!$post) {
        header('Location: index.php?page=browse');
        exit;
    }

    $comments = getComments($conn, $id);
    $costEstimate = getCostEstimate($conn, $id);

    // Derive base cost if no estimate
    if (!$costEstimate) {
        $mapping = ['low' => 500, 'medium' => 1500, 'high' => 3000];
        $baseCost = $mapping[$post['cost_level']] ?? 1000;
    } else {
        $baseCost = floatval($costEstimate['base_cost']);
    }

    require 'views/post_detail.php';
}
?>
