<?php
/**
 * Task Status Badge Partial
 *
 * Variables:
 *   $status  string  Task status: pending, running, completed, failed, cancelled
 *
 * Usage:
 *   <?php $status = 'running'; ?>
 *   <?php include __DIR__ . '/../partials/task-status-badge.php'; ?>
 *
 * Renders an Apple-style status badge with:
 *   - pending:   yellow badge
 *   - running:   blue badge with pulse animation
 *   - completed: green badge
 *   - failed:    red badge
 *   - cancelled: gray badge
 */

$status = $status ?? 'pending';

$badgeConfig = [
    'pending'   => ['class' => 'badge-warning',  'label' => 'Pending',   'pulse' => false],
    'running'   => ['class' => 'badge-primary',  'label' => 'Running',   'pulse' => true],
    'completed' => ['class' => 'badge-success',  'label' => 'Completed', 'pulse' => false],
    'failed'    => ['class' => 'badge-danger',   'label' => 'Failed',    'pulse' => false],
    'cancelled' => ['class' => 'badge-neutral',  'label' => 'Cancelled', 'pulse' => false],
];

$config = $badgeConfig[$status] ?? $badgeConfig['pending'];
$badgeClass = 'badge badge-dot ' . $config['class'];
$pulseStyle = $config['pulse'] ? 'animation: pulse-dot 1.5s ease-in-out infinite;' : '';
?>

<span class="<?= $badgeClass ?>" style="<?= $pulseStyle ?>">
    <?= htmlspecialchars($config['label']) ?>
</span>
