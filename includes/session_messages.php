<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $type): ?>
    <?php if (isset($_SESSION[$key])): ?>
        <div class="alert alert-<?= $type ?> alert-dismissible fade show auto-dismiss-alert" role="alert">
            <?= htmlspecialchars($_SESSION[$key]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION[$key]); ?>
    <?php endif; ?>
<?php endforeach; ?>