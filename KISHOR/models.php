<?php

function getUserByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, password_hash, role, is_verified, profile_picture FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
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

function registerUser($conn, $name, $email, $password, $role) { 
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, role, is_verified) VALUES (?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hash, $role);

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function setRememberToken($conn, $userId, $tokenHash) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = ? WHERE id = ?"); 
    mysqli_stmt_bind_param($stmt, 'si', $tokenHash, $userId);

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getUserByRememberToken($conn, $tokenHash) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, is_verified, profile_picture FROM users WHERE remember_token = ?");
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

function getUserById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, is_verified, profile_picture, created_at FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return $row;
}

function updateProfile($conn, $id, $name, $email, $picture) {
    if ($picture !== null) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'sssi', $name, $email, $picture, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?");

        mysqli_stmt_bind_param($stmt, 'ssi', $name, $email, $id);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updatePassword($conn, $id, $newPassword) {

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getUserPassword($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ? $row['password_hash'] : null;
}

function getApprovedPosts($conn, $limit = 12) { 
    $stmt = mysqli_prepare($conn, "SELECT p.id, p.title, p.country, p.genre, p.cost_level, p.short_history, p.image, p.created_at, u.name AS scout_name FROM posts p JOIN users u ON p.scout_id = u.id WHERE p.status = 'approved' ORDER BY p.created_at DESC LIMIT ?");
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC); 
    mysqli_stmt_close($stmt);
    return $rows;
} 

function getWishlist($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT w.id, w.post_id, w.added_at, p.title, p.country, p.cost_level, p.image, p.genre FROM wishlist w JOIN posts p ON w.post_id = p.id WHERE w.user_id = ? ORDER BY w.added_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);

    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function addToWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO wishlist (user_id, post_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt); 

    return $ok;

}

function removeFromWishlist($conn, $userId, $postId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM wishlist WHERE user_id = ? AND post_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function isInWishlist($conn, $userId, $postId) { 
    $stmt = mysqli_prepare($conn, "SELECT id FROM wishlist WHERE user_id = ? AND post_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt); 
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}
?>
