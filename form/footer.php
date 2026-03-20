<?php
// Must be included from within the form, not accessed directly.
if (!defined('NIPPONIA_FORM')) { http_response_code(403); exit; }
?>
    <footer class="site-footer">
        <div class="container text-center">
            <small>&copy; <?= date('Y') ?> Nipponia S.A. All rights reserved.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
