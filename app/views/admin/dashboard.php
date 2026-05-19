<div class="admin-dashboard">
    <div class="content-header-title" style="margin-bottom: var(--space-6);">
        <h1>Admin Dashboard</h1>
        <span class="badge badge-success badge-dot">System Online</span>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
        <div class="card card-stats">
            <div class="card-body">
                <div class="card-stats-icon blue">&#128100;</div>
                <div class="card-stats-value"><?= htmlspecialchars((string) $totalUsers) ?></div>
                <div class="card-stats-label">Total Users</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body">
                <div class="card-stats-icon green">&#9989;</div>
                <div class="card-stats-value"><?= htmlspecialchars((string) $activeUsers) ?></div>
                <div class="card-stats-label">Active Users</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body">
                <div class="card-stats-icon purple">&#128203;</div>
                <div class="card-stats-value"><?= htmlspecialchars((string) ($taskStats['total'] ?? 0)) ?></div>
                <div class="card-stats-label">Total Tasks</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body">
                <div class="card-stats-icon orange">&#9881;</div>
                <div class="card-stats-value"><?= htmlspecialchars((string) ($taskStats['running'] ?? 0)) ?></div>
                <div class="card-stats-label">Running Tasks</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body">
                <div class="card-stats-icon red">&#127760;</div>
                <div class="card-stats-value"><?= htmlspecialchars((string) $totalProviders) ?></div>
                <div class="card-stats-label">Total Providers</div>
            </div>
        </div>
        <div class="card card-stats">
            <div class="card-body">
                <div class="card-stats-icon blue">&#128337;</div>
                <div class="card-stats-value" title="Since last restart"><?= htmlspecialchars((string) round(memory_get_usage() / 1024 / 1024, 1)) ?> MB</div>
                <div class="card-stats-label">Memory Usage</div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-header-title">Recent Activity</h3>
                <a href="/admin/logs" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (!empty($recentActivity)): ?>
                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentActivity, 0, 10) as $log): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($log['username'] ?? 'System') ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                                $actionLabel = htmlspecialchars($log['action']);
                                                $badgeClass = 'badge-neutral';
                                                if (str_contains($log['action'], 'login')) $badgeClass = 'badge-primary';
                                                elseif (str_contains($log['action'], 'task')) $badgeClass = 'badge-success';
                                                elseif (str_contains($log['action'], 'admin')) $badgeClass = 'badge-danger';
                                                elseif (str_contains($log['action'], 'error')) $badgeClass = 'badge-warning';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $actionLabel ?></span>
                                        </td>
                                        <td>
                                            <span class="text-small"><?= htmlspecialchars(date('M j, H:i', strtotime($log['created_at']))) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="padding: var(--space-6); text-align: center; color: var(--color-text-tertiary);">No recent activity found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Task Distribution -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-header-title">Task Distribution</h3>
                <a href="/admin/tasks" class="btn btn-sm btn-outline">View All Tasks</a>
            </div>
            <div class="card-body">
                <?php
                    $statuses = [
                        'pending'   => ['label' => 'Pending',   'class' => 'badge-pending',   'color' => 'var(--color-warning)'],
                        'running'   => ['label' => 'Running',   'class' => 'badge-running',   'color' => 'var(--color-primary)'],
                        'completed' => ['label' => 'Completed', 'class' => 'badge-completed', 'color' => 'var(--color-success)'],
                        'failed'    => ['label' => 'Failed',    'class' => 'badge-failed',    'color' => 'var(--color-danger)'],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge-neutral',   'color' => 'var(--color-text-tertiary)'],
                    ];
                    $maxCount = max(1, (int) ($taskStats['total'] ?? 1));
                ?>
                <?php foreach ($statuses as $key => $status): ?>
                    <?php $count = (int) ($taskStats[$key] ?? 0); ?>
                    <div style="margin-bottom: var(--space-4);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2);">
                            <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
                            <strong><?= $count ?></strong>
                        </div>
                        <div style="width: 100%; height: 8px; background: var(--color-gray-100); border-radius: var(--radius-full); overflow: hidden;">
                            <div style="width: <?= $maxCount > 0 ? round(($count / $maxCount) * 100) : 0 ?>%; height: 100%; background: <?= $status['color'] ?>; border-radius: var(--radius-full); transition: width var(--transition-base);"></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top: var(--space-5); padding-top: var(--space-4); border-top: 1px solid var(--color-border-light);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--color-text-secondary); font-size: var(--font-size-sm);">Total Tokens Used</span>
                        <strong><?= number_format((int) ($taskStats['total_tokens'] ?? 0)) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--space-2);">
                        <span style="color: var(--color-text-secondary); font-size: var(--font-size-sm);">Avg Execution Time</span>
                        <strong><?= htmlspecialchars((string) ($taskStats['avg_execution_time'] ?? 0)) ?>s</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Admin Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-header-title">Quick Actions</h3>
        </div>
        <div class="card-body" style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
            <a href="/admin/users" class="btn btn-primary">&#128100; Manage Users</a>
            <a href="/admin/tasks" class="btn btn-primary">&#128203; View All Tasks</a>
            <a href="/admin/settings" class="btn btn-outline">&#9881; System Settings</a>
            <a href="/admin/logs" class="btn btn-outline">&#128196; View Logs</a>
            <a href="/admin/providers" class="btn btn-outline">&#127760; Providers</a>
            <a href="/admin/system-info" class="btn btn-outline">&#128187; System Info</a>
        </div>
    </div>
</div>
