<div class="admin-system-info">
    <div style="margin-bottom: var(--space-6);">
        <h1>System Information</h1>
        <p style="color: var(--color-text-tertiary); font-size: var(--font-size-sm); margin-top: var(--space-1);">Overview of your ManusClaw PHP installation and server environment.</p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <!-- Core System Info -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
        <!-- PHP & Server -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-header-title">Server Environment</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="font-weight: 600; width: 40%;">ManusClaw Version</td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($info['app_version'] ?? '1.0.0') ?></span></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">PHP Version</td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($info['php_version'] ?? PHP_VERSION) ?></span></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Server Software</td>
                            <td><?= htmlspecialchars($info['server_software'] ?? 'Unknown') ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Operating System</td>
                            <td><?= htmlspecialchars($info['php_os'] ?? PHP_OS_FAMILY) ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">PHP SAPI</td>
                            <td><?= htmlspecialchars($info['php_sapi'] ?? PHP_SAPI) ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Max Execution Time</td>
                            <td><?= htmlspecialchars($info['max_execution_time'] ?? 'N/A') ?>s</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Memory Limit</td>
                            <td><?= htmlspecialchars($info['memory_limit'] ?? 'N/A') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Database -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-header-title">Database</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table">
                    <tbody>
                        <tr>
                            <td style="font-weight: 600; width: 40%;">SQLite Version</td>
                            <td>
                                <?php
                                    try {
                                        $pdo = new PDO('sqlite::memory:');
                                        $sqliteVersion = $pdo->query('SELECT sqlite_version()')->fetchColumn();
                                        echo '<span class="badge badge-success">' . htmlspecialchars($sqliteVersion) . '</span>';
                                    } catch (Exception $e) {
                                        echo '<span class="badge badge-warning">N/A</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Database Size</td>
                            <td><?= htmlspecialchars($info['database_size'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Database Path</td>
                            <td style="font-family: var(--font-family-mono); font-size: var(--font-size-sm); word-break: break-all; white-space: normal;"><?= htmlspecialchars($info['database_path'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">PDO Drivers</td>
                            <td>
                                <?php foreach (($info['pdo_drivers'] ?? []) as $driver): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($driver) ?></span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Upload Max Filesize</td>
                            <td><?= htmlspecialchars($info['upload_max_filesize'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600;">Post Max Size</td>
                            <td><?= htmlspecialchars($info['post_max_size'] ?? 'N/A') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Storage & Disk -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
        <!-- Disk Space -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-header-title">Disk Space</h3>
            </div>
            <div class="card-body">
                <?php
                    $diskTotal = disk_total_space('/');
                    $diskFree = disk_free_space('/');
                    $diskUsed = $diskTotal - $diskFree;
                    $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;
                ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-3);">
                    <span style="color: var(--color-text-secondary); font-size: var(--font-size-sm);">Used Space</span>
                    <strong><?= round($diskUsed / 1024 / 1024 / 1024, 2) ?> GB / <?= round($diskTotal / 1024 / 1024 / 1024, 2) ?> GB</strong>
                </div>
                <div style="width: 100%; height: 12px; background: var(--color-gray-100); border-radius: var(--radius-full); overflow: hidden; margin-bottom: var(--space-3);">
                    <?php
                        $barColor = 'var(--color-success)';
                        if ($diskPercent > 80) $barColor = 'var(--color-danger)';
                        elseif ($diskPercent > 60) $barColor = 'var(--color-warning)';
                    ?>
                    <div style="width: <?= $diskPercent ?>%; height: 100%; background: <?= $barColor ?>; border-radius: var(--radius-full); transition: width var(--transition-base);"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm);">
                    <span style="color: var(--color-text-tertiary);"><?= $diskPercent ?>% used</span>
                    <span style="color: var(--color-text-tertiary);"><?= round($diskFree / 1024 / 1024 / 1024, 2) ?> GB free</span>
                </div>

                <hr>

                <h5 style="margin-bottom: var(--space-3);">Storage Directories</h5>
                <div style="display: grid; gap: var(--space-2);">
                    <?php
                        $storageDirs = [
                            'Sessions' => ['exists' => $info['storage_sessions_exists'] ?? 'No', 'writable' => $info['storage_sessions_writable'] ?? 'No', 'path' => $info['session_save_path'] ?? 'N/A'],
                            'Uploads'  => ['exists' => $info['storage_uploads_exists'] ?? 'No',  'writable' => $info['storage_uploads_writable'] ?? 'No',  'path' => $info['upload_path'] ?? 'N/A'],
                            'Logs'     => ['exists' => $info['storage_logs_exists'] ?? 'No',    'writable' => $info['storage_logs_writable'] ?? 'No',    'path' => $info['log_path'] ?? 'N/A'],
                        ];
                    ?>
                    <?php foreach ($storageDirs as $name => $dir): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-2) 0;">
                            <span style="font-size: var(--font-size-sm);"><?= htmlspecialchars($name) ?></span>
                            <div style="display: flex; gap: var(--space-2);">
                                <?php if ($dir['exists'] === 'Yes'): ?>
                                    <span class="badge badge-success">Exists</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Missing</span>
                                <?php endif; ?>
                                <?php if ($dir['writable'] === 'Yes'): ?>
                                    <span class="badge badge-success">Writable</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Not Writable</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Memory Usage -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-header-title">Memory Usage</h3>
            </div>
            <div class="card-body">
                <?php
                    $memUsed = memory_get_usage(true);
                    $memPeak = memory_get_peak_usage(true);
                    $memLimit = ini_get('memory_limit');
                    $memLimitBytes = 0;
                    if (preg_match('/^(\d+)(.)$/', $memLimit, $matches)) {
                        $value = (int) $matches[1];
                        $unit = $matches[2];
                        switch (strtoupper($unit)) {
                            case 'G': $memLimitBytes = $value * 1024 * 1024 * 1024; break;
                            case 'M': $memLimitBytes = $value * 1024 * 1024; break;
                            case 'K': $memLimitBytes = $value * 1024; break;
                            default:  $memLimitBytes = $value;
                        }
                    }
                    $memPercent = $memLimitBytes > 0 ? round(($memUsed / $memLimitBytes) * 100, 1) : 0;
                    $peakPercent = $memLimitBytes > 0 ? round(($memPeak / $memLimitBytes) * 100, 1) : 0;
                ?>

                <div style="margin-bottom: var(--space-5);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                        <span style="color: var(--color-text-secondary); font-size: var(--font-size-sm);">Current Usage</span>
                        <strong><?= round($memUsed / 1024 / 1024, 2) ?> MB</strong>
                    </div>
                    <div style="width: 100%; height: 12px; background: var(--color-gray-100); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="width: <?= min($memPercent, 100) ?>%; height: 100%; background: var(--color-primary); border-radius: var(--radius-full);"></div>
                    </div>
                    <div style="font-size: var(--font-size-sm); color: var(--color-text-tertiary); margin-top: var(--space-1);"><?= $memPercent ?>% of <?= htmlspecialchars($memLimit) ?></div>
                </div>

                <div style="margin-bottom: var(--space-5);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                        <span style="color: var(--color-text-secondary); font-size: var(--font-size-sm);">Peak Usage</span>
                        <strong><?= round($memPeak / 1024 / 1024, 2) ?> MB</strong>
                    </div>
                    <div style="width: 100%; height: 12px; background: var(--color-gray-100); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="width: <?= min($peakPercent, 100) ?>%; height: 100%; background: var(--color-warning); border-radius: var(--radius-full);"></div>
                    </div>
                    <div style="font-size: var(--font-size-sm); color: var(--color-text-tertiary); margin-top: var(--space-1);"><?= $peakPercent ?>% of <?= htmlspecialchars($memLimit) ?></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: var(--font-size-sm); padding: var(--space-3); background: var(--color-gray-50); border-radius: var(--radius-md);">
                    <span style="color: var(--color-text-secondary);">Memory Limit</span>
                    <strong><?= htmlspecialchars($memLimit) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- PHP Extensions -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <h3 class="card-header-title">PHP Extensions</h3>
            <span class="badge badge-neutral"><?= count($info['loaded_extensions'] ?? []) ?> loaded</span>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-wrap: wrap; gap: var(--space-2);">
                <?php
                    $importantExtensions = ['pdo', 'pdo_sqlite', 'sqlite3', 'json', 'mbstring', 'openssl', 'curl', 'session', 'fileinfo', 'tokenizer', 'xml', 'dom'];
                    $loaded = $info['loaded_extensions'] ?? [];
                    sort($loaded);
                ?>
                <?php foreach ($loaded as $ext): ?>
                    <?php $isImportant = in_array(strtolower($ext), $importantExtensions); ?>
                    <span class="badge <?= $isImportant ? 'badge-success' : 'badge-neutral' ?>"><?= htmlspecialchars($ext) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <h3 class="card-header-title">Active Sessions</h3>
        </div>
        <div class="card-body">
            <?php
                $sessionPath = session_save_path() ?: sys_get_temp_dir();
                $sessionFiles = glob($sessionPath . '/sess_*');
                $activeSessions = $sessionFiles !== false ? count($sessionFiles) : 0;
            ?>
            <div style="display: flex; align-items: center; gap: var(--space-4);">
                <div style="font-size: var(--font-size-3xl); font-weight: 700;"><?= $activeSessions ?></div>
                <div>
                    <div style="font-weight: 600;">Active Sessions</div>
                    <div style="font-size: var(--font-size-sm); color: var(--color-text-tertiary);">Session path: <?= htmlspecialchars($sessionPath) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-header-title">System Actions</h3>
        </div>
        <div class="card-body" style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
            <form method="POST" action="/admin/system/clear-cache" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-outline">&#128465; Clear Cache</button>
            </form>
            <form method="POST" action="/admin/system/optimize-db" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-outline">&#9881; Optimize Database</button>
            </form>
            <a href="/admin/system/export" class="btn btn-outline">&#128230; Export Data</a>
        </div>
    </div>
</div>
