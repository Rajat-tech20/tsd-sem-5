<?php
// ============================================================
//  HELPER FUNCTIONS
//  includes/functions.php
// ============================================================

/**
 * Sanitise output to prevent XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Format a datetime string for display
 * e.g. "2026-08-17 08:00:00"  →  "August 17, 2026"
 */
function formatDate(string $datetime, string $format = 'F j, Y'): string {
    return date($format, strtotime($datetime));
}

/**
 * Return a human-friendly "time ago" string
 */
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 3600)   return floor($diff / 60) . ' mins ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hrs ago';
    if ($diff < 172800) return 'Yesterday';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDate($datetime);
}

/**
 * Redirect and exit
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Check if the current user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user is an admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if the current user is an author or admin
 */
function isAuthor(): bool {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'author']);
}

/**
 * Require login — redirects to login.php if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) redirect('/chronicle/login.php');
}

/**
 * Require admin — redirects to homepage if not admin
 */
function requireAdmin(): void {
    if (!isAdmin()) redirect('/chronicle/index.php');
}

/**
 * Generate a URL-safe slug from a title
 */
function slugify(string $title): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}
