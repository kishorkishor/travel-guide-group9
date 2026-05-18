<?php
// ================================================================
// MODELS - All DB access using procedural mysqli + prepared stmts
// Task 4: Browse Posts, Search, Comments, Cost Estimate
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

/* ------------------- Browse Posts ------------------- */
function getApprovedPosts($conn) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, u.name AS scout_name FROM posts p JOIN users u ON p.scout_id = u.id WHERE p.status = 'approved' ORDER BY p.created_at DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getApprovedPost($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT p.*, u.name AS scout_name FROM posts p JOIN users u ON p.scout_id = u.id WHERE p.id = ? AND p.status = 'approved'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function getDistinctCountries($conn) {
    $stmt = mysqli_prepare($conn, "SELECT DISTINCT country FROM posts WHERE status = 'approved' ORDER BY country ASC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $countries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $countries[] = $row['country'];
    }
    mysqli_stmt_close($stmt);
    return $countries;
}

/* ------------------- Search & Filter ------------------- */
function searchAndFilterPosts($conn, $query, $country, $genre, $costLevel) {
    $sql = "SELECT p.*, u.name AS scout_name FROM posts p JOIN users u ON p.scout_id = u.id WHERE p.status = 'approved'";
    $types = '';
    $params = [];

    if ($query !== '') {
        $sql .= " AND (p.title LIKE ? OR p.country LIKE ?)";
        $like = '%' . $query . '%';
        $types .= 'ss';
        $params[] = $like;
        $params[] = $like;
    }

    if ($country !== '') {
        $sql .= " AND p.country = ?";
        $types .= 's';
        $params[] = $country;
    }

    if ($genre !== '') {
        $sql .= " AND p.genre = ?";
        $types .= 's';
        $params[] = $genre;
    }

    if ($costLevel !== '' && in_array($costLevel, ['low', 'medium', 'high'])) {
        $sql .= " AND p.cost_level = ?";
        $types .= 's';
        $params[] = $costLevel;
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

/* ------------------- Comments ------------------- */
function getComments($conn, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT c.*, u.name AS user_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function addComment($conn, $postId, $userId, $content) {
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iis', $postId, $userId, $content);
    $ok = mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $ok ? $newId : false;
}

function deleteOwnComment($conn, $commentId, $userId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $commentId, $userId);
    $ok = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected > 0;
}

/* ------------------- Cost Estimates ------------------- */
function getCostEstimate($conn, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM cost_estimates WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function calculateCost($conn, $postId, $travelers, $days) {
    $estimate = getCostEstimate($conn, $postId);

    if ($estimate) {
        $baseCost = floatval($estimate['base_cost']);
        $currency = $estimate['currency'];
    } else {
        // Derive from post cost_level
        $stmt = mysqli_prepare($conn, "SELECT cost_level FROM posts WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $postId);
        mysqli_stmt_execute($stmt);
        $post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$post) return null;

        $mapping = ['low' => 500, 'medium' => 1500, 'high' => 3000];
        $baseCost = $mapping[$post['cost_level']] ?? 1000;
        $currency = 'USD';
    }

    $total = $baseCost * $travelers * ($days / 7);
    return [
        'base_cost' => $baseCost,
        'currency'  => $currency,
        'travelers' => $travelers,
        'days'      => $days,
        'total'     => round($total, 2)
    ];
}
?>
