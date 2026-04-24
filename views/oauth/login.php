<div class="centered-container">
    <h1 style="text-align: center; margin-bottom: var(--spacing-md);"><?= $t->trans('REGISTER_TITLE') ?></h1>

    <?php if (isset($success) && !empty($success)): ?>
        <div style="background: #000; color: #fff; padding: 10px; margin-bottom: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; text-align: center;">
            <?= \htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && !empty($error)): ?>
        <div style="background: #000; color: #fff; padding: 10px; margin-bottom: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; text-align: center;">
            <?= \htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="/register" method="POST" class="minimal-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="text" name="username" placeholder="<?= $t->trans('USERNAME') ?>" class="input-field" required>
        <input type="email" name="email" placeholder="<?= $t->trans('EMAIL') ?>" class="input-field" required>
        <input type="password" name="password" placeholder="<?= $t->trans('PASSWORD') ?>" class="input-field" required>
        <button type="submit"><?= $t->trans('SUBMIT_REGISTER') ?></button>
    </form>
</div>