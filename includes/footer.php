    </div> <!-- End of #content -->
    
    <?php
    // Footer Include

    // Load centralized path configuration (safe, idempotent)
    if (!defined('PHARMALYNX_PATHS_LOADED')) {
        require_once dirname(__FILE__) . '/../config/paths.php';
    }

    // Include AI Assistant UI using filesystem-safe absolute path
    $chatbot_ui_file = dirname(__FILE__) . '/../ai-assistant/ai-assistant-ui.php';
    if (file_exists($chatbot_ui_file)) {
        include $chatbot_ui_file;
    }
    ?>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');

            if (sidebarCollapse && sidebar && content) {
                sidebarCollapse.addEventListener('click', function (e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                    content.classList.toggle('active');
                });

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    const isClickInside = sidebar.contains(event.target) || sidebarCollapse.contains(event.target);
                    if (!isClickInside && sidebar.classList.contains('active') && window.innerWidth <= 992) {
                        sidebar.classList.remove('active');
                        content.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>
