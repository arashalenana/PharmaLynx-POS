<?php
// PharmaLynx POS - PWA Registration & Meta Tags
// Determines the root URL dynamically for all PWA assets

$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost:') === 0;
// Improved HTTPS detection for proxies/load balancers
$protocol = ($is_localhost) ? 'http' : (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
    ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || 
    ($_SERVER['SERVER_PORT'] ?? '') == 443 ? 'https' : 'http'
);
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

$script_dir = dirname($script);
if (basename($script_dir) === 'pages') {
    $root_path = dirname($script_dir);
} else {
    $root_path = $script_dir;
}
$root_path = rtrim($root_path, '/');

$base_url_pwa = $protocol . '://' . $host . $root_path . '/';
$cache_bust = '?v=2.0.' . date('Ymd');
?>

<!-- PWA: Web App Manifest -->
<link rel="manifest" href="<?php echo $base_url_pwa; ?>manifest.json?v=<?php echo time(); ?>" crossorigin="use-credentials">

<!-- PWA: Theme Color -->
<meta name="theme-color" content="#8b0000">
<meta name="msapplication-TileColor" content="#8b0000">
<meta name="msapplication-TileImage" content="<?php echo $base_url_pwa; ?>assets/icons/icon-144x144.png?v=<?php echo date('Ymd'); ?>">

<!-- PWA: Apple / iOS Support -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="PharmaLynx">
<link rel="apple-touch-icon" href="<?php echo $base_url_pwa; ?>assets/icons/apple-touch-icon.png?v=<?php echo date('Ymd'); ?>">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $base_url_pwa; ?>assets/icons/icon-152x152.png?v=<?php echo date('Ymd'); ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $base_url_pwa; ?>assets/icons/apple-touch-icon.png?v=<?php echo date('Ymd'); ?>">
<link rel="apple-touch-icon" sizes="167x167" href="<?php echo $base_url_pwa; ?>assets/icons/icon-152x152.png?v=<?php echo date('Ymd'); ?>">

<!-- PWA: Mobile Optimization -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="PharmaLynx POS">
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">

<!-- PWA: Service Worker Registration with Cache-Busting -->
<script>
(function() {
    'use strict';
    
    if ('serviceWorker' in navigator) {
        // Cache-busting the service worker URL to ensure updates are detected
        var swUrl = '<?php echo $base_url_pwa; ?>sw.js?v=' + new Date().getTime();
        var swScope = '<?php echo $root_path; ?>/';

        window.addEventListener('load', function() {
            console.log('[PWA] Registering Service Worker from:', swUrl);
            navigator.serviceWorker.register(swUrl, { scope: swScope })
                .then(function(registration) {
                    console.log('[PWA] ✓ Service Worker registered successfully');

                    registration.addEventListener('updatefound', function() {
                        var newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('[PWA] New version available');
                                }
                            });
                        }
                    });
                })
                .catch(function(error) {
                    console.error('[PWA] Service Worker registration failed:', error);
                });
        });

        var refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', function() {
            if (!refreshing) {
                refreshing = true;
            }
        });
    }
})();

