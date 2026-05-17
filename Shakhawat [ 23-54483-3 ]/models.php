<?php
// ================================================================
// MODELS - All DB access using procedural mysqli + prepared stmts
// Task 3: Admin Dashboard - Users, Posts, Comments
// ================================================================

/* ------------------- Auth ------------------- */
function getUserByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, password_hash, role, is_verified FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function setRememberToken($conn, $userId, $tokenHash) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $tokenHash, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getUserByRememberToken($conn, $tokenHash) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, is_verified FROM users WHERE remember_token = ?");
    mysqli_stmt_bind_param($stmt, 's', $tokenHash);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function clearRememberToken($conn, $userId) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/* ------------------- Dashboard Counts ------------------- */
function getDashboardCounts($conn) {
    $counts = [];
    $stmt = mysqli_prepare($conn, "SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $counts['admins'] = 0; $counts['scouts'] = 0; $counts['users'] = 0; $counts['total_users'] = 0;
    while ($row = mysqli_fetch_assoc($r)) {
        $key = $row['role'] === 'admin' ? 'admins' : ($row['role'] === 'scout' ? 'scouts' : 'users');
        $counts[$key] = intval($row['cnt']);
        $counts['total_users'] += intval($row['cnt']);
    }
    mysqli_stmt_close($stmt);

    $stmt2 = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM post_requests WHERE status = 'pending'");
    mysqli_stmt_execute($stmt2);
    $counts['pending_requests'] = intval(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2))['cnt']);
    mysqli_stmt_close($stmt2);

    $stmt3 = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM posts WHERE status = 'approved'");
    mysqli_stmt_execute($stmt3);
    $counts['total_posts'] = intval(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt3))['cnt']);
    mysqli_stmt_close($stmt3);

    $stmt4 = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM comments");
    mysqli_stmt_execute($stmt4);
    $counts['total_comments'] = intval(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt4))['cnt']);
    mysqli_stmt_close($stmt4);

    return $counts;
}

/* ------------------- User Management ------------------- */
function getUsers($conn) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, is_verified, created_at FROM users ORDER BY id DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function searchUsers($conn, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, is_verified, created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function addUser($conn, $name, $email, $password, $role, $isVerified) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, role, is_verified) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $hash, $role, $isVerified);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function emailExists($conn, $email, $excludeId = null) {
    if ($excludeId) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function toggleUserVerification($conn, $id, $isVerified) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_verified = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $isVerified, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteUser($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role != 'admin'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

/* ------------------- Post Moderation ------------------- */
function getPendingRequests($conn) {
    $stmt = mysqli_prepare($conn, "SELECT pr.*, u.name AS scout_name FROM post_requests pr JOIN users u ON pr.scout_id = u.id WHERE pr.status = 'pending' ORDER BY pr.requested_at DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getPostRequestById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function approvePostRequest($conn, $id) {
    $req = getPostRequestById($conn, $id);
    if (!$req || $req['status'] !== 'pending') return false;

    $data = json_decode($req['post_data'], true);
    if (!$data) return false;

    $title      = $data['title'] ?? '';
    $history    = $data['short_history'] ?? '';
    $country    = $data['country'] ?? '';
    $genre      = $data['genre'] ?? '';
    $costLevel  = $data['cost_level'] ?? 'low';
    $travelInfo = $data['travel_medium_info'] ?? '';
    $image      = $data['image'] ?? '';
    $scoutId    = $req['scout_id'];

    // If it's a change request, update existing post
    if (!empty($req['original_post_id'])) {
        $stmt = mysqli_prepare($conn, "UPDATE posts SET title=?, short_history=?, country=?, genre=?, cost_level=?, travel_medium_info=?, image=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssssi', $title, $history, $country, $genre, $costLevel, $travelInfo, $image, $req['original_post_id']);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Create new post
        $stmt = mysqli_prepare($conn, "INSERT INTO posts (scout_id, title, short_history, country, genre, cost_level, travel_medium_info, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
        mysqli_stmt_bind_param($stmt, 'isssssss', $scoutId, $title, $history, $country, $genre, $costLevel, $travelInfo, $image);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Update request status
    $stmt2 = mysqli_prepare($conn, "UPDATE post_requests SET status = 'approved' WHERE id = ?");
    mysqli_stmt_bind_param($stmt2, 'i', $id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    return $ok;
}

function rejectPostRequest($conn, $id) {
    $stmt = mysqli_prepare($conn, "UPDATE post_requests SET status = 'rejected' WHERE id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

function getAllPosts($conn) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, u.name AS scout_name FROM posts p JOIN users u ON p.scout_id = u.id ORDER BY p.created_at DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getPost($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function updatePost($conn, $id, $title, $history, $country, $genre, $costLevel, $travelInfo) {
    $stmt = mysqli_prepare($conn, "UPDATE posts SET title=?, short_history=?, country=?, genre=?, cost_level=?, travel_medium_info=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'ssssssi', $title, $history, $country, $genre, $costLevel, $travelInfo, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deletePost($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

/* ------------------- Comment Moderation ------------------- */
function getAllComments($conn) {
    $stmt = mysqli_prepare($conn, "SELECT c.*, u.name AS user_name, p.title AS post_title FROM comments c JOIN users u ON c.user_id = u.id JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function searchComments($conn, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn, "SELECT c.*, u.name AS user_name, p.title AS post_title FROM comments c JOIN users u ON c.user_id = u.id JOIN posts p ON c.post_id = p.id WHERE c.content LIKE ? OR u.name LIKE ? OR p.title LIKE ? ORDER BY c.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function deleteComment($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
?>
