<div class="edit-provider-page">
    <!-- Flash Messages -->
    <?php if (isset($flash) && $flash): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div style="margin-bottom: var(--space-6);">
        <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-2);">
            <a href="/user/providers" class="btn btn-ghost btn-icon" title="Back to Providers">←</a>
            <h2 style="margin: 0;">Edit Provider</h2>
        </div>
        <p style="color: var(--color-text-tertiary); margin: 0;">Update your LLM provider configuration</p>
    </div>

    <!-- Provider Form -->
    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($formAction ?? '/user/providers/update/' . ($provider['id'] ?? '')); ?>" id="provider-form">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                <input type="hidden" name="provider_id" value="<?php echo (int)($provider['id'] ?? 0); ?>">

                <!-- Provider Type -->
                <div class="form-group">
                    <label class="form-label" for="provider_type">Provider Type</label>
                    <select name="provider_type" id="provider_type" class="form-select" required>
                        <option value="">Select a provider type...</option>
                        <?php foreach ($providerTypes as $key => $type): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>" <?php echo (!empty($provider) && ($provider['provider_type'] ?? '') === $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint" id="provider-type-hint"><?php echo htmlspecialchars($providerTypes[$provider['provider_type'] ?? '']['description'] ?? ''); ?></div>
                </div>

                <!-- Provider Name -->
                <div class="form-group">
                    <label class="form-label" for="name">Provider Name</label>
                    <input type="text" name="name" id="name" class="form-input" placeholder="e.g., My GPT-4o" value="<?php echo htmlspecialchars($provider['name'] ?? ''); ?>" required>
                    <div class="form-hint">A friendly name to identify this provider</div>
                </div>

                <!-- API Key -->
                <div class="form-group" id="api-key-group">
                    <label class="form-label" for="api_key" id="api-key-label">API Key</label>
                    <div style="position: relative;">
                        <input type="password" name="api_key" id="api_key" class="form-input" style="padding-right: 3rem;" placeholder="Leave blank to keep current key" autocomplete="new-password">
                        <button type="button" class="btn btn-ghost btn-icon btn-sm" style="position: absolute; right: 4px; top: 50%; transform: translateY(-50%);" onclick="toggleApiKeyVisibility()" id="api-key-toggle" title="Show/Hide API Key">
                            👁️
                        </button>
                    </div>
                    <div class="form-hint" id="api-key-hint">Leave blank to keep the existing API key. Your key is stored securely.</div>
                </div>

                <!-- Base URL -->
                <div class="form-group">
                    <label class="form-label" for="base_url">Base URL</label>
                    <input type="text" name="base_url" id="base_url" class="form-input" placeholder="https://api.example.com/v1" value="<?php echo htmlspecialchars($provider['base_url'] ?? ''); ?>">
                    <div class="form-hint" id="base-url-hint">Auto-filled based on provider type. Edit if using a custom endpoint.</div>
                </div>

                <!-- Model Name -->
                <div class="form-group">
                    <label class="form-label" for="model_name">Model Name</label>
                    <input type="text" name="model_name" id="model_name" class="form-input" placeholder="gpt-4o" value="<?php echo htmlspecialchars($provider['model_name'] ?? ''); ?>">
                    <div class="form-hint" id="model-hint">The model identifier used in API requests</div>
                </div>

                <!-- Max Tokens & Timeout Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-5);">
                    <!-- Max Tokens -->
                    <div class="form-group">
                        <label class="form-label" for="max_tokens">Max Tokens</label>
                        <input type="number" name="max_tokens" id="max_tokens" class="form-input" min="1" max="100000" value="<?php echo htmlspecialchars($provider['max_tokens'] ?? '4096'); ?>">
                    </div>

                    <!-- Timeout -->
                    <div class="form-group">
                        <label class="form-label" for="timeout">Timeout (seconds)</label>
                        <input type="number" name="timeout" id="timeout" class="form-input" min="1" max="3600" value="<?php echo htmlspecialchars($provider['timeout'] ?? '300'); ?>">
                    </div>
                </div>

                <!-- Temperature Slider -->
                <div class="form-group">
                    <label class="form-label">Temperature: <span id="temp-value"><?php echo htmlspecialchars($provider['temperature'] ?? '0.7'); ?></span></label>
                    <input type="range" name="temperature" id="temperature" min="0" max="2" step="0.1" value="<?php echo htmlspecialchars($provider['temperature'] ?? '0.7'); ?>" style="width: 100%; accent-color: var(--color-primary);">
                    <div style="display: flex; justify-content: space-between; font-size: var(--font-size-xs); color: var(--color-text-tertiary);">
                        <span>Precise (0)</span>
                        <span>Balanced (1)</span>
                        <span>Creative (2)</span>
                    </div>
                </div>

                <!-- Set as Default -->
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_default" class="form-check-input" value="1" <?php echo !empty($provider['is_default']) ? 'checked' : ''; ?>>
                        <span>Set as default provider</span>
                    </label>
                </div>

                <!-- Submit -->
                <div style="display: flex; gap: var(--space-3); padding-top: var(--space-4);">
                    <button type="submit" class="btn btn-primary btn-lg">Save Provider</button>
                    <a href="/user/providers" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var providerData = {
        openai: {
            name: 'OpenAI',
            emoji: '🤖',
            baseUrl: 'https://api.openai.com/v1',
            modelPlaceholder: 'gpt-4o',
            requiresApiKey: true,
            hint: 'GPT-4, GPT-3.5 Turbo and other OpenAI models'
        },
        anthropic: {
            name: 'Anthropic',
            emoji: '🧠',
            baseUrl: 'https://api.anthropic.com/v1',
            modelPlaceholder: 'claude-3-5-sonnet-20241022',
            requiresApiKey: true,
            hint: 'Claude 3.5, Claude 3 and other Anthropic models'
        },
        google: {
            name: 'Google AI',
            emoji: '🔮',
            baseUrl: 'https://generativelanguage.googleapis.com/v1',
            modelPlaceholder: 'gemini-pro',
            requiresApiKey: true,
            hint: 'Gemini Pro, Gemini Ultra and other Google models'
        },
        huggingface: {
            name: 'Hugging Face',
            emoji: '🤗',
            baseUrl: 'https://api-inference.huggingface.co',
            modelPlaceholder: 'meta-llama/Llama-2-70b-chat-hf',
            requiresApiKey: true,
            hint: 'Open-source models via Hugging Face Inference API'
        },
        ollama: {
            name: 'Ollama',
            emoji: '🦙',
            baseUrl: 'http://localhost:11434',
            modelPlaceholder: 'llama2',
            requiresApiKey: false,
            hint: 'Local LLM models via Ollama'
        },
        lmstudio: {
            name: 'LM Studio',
            emoji: '💻',
            baseUrl: 'http://localhost:1234',
            modelPlaceholder: 'default',
            requiresApiKey: false,
            hint: 'Local LLM models via LM Studio'
        },
        openrouter: {
            name: 'OpenRouter',
            emoji: '🌐',
            baseUrl: 'https://openrouter.ai/api/v1',
            modelPlaceholder: 'openai/gpt-3.5-turbo',
            requiresApiKey: true,
            hint: 'Unified API for multiple LLM providers'
        },
        universal: {
            name: 'Universal / Custom',
            emoji: '⚡',
            baseUrl: '',
            modelPlaceholder: 'model-name',
            requiresApiKey: true,
            hint: 'Any OpenAI-compatible API endpoint'
        }
    };

    var currentType = <?php echo json_encode($provider['provider_type'] ?? ''); ?>;
    var typeSelect = document.getElementById('provider_type');
    var baseUrlInput = document.getElementById('base_url');
    var modelInput = document.getElementById('model_name');
    var apiKeyGroup = document.getElementById('api-key-group');
    var apiKeyLabel = document.getElementById('api-key-label');
    var apiKeyHint = document.getElementById('api-key-hint');
    var apiKeyInput = document.getElementById('api_key');
    var typeHint = document.getElementById('provider-type-hint');

    // Store original base URL to detect user changes
    var originalBaseUrl = baseUrlInput.value;

    function onTypeChange() {
        var selected = typeSelect.value;
        if (selected && providerData[selected]) {
            var data = providerData[selected];

            typeHint.textContent = data.hint;

            // Only auto-fill base URL if it hasn't been modified from a previous provider type
            if (!originalBaseUrl || isDefaultBaseUrl(originalBaseUrl) || baseUrlInput.value === originalBaseUrl) {
                baseUrlInput.value = data.baseUrl;
                originalBaseUrl = data.baseUrl;
            }

            modelInput.placeholder = data.modelPlaceholder;

            if (data.requiresApiKey) {
                apiKeyLabel.textContent = 'API Key';
                apiKeyHint.textContent = 'Leave blank to keep the existing API key. Your key is stored securely.';
                apiKeyInput.required = false;
                apiKeyInput.placeholder = 'Leave blank to keep current key';
            } else {
                apiKeyLabel.textContent = 'API Key (Not needed for local)';
                apiKeyHint.textContent = 'Local providers like ' + data.name + ' do not require an API key';
                apiKeyInput.required = false;
                apiKeyInput.placeholder = 'Not required for ' + data.name;
            }
        }
    }

    function isDefaultBaseUrl(url) {
        for (var key in providerData) {
            if (providerData[key].baseUrl === url) return true;
        }
        return false;
    }

    typeSelect.addEventListener('change', onTypeChange);

    // Initialize for edit mode
    if (currentType && providerData[currentType]) {
        modelInput.placeholder = providerData[currentType].modelPlaceholder;
        if (!providerData[currentType].requiresApiKey) {
            apiKeyLabel.textContent = 'API Key (Not needed for local)';
            apiKeyHint.textContent = 'Local providers like ' + providerData[currentType].name + ' do not require an API key';
            apiKeyInput.placeholder = 'Not required for ' + providerData[currentType].name;
        }
    }

    // Temperature slider
    var tempSlider = document.getElementById('temperature');
    var tempValue = document.getElementById('temp-value');
    tempSlider.addEventListener('input', function() {
        tempValue.textContent = this.value;
    });
})();

function toggleApiKeyVisibility() {
    var input = document.getElementById('api_key');
    var btn = document.getElementById('api-key-toggle');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🔒';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>
