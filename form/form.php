<?php
require __DIR__ . '/form-bootstrap.php';

// Calculate base path for assets (empty string if osTicket is at domain root).
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

// Pre-fill from the authenticated client; POST values take priority on re-render.
$prefill_name  = $_POST['name']  ?? (isset($thisclient) ? $thisclient->getName()  : '');
$prefill_email = $_POST['email'] ?? (isset($thisclient) ? $thisclient->getEmail() : '');

require __DIR__ . '/header.php';
?>
<main class="flex-grow-1">
    <div class="container py-5">

        <h1 class="mb-4"><?= t('form.header') ?></h1>

        <form method="post" action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/form/submit.php"
              enctype="multipart/form-data">

            <div class="mb-3">
                <label for="name" class="form-label"><?= t('field.name') ?></label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= htmlspecialchars($prefill_name, ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><?= t('field.email') ?></label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($prefill_email, ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label"><?= t('field.subject') ?></label>
                <input type="text" class="form-control" id="subject" name="subject"
                       value="<?= htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="vin" class="form-label"><?= t('field.vin') ?></label>
                <input type="text" class="form-control" id="vin" name="vin"
                       value="<?= htmlspecialchars($_POST['vin'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="message" class="form-label"><?= t('field.message') ?></label>
                <textarea class="form-control" id="message" name="message"
                          rows="5" required><?= htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="attachments" class="form-label"><?= t('field.attachments') ?></label>
                <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">
                    <?= t('form.submit') ?>
                </button>
            </div>
        </form>

    </div>
</main>

<?php require __DIR__ . '/footer.php'; ?>
