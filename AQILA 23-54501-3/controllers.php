<?php
// CONTROLLERS - request handling  , role-based logic

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
                if ($user['role'] !== 'scout') {
                    $error = 'Access restricted to scout accounts only.';
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

function dashboardCtrl($conn) {
    $counts = getScoutRequestCounts($conn, $_SESSION['user']['id']);
    require 'views/scout_dashboard.php';
}

function createRequestCtrl($conn) {
    $error = $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title       = trim($_POST['title'] ?? '');
        $history     = trim($_POST['short_history'] ?? '');
        $country     = trim($_POST['country'] ?? '');
        $genre       = trim($_POST['genre'] ?? '');
        $costLevel   = trim($_POST['cost_level'] ?? '');
        $travelInfo  = trim($_POST['travel_medium_info'] ?? '');

        if ($title === '' || $history === '' || $country === '' || $genre === '' || $costLevel === '' || $travelInfo === '') {
            $error = 'All fields are required.';
        } elseif (!in_array($costLevel, ['low', 'medium', 'high'])) {
            $error = 'Invalid cost level.';
        } elseif (!in_array($genre, ['beach', 'mountain', 'city', 'historical', 'adventure', 'cultural', 'nature'])) {
            $error = 'Invalid genre selected.';
        } else {
            $imagePath = '';
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    $error = 'Image must be JPG, PNG, or WebP.';
                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $error = 'Image must be under 5MB.';
                } else {
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest = 'uploads/posts/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        $imagePath = $dest;
                    } else {
                        $error = 'Failed to upload image.';
                    }
                }
            }

            if ($error === '') {
                $postData = json_encode([
                    'title'              => $title,
                    'short_history'      => $history,
                    'country'            => $country,
                    'genre'              => $genre,
                    'cost_level'         => $costLevel,
                    'travel_medium_info' => $travelInfo,
                    'image'              => $imagePath
                ]);

                if (createPostRequest($conn, $_SESSION['user']['id'], $postData)) {
                    header('Location: index.php?page=my_requests&msg=created');
                    exit;
                }
                $error = 'Failed to submit request.';
            }
        }
    }

    require 'views/create_request.php';
}

function myRequestsCtrl($conn) {
    $requests = getScoutRequests($conn, $_SESSION['user']['id']);
    require 'views/my_requests.php';
}

function editRequestCtrl($conn) {
    $id    = intval($_GET['id'] ?? 0);
    $error = '';
    $request = getPostRequest($conn, $id, $_SESSION['user']['id']);

    if (!$request || $request['status'] !== 'pending') {
        header('Location: index.php?page=my_requests');
        exit;
    }

    $editing = json_decode($request['post_data'], true);
    $editing['id'] = $request['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title       = trim($_POST['title'] ?? '');
        $history     = trim($_POST['short_history'] ?? '');
        $country     = trim($_POST['country'] ?? '');
        $genre       = trim($_POST['genre'] ?? '');
        $costLevel   = trim($_POST['cost_level'] ?? '');
        $travelInfo  = trim($_POST['travel_medium_info'] ?? '');

        if ($title === '' || $history === '' || $country === '' || $genre === '' || $costLevel === '' || $travelInfo === '') {
            $error = 'All fields are required.';
        } elseif (!in_array($costLevel, ['low', 'medium', 'high'])) {
            $error = 'Invalid cost level.';
        } else {
            $imagePath = $editing['image'] ?? '';

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    $error = 'Image must be JPG, PNG, or WebP.';
                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $error = 'Image must be under 5MB.';
                } else {
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest = 'uploads/posts/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        $imagePath = $dest;
                    }
                }
            }

            if ($error === '') {
                $postData = json_encode([
                    'title'              => $title,
                    'short_history'      => $history,
                    'country'            => $country,
                    'genre'              => $genre,
                    'cost_level'         => $costLevel,
                    'travel_medium_info' => $travelInfo,
                    'image'              => $imagePath
                ]);

                if (updatePostRequest($conn, $id, $_SESSION['user']['id'], $postData)) {
                    header('Location: index.php?page=my_requests&msg=updated');
                    exit;
                }
                $error = 'Failed to update request.';
            }

            $editing = compact('title', 'country', 'genre', 'travelInfo');
            $editing['short_history'] = $history;
            $editing['cost_level'] = $costLevel;
            $editing['travel_medium_info'] = $travelInfo;
            $editing['image'] = $imagePath;
            $editing['id'] = $id;
        }
    }

    require 'views/edit_request.php';
}

function approvedPostsCtrl($conn) {
    $posts = getApprovedPostsByScout($conn, $_SESSION['user']['id']);
    require 'views/approved_posts.php';
}
?>
