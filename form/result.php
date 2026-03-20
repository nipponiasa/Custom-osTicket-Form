<?php
// secure.inc.php and client.inc.php use relative paths anchored to the osTicket root,
// so the working directory must be the root before bootstrapping.
chdir(__DIR__ . '/..');

// $base must point to the document root (parent of this file's directory).
$base = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

require __DIR__ . '/form-bootstrap.php';

// Consume the one-time flash set by submit.php.
$flash = $_SESSION['form_flash'] ?? null;
unset($_SESSION['form_flash']);

// Dev preview if PREVIEW_RESULT is enabled in config.php
// /form/result.php?status=error&detail=HTTP+422%3A+Missing+required+field
// /form/result.php?status=success&ticket_id=306621
if (!$flash && defined('PREVIEW_RESULT') && PREVIEW_RESULT) {
    $status = in_array($_GET['status'] ?? '', ['success', 'error'], true) ? $_GET['status'] : null;
    if ($status) {
        $flash = ['status' => $status];
        if ($status === 'success') {
            $flash['ticket_id'] = $_GET['ticket_id'] ?? 'XXXXX';
        } else {
            $flash['detail'] = $_GET['detail'] ?? '';
        }
    }
}

// No flash means someone navigated here directly.
if (!$flash) {
    header('Location: ' . $base . '/new.php');
    exit;
}

$is_success = ($flash['status'] === 'success');
$ticket_id   = $is_success ? htmlspecialchars($flash['ticket_id'], ENT_QUOTES, 'UTF-8') : '';
$error_detail = !$is_success && !empty($flash['detail'])
    ? htmlspecialchars($flash['detail'], ENT_QUOTES, 'UTF-8')
    : '';

require __DIR__ . '/header.php';
?>
<main class="flex-grow-1">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <?php if ($is_success): ?>

                <div class="card border-success">
                    <div class="card-body text-center py-5">
                        <div class="mb-3 text-success" style="font-size:3rem;">&#10003;</div>
                        <h2 class="card-title"><?= t('result.success.title') ?></h2>
                        <p class="card-text mt-3"><?= t('result.success.lead') ?></p>
                        <?php if ($ticket_id): ?>
                        <p class="mt-2"><?= t('result.ticket_number') ?> <strong><?= $ticket_id ?></strong></p>
                        <?php endif; ?>
                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mt-4">
                            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/tickets.php"
                               class="btn btn-primary"><?= t('result.view_tickets') ?></a>
                            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/new.php"
                               class="btn btn-outline-secondary"><?= t('result.new_ticket') ?></a>
                        </div>
                    </div>
                </div>

                <?php else: ?>

                <div class="card border-danger">
                    <div class="card-body text-center py-5">
                        <div class="mb-3 text-danger" style="font-size:3rem;">&#10007;</div>
                        <h2 class="card-title"><?= t('result.error.title') ?></h2>
                        <p class="card-text mt-3"><?= t('result.error.lead') ?></p>
                        <?php if ($error_detail): ?>
                        <p class="mt-2"><code><?= $error_detail ?></code></p>
                        <?php endif; ?>
                        <div class="mt-4">
                            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/new.php"
                               class="btn btn-primary"><?= t('result.back') ?></a>
                        </div>
                    </div>
                </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/footer.php'; ?>
