<div class="admin-providers">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-6);">
        <h1>All Provider Configurations</h1>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'))) ?>">
            <div class="alert-content"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom: var(--space-4);">
        <div class="card-body" style="padding: var(--space-4) var(--space-6);">
            <form method="GET" action="/admin/providers" style="display: flex; gap: var(--space-3); align-items: center;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <input type="text" name="search" class="form-input"
                           placeholder="Search providers by name, user, or model..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                    <select name="provider_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="openai" <?= ($_GET['provider_type'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                        <option value="anthropic" <?= ($_GET['provider_type'] ?? '') === 'anthropic' ? 'selected' : '' ?>>Anthropic</option>
                        <option value="google" <?= ($_GET['provider_type'] ?? '') === 'google' ? 'selected' : '' ?>>Google AI</option>
                        <option value="huggingface" <?= ($_GET['provider_type'] ?? '') === 'huggingface' ? 'selected' : '' ?>>Hugging Face</option>
                        <option value="ollama" <?= ($_GET['provider_type'] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama</option>
                        <option value="lmstudio" <?= ($_GET['provider_type'] ?? '') === 'lmstudio' ? 'selected' : '' ?>>LM Studio</option>
                        <option value="openrouter" <?= ($_GET['provider_type'] ?? '') === 'openrouter' ? 'selected' : '' ?>>OpenRouter</option>
                        <option value="universal" <?= ($_GET['provider_type'] ?? '') === 'universal' ? 'selected' : '' ?>>Universal</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="/admin/providers" class="btn btn-secondary">Clear</a>
            </form>
        </div>
    </div>

    <!-- Providers Table -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Model</th>
                    <th>Base URL</th>
                    <th>Active</th>
                    <th>Default</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($providers)): ?>
                    <?php foreach ($providers as $provider): ?>
                        <tr data-provider-id="<?= (int) $provider['id'] ?>">
                            <td>
                                <span style="font-family: var(--font-family-mono); font-size: var(--font-size-sm);">#<?= (int) $provider['id'] ?></span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($provider['username'] ?? 'Unknown') ?></strong>
                            </td>
                            <td>
                                <?php
                                    $typeBadgeMap = [
                                        'openai'     => 'badge-primary',
                                        'anthropic'  => 'badge-warning',
                                        'google'     => 'badge-info',
                                        'huggingface' => 'badge-neutral',
                                        'ollama'     => 'badge-success',
                                        'lmstudio'   => 'badge-success',
                                        'openrouter' => 'badge-secondary',
                                        'universal'  => 'badge-neutral',
                                    ];
                                    $typeBadge = $typeBadgeMap[$provider['provider_type']] ?? 'badge-neutral';
                                ?>
                                <span class="badge <?= $typeBadge ?>"><?= htmlspecialchars(ucfirst($provider['provider_type'])) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($provider['name']) ?>
                            </td>
                            <td>
                                <span style="font-family: var(--font-family-mono); font-size: var(--font-size-sm);"><?= htmlspecialchars($provider['model_name'] ?? 'Default') ?></span>
                            </td>
                            <td>
                                <span class="text-small" style="max-width: 180px; display: inline-block; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($provider['base_url'] ?? '') ?>">
                                    <?= htmlspecialchars($provider['base_url'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($provider['is_active'])): ?>
                                    <span class="badge badge-success badge-dot">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger badge-dot">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($provider['is_default'])): ?>
                                    <span class="badge badge-primary">Default</span>
                                <?php else: ?>
                                    <span style="color: var(--color-text-tertiary); font-size: var(--font-size-sm);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-small"><?= htmlspecialchars(date('M j, Y', strtotime($provider['created_at']))) ?></span>
                            </td>
                            <td>
                                <div style="display: flex; gap: var(--space-2);">
                                    <a href="/user/providers/edit/<?= (int) $provider['id'] ?>" class="btn btn-sm btn-outline">View</a>
                                    <button type="button" class="btn btn-sm btn-secondary toggle-provider-btn"
                                            data-provider-id="<?= (int) $provider['id'] ?>"
                                            data-current-status="<?= !empty($provider['is_active']) ? '1' : '0' ?>"
                                            title="<?= !empty($provider['is_active']) ? 'Deactivate' : 'Activate' ?>">
                                        <?= !empty($provider['is_active']) ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-provider-btn"
                                            data-provider-id="<?= (int) $provider['id'] ?>"
                                            data-provider-name="<?= htmlspecialchars($provider['name']) ?>">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: var(--space-8); color: var(--color-text-tertiary);">
                            No provider configurations found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Provider Summary -->
    <?php if (!empty($providers)): ?>
        <div style="margin-top: var(--space-4); display: flex; gap: var(--space-3); flex-wrap: wrap;">
            <?php
                $typeCounts = [];
                foreach ($providers as $p) {
                    $t = $p['provider_type'] ?? 'unknown';
                    $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
                }
            ?>
            <?php foreach ($typeCounts as $type => $count): ?>
                <span class="badge <?= $typeBadgeMap[$type] ?? 'badge-neutral' ?>"><?= htmlspecialchars(ucfirst($type)) ?>: <?= (int) $count ?></span>
            <?php endforeach; ?>
            <span class="badge badge-neutral">Total: <?= count($providers) ?></span>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Provider Confirmation Modal -->
<div class="modal-overlay" id="deleteProviderModal">
    <div class="modal-dialog modal-sm">
        <div style="padding: var(--space-5) var(--space-6); border-bottom: 1px solid var(--color-border-light);">
            <h3 style="font-size: var(--font-size-lg); color: var(--color-danger);">Delete Provider</h3>
        </div>
        <div style="padding: var(--space-6);">
            <p>Are you sure you want to delete provider <strong id="deleteProviderName"></strong>? Tasks using this provider may fail.</p>
        </div>
        <div style="padding: var(--space-4) var(--space-6); border-top: 1px solid var(--color-border-light); display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" action="" id="deleteProviderForm" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">Delete Provider</button>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Toggle provider active status
    document.querySelectorAll('.toggle-provider-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var providerId = this.dataset.providerId;
            var currentStatus = this.dataset.currentStatus;
            var action = currentStatus === '1' ? 'deactivate' : 'activate';

            if (!confirm('Are you sure you want to ' + action + ' this provider?')) {
                return;
            }

            fetch('/admin/providers/toggle-active/' + providerId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?= htmlspecialchars($csrfToken) ?>',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to update provider status.');
                }
            })
            .catch(function() {
                alert('An error occurred. Please try again.');
            });
        });
    });

    // Delete provider
    document.querySelectorAll('.delete-provider-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('deleteProviderName').textContent = this.dataset.providerName;
            document.getElementById('deleteProviderForm').action = '/admin/providers/delete/' + this.dataset.providerId;
            document.getElementById('deleteProviderModal').classList.add('active');
        });
    });

    window.closeDeleteModal = function() {
        document.getElementById('deleteProviderModal').classList.remove('active');
    };

    document.getElementById('deleteProviderModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
})();
</script>