// PWA: Custom Install Prompt for Unsupported Browsers
(function() {
    'use strict';
    
    let deferredPrompt = null;
    let installPromptShown = false;
    const INSTALLED_KEY = 'pwa-app-installed-v2';
    
    function isAppInstalled() {
        // Check if app is already installed
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            localStorage.setItem(INSTALLED_KEY, 'true');
            return true;
        }
        return localStorage.getItem(INSTALLED_KEY) === 'true';
    }
    
    function createInstallBanner() {
        if (document.getElementById('pwa-install-banner')) return;
        
        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-label', 'Install PharmaLynx POS');
        
        banner.style.cssText = `
            display: none;
            position: fixed;
            bottom: 16px;
            right: 16px;
            width: 360px;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border: 2px solid #8b0000;
            border-radius: 16px;
            padding: 20px;
            z-index: 99999;
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.5), 0 0 2px rgba(139, 0, 0, 0.5);
            animation: slideUpPWA 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        `;
        
        banner.innerHTML = `
            <style>
                @keyframes slideUpPWA {
                    from {
                        transform: translateY(120px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                @media (max-width: 600px) {
                    #pwa-install-banner {
                        width: calc(100% - 32px) !important;
                        right: 16px !important;
                        left: 16px !important;
                        bottom: 16px !important;
                    }
                }
                
                #pwa-install-btn:active {
                    transform: scale(0.98);
                }
                
                #pwa-dismiss-btn:active {
                    background-color: #333 !important;
                }
            </style>
            <div style="display: flex; gap: 16px; align-items: flex-start;">
                <img src="<?php echo $base_url_pwa; ?>assets/icons/icon-96x96.png" alt="PharmaLynx" style="width: 56px; height: 56px; border-radius: 12px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.4); border: 2px solid #8b0000;">
                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="margin: 0 0 6px 0; font-size: 1em; font-weight: 700; color: #fff; letter-spacing: 0.5px;">Install PharmaLynx</h3>
                        <p style="color: #bbb; font-size: 0.9em; margin: 0 0 14px 0; line-height: 1.5;">Get instant access, work offline, and manage your pharmacy from anywhere.</p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%;">
                        <button id="pwa-install-btn" style="background: linear-gradient(135deg, #8b0000 0%, #a50000 100%); color: #fff; border: none; padding: 12px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9em; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Install Now</button>
                        <button id="pwa-dismiss-btn" style="background: rgba(255,255,255,0.1); color: #ccc; border: 1px solid rgba(255,255,255,0.2); padding: 12px 16px; border-radius: 8px; cursor: pointer; font-size: 0.9em; transition: all 0.2s ease; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Later</button>
                    </div>
                </div>
                <button id="pwa-close-btn" style="background: transparent; border: none; color: #888; cursor: pointer; font-size: 1.4em; padding: 0; width: 28px; height: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: color 0.2s ease; font-weight: 300;">×</button>
            </div>
        `;
        document.body.appendChild(banner);
        
        // Install button
        document.getElementById('pwa-install-btn').addEventListener('click', function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('[PWA] User accepted installation');
                        localStorage.setItem(INSTALLED_KEY, 'true');
                    } else {
                        console.log('[PWA] User declined installation');
                    }
                    deferredPrompt = null;
                    banner.style.display = 'none';
                });
            }
        });
        
        // Dismiss button - shows again on next login
        document.getElementById('pwa-dismiss-btn').addEventListener('click', function() {
            banner.style.display = 'none';
        });
        
        // Close button - shows again on next login
        document.getElementById('pwa-close-btn').addEventListener('click', function() {
            banner.style.display = 'none';
        });
        
        // Hover effects
        document.getElementById('pwa-install-btn').addEventListener('mouseover', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 6px 20px rgba(139, 0, 0, 0.5)';
        });
        document.getElementById('pwa-install-btn').addEventListener('mouseout', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(139, 0, 0, 0.3)';
        });
        
        document.getElementById('pwa-dismiss-btn').addEventListener('mouseover', function() {
            this.style.backgroundColor = 'rgba(255,255,255,0.15)';
            this.style.borderColor = 'rgba(255,255,255,0.3)';
        });
        document.getElementById('pwa-dismiss-btn').addEventListener('mouseout', function() {
            this.style.backgroundColor = 'rgba(255,255,255,0.1)';
            this.style.borderColor = 'rgba(255,255,255,0.2)';
        });
        
        document.getElementById('pwa-close-btn').addEventListener('mouseover', function() {
            this.style.color = '#aaa';
        });
        document.getElementById('pwa-close-btn').addEventListener('mouseout', function() {
            this.style.color = '#888';
        });
    }
    // Show the install prompt if the event was fired or if on unsupported platforms
    function showPrompt() {
        if (isAppInstalled() || installPromptShown) return;
        createInstallBanner();
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.style.display = 'block';
            installPromptShown = true;
            console.log('[PWA] Install prompt displayed');
        }
    }

    // Listen for the beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        console.log('[PWA] beforeinstallprompt event fired - ready to install');
        
        // Show prompt after a short delay
        setTimeout(showPrompt, 1500);
    });

    // App installed event
    window.addEventListener('appinstalled', function() {
        console.log('[PWA] ✓ App installed successfully!');
        const banner = document.getElementById('pwa-install-banner');
        if (banner) banner.style.display = 'none';
        deferredPrompt = null;
        localStorage.setItem(INSTALLED_KEY, 'true');
    });

    // Manual installation guides for iOS and Firefox
    window.addEventListener('load', function() {
        const ua = navigator.userAgent;
        const isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
        const isAndroid = /Android/.test(ua);
        const isFirefoxMobile = isAndroid && /Firefox/.test(ua);
        const isChromeMobile = isAndroid && /Chrome/.test(ua);
        const isSafariMobile = isIOS;
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        console.log('[PWA] Device Detection - iOS:', isIOS, 'Android:', isAndroid, 'Standalone:', isStandalone);

        // iOS Safari - Show manual guide
        if (isSafariMobile && !isStandalone && !isAppInstalled() && !installPromptShown) {
            setTimeout(function() {
                if (!deferredPrompt && !installPromptShown && !isAppInstalled()) {
                    createInstallBanner();
                    const banner = document.getElementById('pwa-install-banner');
                    const btn = document.getElementById('pwa-install-btn');
                    btn.textContent = 'How to Install';
                    btn.style.background = 'linear-gradient(135deg, #8b0000 0%, #a50000 100%)';
                    btn.onclick = function() {
                        alert('📱 To install PharmaLynx on iOS:\n\n1. Tap the Share button (box with arrow)\n2. Scroll down and tap "Add to Home Screen"\n3. Enter a name and tap "Add"\n\nThe app will appear on your home screen!');
                    };
                    banner.style.display = 'block';
                    installPromptShown = true;
                }
            }, 1500);
        }
        // Android Firefox - Show manual guide
        else if (isFirefoxMobile && !isStandalone && !isAppInstalled() && !installPromptShown) {
            setTimeout(function() {
                if (!deferredPrompt && !installPromptShown && !isAppInstalled()) {
                    createInstallBanner();
                    const banner = document.getElementById('pwa-install-banner');
                    const btn = document.getElementById('pwa-install-btn');
                    btn.textContent = 'How to Install';
                    btn.style.background = 'linear-gradient(135deg, #8b0000 0%, #a50000 100%)';
                    btn.onclick = function() {
                        alert('🔧 To install PharmaLynx on Firefox:\n\n1. Tap the menu button (three dots)\n2. Select "Install"\n3. Confirm the installation\n\nThe app will be installed on your device!');
                    };
                    banner.style.display = 'block';
                    installPromptShown = true;
                }
            }, 1500);
        }
        // Android Chrome - Show manual guide if beforeinstallprompt not supported
        else if (isChromeMobile && !isStandalone && !isAppInstalled() && !installPromptShown) {
            setTimeout(function() {
                if (!deferredPrompt && !installPromptShown && !isAppInstalled()) {
                    createInstallBanner();
                    const banner = document.getElementById('pwa-install-banner');
                    const btn = document.getElementById('pwa-install-btn');
                    btn.textContent = 'How to Install';
                    btn.style.background = 'linear-gradient(135deg, #8b0000 0%, #a50000 100%)';
                    btn.onclick = function() {
                        alert('📱 To install PharmaLynx on Android Chrome:\n\n1. Tap the menu button (three dots)\n2. Select "Install app" or "Add to Home Screen"\n3. Confirm the installation\n\nThe app will appear on your home screen!');
                    };
                    banner.style.display = 'block';
                    installPromptShown = true;
                }
            }, 2000);
        }
    });
})();
</script>
