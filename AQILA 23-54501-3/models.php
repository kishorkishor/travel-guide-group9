<?php


/*  Auth  */
function getUserByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, password_hash, role, is_verified, profile_picture FROM users WHERE email = ?");
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

/* Post Requests */
function createPostRequest($conn, $scoutId, $postDataJson, $originalPostId = null) {
    $stmt = mysqli_prepare($conn, "INSERT INTO post_requests (scout_id, post_data, original_post_id, status) VALUES (?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, 'isi', $scoutId, $postDataJson, $originalPostId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getScoutRequests($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE scout_id = ? ORDER BY requested_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getPostRequest($conn, $id, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE id = ? AND scout_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $scoutId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function updatePostRequest($conn, $id, $scoutId, $postDataJson) {
    $stmt = mysqli_prepare($conn, "UPDATE post_requests SET post_data = ? WHERE id = ? AND scout_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'sii', $postDataJson, $id, $scoutId);
    $ok = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

function deletePostRequest($conn, $id, $scoutId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM post_requests WHERE id = ? AND scout_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $scoutId);
    $ok = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

function searchScoutRequests($conn, $scoutId, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn, "SELECT * FROM post_requests WHERE scout_id = ? AND post_data LIKE ? ORDER BY requested_at DESC");
    mysqli_stmt_bind_param($stmt, 'is', $scoutId, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

/* Dashboard Counts  */
function getScoutRequestCounts($conn, $scoutId) {
    $counts = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

    $stmt = mysqli_prepare($conn, "SELECT status, COUNT(*) as cnt FROM post_requests WHERE scout_id = ? GROUP BY status");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $counts[$row['status']] = intval($row['cnt']);
        $counts['total'] += intval($row['cnt']);
    }
    mysqli_stmt_close($stmt);
    return $counts;
}

/*  Approved Posts (Scout's own)  */
function getApprovedPostsByScout($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT id, title, country, genre, cost_level, short_history, image, status, created_at FROM posts WHERE scout_id = ? AND status = 'approved' ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getApprovedPost($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ? AND status = 'approved'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
?>
