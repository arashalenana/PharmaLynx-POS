<?php
// Header Include
 

// Load centralized path configuration (safe, idempotent)
if (!defined('PHARMALYNX_PATHS_LOADED')) {
    require_once dirname(__FILE__) . '/../config/paths.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#8b0000">
    <meta name="mobile-web-app-capable" content="yes">
    <title>PharmaLynx POS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $asset_path; ?>images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $asset_path; ?>images/favicon-16x16.png">
    <link rel="shortcut icon" href="<?php echo $asset_path; ?>images/favicon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Application CSS -->
    <link rel="stylesheet" href="<?php echo $asset_path; ?>css/style.css">

    <!-- AI Assistant CSS -->
    <link rel="stylesheet" href="<?php echo $chatbot_path; ?>ai-assistant.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- PWA: Manifest, meta tags, and service worker registration -->
    <?php include dirname(__FILE__) . '/pwa.php'; ?>
</head>
<body>
    <div class="wrapper d-flex align-items-stretch">
