<?php
// Path and URL Configuration
 
if (!defined('PHARMALYNX_PATHS_LOADED')) {
    define('PHARMALYNX_PATHS_LOADED', true);

    // Detect subdirectory depth
    $current_uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $script_name  = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $script_dir   = dirname($script_name);

    // Determine if we are inside a subdirectory 
    $is_subpage   = (strpos($current_uri, '/pages/') !== false);

    // Relative path helpers 
    $base_path    = $is_subpage ? '../'         : '';
    $asset_path   = $is_subpage ? '../assets/'  : 'assets/';
    $chatbot_path = $is_subpage ? '../ai-assistant/' : 'ai-assistant/';

    // Base URL construction (for links and redirects) 
    $is_localhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0;
    // Determine protocol based on server variables
    $protocol = ($is_localhost) ? 'http' : (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
        ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || 
        ($_SERVER['SERVER_PORT'] ?? '') == 443 ? 'https' : 'http'
    );
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Determine root directory for URL construction
    if ($is_subpage) {
        $root_dir = dirname($script_dir); 
    } else {
        $root_dir = $script_dir;          
    }
    $root_dir = rtrim($root_dir, '/');

    $base_url = $protocol . '://' . $host . $root_dir . '/';

    // Filesystem helpers (for include/require) 
    $fs_root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . $root_dir . '/';
}
