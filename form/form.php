<?php
require __DIR__ . '/form-bootstrap.php';

// Calculate base path for assets (empty string if osTicket is at domain root).
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

// Pre-fill from the authenticated client; POST values take priority on re-render.
// Agents fill all contact fields manually (on behalf of a distributor).
$is_agent = (FORM_ROLE === 'agent');
if ($is_agent) {
    $prefill_name  = $_POST['name']         ?? (isset($thisstaff) ? $thisstaff->getName()  : '');
    $prefill_email = $_POST['email']        ?? (isset($thisstaff) ? $thisstaff->getEmail() : '');
    $prefill_org   = $_POST['organization'] ?? '';
} else {
    $prefill_name  = $_POST['name']         ?? (isset($thisclient) ? $thisclient->getName()                             : '');
    $prefill_email = $_POST['email']        ?? (isset($thisclient) ? $thisclient->getEmail()                            : '');
    $prefill_org   = $_POST['organization'] ?? (isset($thisclient) ? ($thisclient->getOrganization()?->getName() ?? '') : '');
}

// Fetch active public help topics (only available when osTicket is bootstrapped).
$help_topics = [];
if (defined('INCLUDE_DIR')) {
    require_once INCLUDE_DIR . 'class.topic.php';
    $help_topics = Topic::getPublicHelpTopics();
}

require __DIR__ . '/header.php';
?>
<main class="flex-grow-1">
    <div class="container py-5">

        <h1 class="mb-4"><?= t('form.header') ?></h1>

        <form method="post" action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/form/submit.php"
              enctype="multipart/form-data">
            <input type="hidden" name="role" value="<?= htmlspecialchars(FORM_ROLE, ENT_QUOTES, 'UTF-8') ?>">

            <h2 class="border-bottom pb-2 mt-2 mb-3"><?= t('section.motorcycle') ?></h2>

            <div class="mb-3">
                <label for="brand" class="form-label"><?= t('field.brand') ?></label>
                <select class="form-select" id="brand" name="brand" required>
                    <option value="Nipponia" <?= (($_POST['brand'] ?? '') !== 'PGO') ? 'selected' : '' ?>>Nipponia</option>
                    <option value="PGO" <?= (($_POST['brand'] ?? '') === 'PGO') ? 'selected' : '' ?>>PGO</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="vin" class="form-label"><?= t('field.vin') ?></label>
                <input type="text" class="form-control" id="vin" name="vin"
                       value="<?= htmlspecialchars($_POST['vin'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="model" class="form-label"><?= t('field.model') ?></label>
                <input type="text" class="form-control" id="model" name="model"
                       value="<?= htmlspecialchars($_POST['model'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="mb-3">
                <label for="color" class="form-label"><?= t('field.color') ?></label>
                <input type="text" class="form-control" id="color" name="color"
                       value="<?= htmlspecialchars($_POST['color'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="mb-3">
                <label for="order_no" class="form-label"><?= t('field.order_no') ?> - to be hidden and encrypted!</label>
                <input type="text" class="form-control" id="order_no" name="order_no"
                       value="<?= htmlspecialchars($_POST['order_no'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="mb-3">
                <label for="km" class="form-label"><?= t('field.km') ?></label>
                <input type="number" class="form-control" id="km" name="km"
                       value="<?= htmlspecialchars($_POST['km'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       min="0" step="1" required>
            </div>

            <h2 class="border-bottom pb-2 mt-4 mb-3"><?= t('section.distributor') ?></h2>

            <div class="mb-3<?= $is_agent ? ' d-none' : '' ?>">
                <label for="organization" class="form-label"><?= t('field.company') ?></label>
                <input type="text" class="form-control" id="organization" name="organization"
                       value="<?= htmlspecialchars($prefill_org, ENT_QUOTES, 'UTF-8') ?>"
                       readonly>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label"><?= t('field.name') ?></label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= htmlspecialchars($prefill_name, ENT_QUOTES, 'UTF-8') ?>"
                       required <?= $is_agent ? '' : 'readonly' ?>>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><?= t('field.email') ?></label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($prefill_email, ENT_QUOTES, 'UTF-8') ?>"
                       required <?= $is_agent ? '' : 'readonly' ?>>
            </div>

            <h2 class="border-bottom pb-2 mt-4 mb-3"><?= t('section.description') ?></h2>

            <div class="mb-3">
                <label for="topicId" class="form-label"><?= t('field.topic') ?></label>
                <select class="form-select" id="topicId" name="topicId" required>
                    <option value=""><?= t('field.topic_placeholder') ?></option>
                    <?php foreach ($help_topics as $id => $name): ?>
                        <option value="<?= (int) $id ?>"
                            <?= (isset($_POST['topicId']) && (int) $_POST['topicId'] === (int) $id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label"><?= t('field.description') ?></label>
                <textarea class="form-control" id="description" name="description"
                          rows="5" required><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="observations" class="form-label"><?= t('field.observations') ?></label>
                <textarea class="form-control" id="observations" name="observations"
                          rows="3"><?= htmlspecialchars($_POST['observations'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="parts_required" class="form-label"><?= t('field.parts_required') ?></label>
                <textarea class="form-control" id="parts_required" name="parts_required"
                          rows="3"><?= htmlspecialchars($_POST['parts_required'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="attachments" class="form-label"><?= t('field.attachments') ?></label>
                <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
            </div>

            <div class="d-grid mt-5">
                <button type="submit" class="btn btn-primary py-2">
                    <?= t('form.submit') ?>
                </button>
            </div>
        </form>

    </div>
</main>

<?php require __DIR__ . '/footer.php'; ?>
