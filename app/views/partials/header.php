<?php
/**
 * Header Partial
 *
 * Variables:
 *   $pageTitle    string  Current page title
 *   $breadcrumbs  array   Array of ['label' => string, 'href' => string|null]
 *                          Last item is the current page (no href needed)
 *
 * Usage:
 *   <?php $pageTitle = 'My Page'; ?>
 *   <?php $breadcrumbs = [
 *       ['label' => 'Dashboard', 'href' => '/user/dashboard'],
 *       ['label' => 'Tasks', 'href' => '/user/tasks'],
 *       ['label' => 'Task #42', 'href' => null],
 *   ]; ?>
 *   <?php include __DIR__ . '/../partials/header.php'; ?>
 */
?>

<?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
<nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom: var(--space-4);">
    <ol style="display: flex; align-items: center; gap: var(--space-2); flex-wrap: wrap; list-style: none; margin: 0; padding: 0;">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php $isLast = ($i === count($breadcrumbs) - 1); ?>
            <li style="display: flex; align-items: center; gap: var(--space-2);">
                <?php if ($i > 0): ?>
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="flex-shrink: 0; color: var(--color-text-tertiary);">
                        <path d="M4.5 2.5L8 6L4.5 9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>

                <?php if ($isLast): ?>
                    <span style="font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); color: var(--color-text);">
                        <?= htmlspecialchars($crumb['label']) ?>
                    </span>
                <?php elseif (!empty($crumb['href'])): ?>
                    <a href="<?= htmlspecialchars($crumb['href']) ?>" style="font-size: var(--font-size-sm); color: var(--color-text-tertiary); text-decoration: none; transition: color 0.15s ease;">
                        <?= htmlspecialchars($crumb['label']) ?>
                    </a>
                <?php else: ?>
                    <span style="font-size: var(--font-size-sm); color: var(--color-text-tertiary);">
                        <?= htmlspecialchars($crumb['label']) ?>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>

<?php if (!empty($pageTitle)): ?>
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6); gap: var(--space-4); flex-wrap: wrap;">
    <div>
        <h2 style="margin: 0; font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-text); letter-spacing: -0.02em;">
            <?= htmlspecialchars($pageTitle) ?>
        </h2>
        <?php if (!empty($pageSubtitle)): ?>
            <p style="margin: var(--space-1) 0 0 0; font-size: var(--font-size-base); color: var(--color-text-secondary);">
                <?= htmlspecialchars($pageSubtitle) ?>
            </p>
        <?php endif; ?>
    </div>
    <?php if (!empty($pageActions)): ?>
        <div style="display: flex; align-items: center; gap: var(--space-3); flex-shrink: 0;">
            <?= $pageActions ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
