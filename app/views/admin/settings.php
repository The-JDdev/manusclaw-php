<div class="admin-settings">
    <div style="margin-bottom: var(--space-6);">
        <h1>System Settings</h1>
        <p style="color: var(--color-text-tertiary); font-size: var(--font-size-sm); margin-top: var(--space-1);">Configure your ManusClaw PHP instance. Changes take effect immediately.</p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="/admin/settings/save" id="settingsForm">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <!-- General Settings -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <div>
                    <h3 class="card-header-title">General Settings</h3>
                    <p class="card-header-subtitle">Core application configuration</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="app_name">App Name</label>
                    <input type="text" id="app_name" name="app_name" class="form-input"
                           value="<?= htmlspecialchars($settings['app_name']['value'] ?? 'ManusClaw PHP') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="app_description">App Description</label>
                    <textarea id="app_description" name="app_description" class="form-textarea" rows="3"
                              placeholder="Brief description of your instance"><?= htmlspecialchars($settings['app_description']['value'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label" for="task_timeout">Default LLM Timeout (seconds)</label>
                        <input type="number" id="task_timeout" name="task_timeout" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['task_timeout']['value'] ?? 300)) ?>"
                               min="10" max="3600">
                        <span class="form-hint">Maximum time a task can run before timing out.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_concurrent_tasks">Max Concurrent Tasks</label>
                        <input type="number" id="max_concurrent_tasks" name="max_concurrent_tasks" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['max_concurrent_tasks']['value'] ?? 5)) ?>"
                               min="1" max="50">
                        <span class="form-hint">Maximum number of tasks running simultaneously.</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label">Registration Enabled</label>
                        <label class="form-check">
                            <input type="checkbox" name="allow_registration" value="1"
                                   class="form-check-input"
                                   <?= !empty($settings['allow_registration']['value']) ? 'checked' : '' ?>>
                            <span>Allow new user registrations</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Maintenance Mode</label>
                        <label class="form-check">
                            <input type="checkbox" name="maintenance_mode" value="1"
                                   class="form-check-input"
                                   <?= !empty($settings['maintenance_mode']['value']) ? 'checked' : '' ?>>
                            <span>Enable maintenance mode</span>
                        </label>
                        <span class="form-hint">Only admins can access the system when enabled.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <div>
                    <h3 class="card-header-title">Security Settings</h3>
                    <p class="card-header-subtitle">Authentication and access control</p>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label" for="session_lifetime">Session Lifetime (minutes)</label>
                        <input type="number" id="session_lifetime" name="session_lifetime" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['session_lifetime']['value'] ?? 120)) ?>"
                               min="5" max="10080">
                        <span class="form-hint">How long a user session stays active.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rate_limit">Rate Limiting (requests/minute)</label>
                        <input type="number" id="rate_limit" name="rate_limit" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['rate_limit']['value'] ?? 60)) ?>"
                               min="1" max="1000">
                        <span class="form-hint">Maximum requests per minute per IP address.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">CSRF Protection</label>
                    <label class="form-check">
                        <input type="checkbox" checked disabled class="form-check-input">
                        <span>CSRF protection is always enabled</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ip_whitelist">IP Whitelist</label>
                    <textarea id="ip_whitelist" name="ip_whitelist" class="form-textarea" rows="4"
                              placeholder="One IP address per line. Leave empty to allow all."><?= htmlspecialchars($settings['ip_whitelist']['value'] ?? '') ?></textarea>
                    <span class="form-hint">One IP address per line. Leave empty to allow all IPs.</span>
                </div>
            </div>
        </div>

        <!-- API Settings -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <div>
                    <h3 class="card-header-title">API Settings</h3>
                    <p class="card-header-subtitle">ManusClaw Python backend and webhook configuration</p>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="manusclaw_backend_url">ManusClaw Python Backend URL</label>
                    <input type="text" id="manusclaw_backend_url" name="manusclaw_backend_url" class="form-input"
                           value="<?= htmlspecialchars($settings['manusclaw_backend_url']['value'] ?? 'http://localhost:8000') ?>"
                           placeholder="http://localhost:8000">
                    <span class="form-hint">URL of the ManusClaw Python backend service.</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label" for="api_rate_limit">API Rate Limit (requests/minute)</label>
                        <input type="number" id="api_rate_limit" name="api_rate_limit" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['api_rate_limit']['value'] ?? 100)) ?>"
                               min="1" max="10000">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_tokens_default">Default Max Tokens</label>
                        <input type="number" id="max_tokens_default" name="max_tokens_default" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['max_tokens_default']['value'] ?? 4096)) ?>"
                               min="1" max="200000">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label" for="webhook_url">Webhook URL</label>
                        <input type="text" id="webhook_url" name="webhook_url" class="form-input"
                               value="<?= htmlspecialchars($settings['webhook_url']['value'] ?? '') ?>"
                               placeholder="https://example.com/webhook">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="webhook_secret">Webhook Secret</label>
                        <input type="password" id="webhook_secret" name="webhook_secret" class="form-input"
                               value="<?= htmlspecialchars($settings['webhook_secret']['value'] ?? '') ?>"
                               placeholder="Leave blank to keep current">
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <div>
                    <h3 class="card-header-title">Email Settings</h3>
                    <p class="card-header-subtitle">SMTP configuration for system emails</p>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label" for="smtp_host">SMTP Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" class="form-input"
                               value="<?= htmlspecialchars($settings['smtp_host']['value'] ?? '') ?>"
                               placeholder="smtp.example.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="smtp_port">SMTP Port</label>
                        <input type="number" id="smtp_port" name="smtp_port" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['smtp_port']['value'] ?? 587)) ?>"
                               min="1" max="65535">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label" for="smtp_username">SMTP Username</label>
                        <input type="text" id="smtp_username" name="smtp_username" class="form-input"
                               value="<?= htmlspecialchars($settings['smtp_username']['value'] ?? '') ?>"
                               placeholder="user@example.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="smtp_password">SMTP Password</label>
                        <input type="password" id="smtp_password" name="smtp_password" class="form-input"
                               value="<?= htmlspecialchars($settings['smtp_password']['value'] ?? '') ?>"
                               placeholder="Leave blank to keep current">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="smtp_from_address">From Address</label>
                    <input type="email" id="smtp_from_address" name="smtp_from_address" class="form-input"
                           value="<?= htmlspecialchars($settings['smtp_from_address']['value'] ?? '') ?>"
                           placeholder="noreply@example.com">
                </div>
            </div>
        </div>

        <!-- Storage Settings -->
        <div class="card" style="margin-bottom: var(--space-6);">
            <div class="card-header">
                <div>
                    <h3 class="card-header-title">Storage Settings</h3>
                    <p class="card-header-subtitle">File upload and cleanup configuration</p>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                    <div class="form-group">
                        <label class="form-label" for="max_upload_size">Max Upload Size (MB)</label>
                        <input type="number" id="max_upload_size" name="max_upload_size" class="form-input"
                               value="<?= htmlspecialchars((string) ($settings['max_upload_size']['value'] ?? 10)) ?>"
                               min="1" max="500">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="allowed_file_types">Allowed File Types</label>
                        <input type="text" id="allowed_file_types" name="allowed_file_types" class="form-input"
                               value="<?= htmlspecialchars($settings['allowed_file_types']['value'] ?? 'txt,pdf,doc,docx,csv,json,md') ?>"
                               placeholder="txt,pdf,doc,csv,json">
                        <span class="form-hint">Comma-separated list of allowed file extensions.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Auto Cleanup</label>
                    <div style="display: flex; align-items: center; gap: var(--space-4);">
                        <label class="form-check">
                            <input type="checkbox" name="auto_cleanup" value="1"
                                   class="form-check-input"
                                   <?= !empty($settings['auto_cleanup']['value']) ? 'checked' : '' ?>>
                            <span>Enable automatic cleanup</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: var(--space-2);">
                            <span style="font-size: var(--font-size-sm); color: var(--color-text-secondary);">After</span>
                            <input type="number" name="cleanup_days" class="form-input" style="width: 80px;"
                                   value="<?= htmlspecialchars((string) ($settings['cleanup_days']['value'] ?? 30)) ?>"
                                   min="1" max="365">
                            <span style="font-size: var(--font-size-sm); color: var(--color-text-secondary);">days</span>
                        </div>
                    </div>
                    <span class="form-hint">Automatically delete old task outputs and temporary files.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="log_retention_days">Log Retention (days)</label>
                    <input type="number" id="log_retention_days" name="log_retention_days" class="form-input" style="max-width: 200px;"
                           value="<?= htmlspecialchars((string) ($settings['log_retention_days']['value'] ?? 90)) ?>"
                           min="1" max="3650">
                    <span class="form-hint">Activity logs older than this will be automatically deleted.</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-danger" id="resetSettingsBtn">Reset to Defaults</button>
            <div style="display: flex; gap: var(--space-3);">
                <a href="/admin/settings" class="btn btn-secondary">Discard Changes</a>
                <button type="submit" class="btn btn-primary">Save All Settings</button>
            </div>
        </div>
    </form>
</div>

<!-- Reset to Defaults Confirmation Modal -->
<div class="modal-overlay" id="resetSettingsModal">
    <div class="modal-dialog modal-sm">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--color-border-light);">
            <h3 style="font-size: var(--font-size-lg); color: var(--color-danger);">Reset Settings</h3>
        </div>
        <div style="padding: var(--space-6);">
            <p>Are you sure you want to reset all settings to their default values? This cannot be undone.</p>
        </div>
        <div style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--color-border-light); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="closeResetModal()">Cancel</button>
            <form method="POST" action="/admin/settings/reset" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">Yes, Reset All</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    document.getElementById('resetSettingsBtn').addEventListener('click', function() {
        document.getElementById('resetSettingsModal').classList.add('active');
    });

    window.closeResetModal = function() {
        document.getElementById('resetSettingsModal').classList.remove('active');
    };

    document.getElementById('resetSettingsModal').addEventListener('click', function(e) {
        if (e.target === this) closeResetModal();
    });
})();
</script>
