<?php
// Must be included from within the form, not accessed directly.
if (!defined('NIPPONIA_FORM')) { http_response_code(403); exit; }
?>
    <footer class="site-footer">
        <div class="container text-center">
            <small>&copy; <?= date('Y') ?> Nipponia S.A. All rights reserved.</small>
        </div>
    </footer>

</body>
</html>
