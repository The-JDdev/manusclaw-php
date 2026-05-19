<?php

class LLMProvider
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByUserId(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM llm_providers WHERE user_id = ? ORDER BY is_default DESC, name ASC',
            [$userId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM llm_providers WHERE id = ?', [$id]);
    }

    public function findDefault(int $userId): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM llm_providers WHERE user_id = ? AND is_default = 1 AND is_active = 1',
            [$userId]
        );
    }

    public function create(array $data): int
    {
        $fields = [
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'provider_type' => $data['provider_type'],
            'api_key' => $data['api_key'] ?? null,
            'base_url' => $data['base_url'] ?? null,
            'model_name' => $data['model_name'] ?? null,
            'is_default' => $data['is_default'] ?? 0,
            'max_tokens' => $data['max_tokens'] ?? 4096,
            'temperature' => $data['temperature'] ?? 0.7,
            'timeout' => $data['timeout'] ?? 300,
            'is_active' => $data['is_active'] ?? 1,
        ];

        // If this is set as default, unset other defaults for this user
        if ($fields['is_default']) {
            $this->db->update('llm_providers', ['is_default' => 0], 'user_id = ?', [$data['user_id']]);
        }

        return $this->db->insert('llm_providers', $fields);
    }

    public function update(int $id, array $data): int
    {
        $allowed = ['name', 'provider_type', 'api_key', 'base_url', 'model_name', 'is_default', 'max_tokens', 'temperature', 'timeout', 'is_active'];
        $fields = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[$key] = $data[$key];
            }
        }

        if (empty($fields)) {
            return 0;
        }

        // If setting as default, unset other defaults for this user
        if (isset($fields['is_default']) && $fields['is_default']) {
            $provider = $this->findById($id);
            if ($provider) {
                $this->db->update('llm_providers', ['is_default' => 0], 'user_id = ? AND id != ?', [$provider['user_id'], $id]);
            }
        }

        $fields['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('llm_providers', $fields, 'id = ?', [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete('llm_providers', 'id = ?', [$id]);
    }

    public function setDefault(int $id, int $userId): bool
    {
        $provider = $this->findById($id);
        if (!$provider || $provider['user_id'] != $userId) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $this->db->update('llm_providers', ['is_default' => 0, 'updated_at' => date('Y-m-d H:i:s')], 'user_id = ?', [$userId]);
            $this->db->update('llm_providers', ['is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function toggleActive(int $id): bool
    {
        $provider = $this->findById($id);
        if (!$provider) {
            return false;
        }

        $newStatus = $provider['is_active'] ? 0 : 1;
        $affected = $this->db->update('llm_providers', [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        return $affected > 0;
    }

    public function testConnection(int $id): array
    {
        $provider = $this->findById($id);
        if (!$provider) {
            return ['success' => false, 'error' => 'Provider not found'];
        }

        if (!$provider['is_active']) {
            return ['success' => false, 'error' => 'Provider is inactive'];
        }

        $startTime = microtime(true);

        try {
            $messages = [
                ['role' => 'user', 'content' => 'Say "Connection successful!" and nothing else.']
            ];

            $bridge = new ManusClawBridge($provider);
            $result = $bridge->callLLM($provider, $messages, ['max_tokens' => 50]);

            $elapsed = round(microtime(true) - $startTime, 2);

            if (isset($result['error'])) {
                return ['success' => false, 'error' => $result['error'], 'time' => $elapsed];
            }

            return [
                'success' => true,
                'response' => $result['content'] ?? 'Connected',
                'time' => $elapsed,
                'tokens' => $result['usage']['total_tokens'] ?? 0,
            ];
        } catch (Exception $e) {
            $elapsed = round(microtime(true) - $startTime, 2);
            return ['success' => false, 'error' => $e->getMessage(), 'time' => $elapsed];
        }
    }

    public function getProviderTypes(): array
    {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'description' => 'GPT-4, GPT-3.5 Turbo and other OpenAI models',
                'default_base_url' => 'https://api.openai.com/v1',
                'default_model' => 'gpt-4',
                'requires_api_key' => true,
            ],
            'anthropic' => [
                'name' => 'Anthropic',
                'description' => 'Claude 3.5, Claude 3 and other Anthropic models',
                'default_base_url' => 'https://api.anthropic.com/v1',
                'default_model' => 'claude-3-5-sonnet-20241022',
                'requires_api_key' => true,
            ],
            'google' => [
                'name' => 'Google AI',
                'description' => 'Gemini Pro, Gemini Ultra and other Google models',
                'default_base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'default_model' => 'gemini-pro',
                'requires_api_key' => true,
            ],
            'huggingface' => [
                'name' => 'Hugging Face',
                'description' => 'Open-source models via Hugging Face Inference API',
                'default_base_url' => 'https://api-inference.huggingface.co/models',
                'default_model' => 'meta-llama/Llama-2-70b-chat-hf',
                'requires_api_key' => true,
            ],
            'ollama' => [
                'name' => 'Ollama',
                'description' => 'Local LLM models via Ollama',
                'default_base_url' => 'http://localhost:11434/api',
                'default_model' => 'llama2',
                'requires_api_key' => false,
            ],
            'lmstudio' => [
                'name' => 'LM Studio',
                'description' => 'Local LLM models via LM Studio',
                'default_base_url' => 'http://localhost:1234/v1',
                'default_model' => 'default',
                'requires_api_key' => false,
            ],
            'openrouter' => [
                'name' => 'OpenRouter',
                'description' => 'Unified API for multiple LLM providers',
                'default_base_url' => 'https://openrouter.ai/api/v1',
                'default_model' => 'openai/gpt-3.5-turbo',
                'requires_api_key' => true,
            ],
            'universal' => [
                'name' => 'Universal (OpenAI-compatible)',
                'description' => 'Any OpenAI-compatible API endpoint',
                'default_base_url' => '',
                'default_model' => '',
                'requires_api_key' => true,
            ],
        ];
    }

    public function countByUserId(int $userId): int
    {
        $result = $this->db->fetch('SELECT COUNT(*) as cnt FROM llm_providers WHERE user_id = ?', [$userId]);
        return (int) $result['cnt'];
    }

    public function findAll(): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, u.username FROM llm_providers p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC'
        );
    }
}
