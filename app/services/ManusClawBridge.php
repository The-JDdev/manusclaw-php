<?php

class ManusClawBridge
{
    private array $providerConfig;

    public function __construct(array $providerConfig)
    {
        $this->providerConfig = $providerConfig;
    }

    public function executeTask(string $message, array $options = []): array
    {
        $messages = [['role' => 'user', 'content' => $message]];

        // Try ManusClaw Python backend first
        $manusclawUrl = $options['manusclaw_url'] ?? getenv('MANUSCLAW_API_URL');

        if ($manusclawUrl) {
            $result = $this->callManusClawBackend($manusclawUrl, $message, $options);
            if ($result !== null) {
                return $result;
            }
        }

        // Fall back to direct LLM API call
        return $this->callLLM($this->providerConfig, $messages, $options);
    }

    private function callManusClawBackend(string $url, string $message, array $options = []): ?array
    {
        $payload = [
            'message' => $message,
            'provider' => $this->providerConfig['provider_type'],
            'model' => $this->providerConfig['model_name'] ?? null,
            'options' => $options,
        ];

        $ch = curl_init(rtrim($url, '/') . '/api/execute');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . ($this->providerConfig['api_key'] ?? ''),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->providerConfig['timeout'] ?? DEFAULT_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return null; // Backend unavailable, fall through to direct call
        }

        if ($httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    public function callLLM(array $provider, array $messages, array $options = []): array
    {
        $providerType = $provider['provider_type'];

        try {
            switch ($providerType) {
                case 'openai':
                case 'lmstudio':
                case 'openrouter':
                case 'universal':
                    return $this->callOpenAICompatible($provider, $messages, $options);

                case 'anthropic':
                    return $this->callAnthropic($provider, $messages, $options);

                case 'google':
                    return $this->callGoogle($provider, $messages, $options);

                case 'huggingface':
                    return $this->callHuggingFace($provider, $messages, $options);

                case 'ollama':
                    return $this->callOllama($provider, $messages, $options);

                default:
                    return ['error' => "Unsupported provider type: {$providerType}"];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function callOpenAICompatible(array $provider, array $messages, array $options = []): array
    {
        $baseUrl = $this->getBaseUrl($provider);
        $url = rtrim($baseUrl, '/') . '/chat/completions';

        $body = $this->buildOpenAIRequest($messages, $options);
        $body['model'] = $provider['model_name'] ?? 'gpt-4';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($provider['api_key'] ?? ''),
        ];

        if ($provider['provider_type'] === 'openrouter') {
            $headers[] = 'HTTP-Referer: ' . ($options['referer'] ?? 'https://manusclaw.local');
            $headers[] = 'X-Title: ManusClaw';
        }

        $response = $this->httpPost($url, $body, $headers, $provider['timeout'] ?? DEFAULT_TIMEOUT);
        return $this->parseOpenAIResponse($response);
    }

    private function callAnthropic(array $provider, array $messages, array $options = []): array
    {
        $baseUrl = $this->getBaseUrl($provider);
        $url = rtrim($baseUrl, '/') . '/messages';

        $body = $this->buildAnthropicRequest($messages, $options);
        $body['model'] = $provider['model_name'] ?? 'claude-3-5-sonnet-20241022';

        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . ($provider['api_key'] ?? ''),
            'anthropic-version: 2023-06-01',
        ];

        $response = $this->httpPost($url, $body, $headers, $provider['timeout'] ?? DEFAULT_TIMEOUT);
        return $this->parseAnthropicResponse($response);
    }

    private function callGoogle(array $provider, array $messages, array $options = []): array
    {
        $baseUrl = $this->getBaseUrl($provider);
        $model = $provider['model_name'] ?? 'gemini-pro';
        $apiKey = $provider['api_key'] ?? '';

        $url = rtrim($baseUrl, '/') . "/models/{$model}:generateContent?key={$apiKey}";

        $body = $this->buildGoogleRequest($messages, $options);

        $headers = [
            'Content-Type: application/json',
        ];

        $response = $this->httpPost($url, $body, $headers, $provider['timeout'] ?? DEFAULT_TIMEOUT);
        return $this->parseGoogleResponse($response);
    }

    private function callHuggingFace(array $provider, array $messages, array $options = []): array
    {
        $baseUrl = $this->getBaseUrl($provider);
        $model = $provider['model_name'] ?? 'meta-llama/Llama-2-70b-chat-hf';
        $apiKey = $provider['api_key'] ?? '';

        $url = rtrim($baseUrl, '/') . "/{$model}";

        $body = $this->buildHuggingFaceRequest($messages, $options);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];

        $response = $this->httpPost($url, $body, $headers, $provider['timeout'] ?? DEFAULT_TIMEOUT);
        return $this->parseHuggingFaceResponse($response);
    }

    private function callOllama(array $provider, array $messages, array $options = []): array
    {
        $baseUrl = $this->getBaseUrl($provider);
        $url = rtrim($baseUrl, '/') . '/chat';

        $body = [
            'model' => $provider['model_name'] ?? 'llama2',
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => (float) ($provider['temperature'] ?? 0.7),
                'num_predict' => (int) ($options['max_tokens'] ?? $provider['max_tokens'] ?? 4096),
            ],
        ];

        $headers = [
            'Content-Type: application/json',
        ];

        $response = $this->httpPost($url, $body, $headers, $provider['timeout'] ?? DEFAULT_TIMEOUT);
        return $this->parseOllamaResponse($response);
    }

    public function buildOpenAIRequest(array $messages, array $options = []): array
    {
        return [
            'messages' => $messages,
            'max_tokens' => (int) ($options['max_tokens'] ?? $this->providerConfig['max_tokens'] ?? 4096),
            'temperature' => (float) ($options['temperature'] ?? $this->providerConfig['temperature'] ?? 0.7),
            'top_p' => (float) ($options['top_p'] ?? 1.0),
            'frequency_penalty' => (float) ($options['frequency_penalty'] ?? 0),
            'presence_penalty' => (float) ($options['presence_penalty'] ?? 0),
            'stream' => false,
        ];
    }

    public function buildAnthropicRequest(array $messages, array $options = []): array
    {
        // Anthropic requires system message separately
        $systemMessage = null;
        $chatMessages = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemMessage = $msg['content'];
            } else {
                $chatMessages[] = $msg;
            }
        }

        $body = [
            'messages' => $chatMessages,
            'max_tokens' => (int) ($options['max_tokens'] ?? $this->providerConfig['max_tokens'] ?? 4096),
            'temperature' => (float) ($options['temperature'] ?? $this->providerConfig['temperature'] ?? 0.7),
        ];

        if ($systemMessage !== null) {
            $body['system'] = $systemMessage;
        }

        return $body;
    }

    public function buildHuggingFaceRequest(array $messages, array $options = []): array
    {
        // HF Inference API format
        $lastMessage = end($messages);
        return [
            'inputs' => $lastMessage['content'],
            'parameters' => [
                'max_new_tokens' => (int) ($options['max_tokens'] ?? $this->providerConfig['max_tokens'] ?? 4096),
                'temperature' => (float) ($options['temperature'] ?? $this->providerConfig['temperature'] ?? 0.7),
                'return_full_text' => false,
            ],
            'options' => [
                'wait_for_model' => true,
            ],
        ];
    }

    private function buildGoogleRequest(array $messages, array $options = []): array
    {
        $contents = [];
        foreach ($messages as $msg) {
            if ($msg['role'] === 'user') {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            } elseif ($msg['role'] === 'assistant') {
                $contents[] = [
                    'role' => 'model',
                    'parts' => [['text' => $msg['content']]],
                ];
            }
        }

        return [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => (int) ($options['max_tokens'] ?? $this->providerConfig['max_tokens'] ?? 4096),
                'temperature' => (float) ($options['temperature'] ?? $this->providerConfig['temperature'] ?? 0.7),
                'topP' => (float) ($options['top_p'] ?? 0.95),
            ],
        ];
    }

    private function httpPost(string $url, array $body, array $headers, int $timeout = 300): array
    {
        $ch = curl_init($url);

        $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        if ($error) {
            return [
                'error' => true,
                'message' => 'cURL error: ' . $error,
                'http_code' => 0,
            ];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => true,
                'message' => 'Invalid JSON response: ' . substr($response, 0, 500),
                'http_code' => $httpCode,
            ];
        }

        return [
            'data' => $decoded,
            'http_code' => $httpCode,
            'total_time' => $totalTime,
        ];
    }

    public function parseResponse(array $response, string $providerType): array
    {
        return match ($providerType) {
            'openai', 'lmstudio', 'openrouter', 'universal' => $this->parseOpenAIResponse($response),
            'anthropic' => $this->parseAnthropicResponse($response),
            'google' => $this->parseGoogleResponse($response),
            'huggingface' => $this->parseHuggingFaceResponse($response),
            'ollama' => $this->parseOllamaResponse($response),
            default => ['error' => "Unknown provider type: {$providerType}"],
        };
    }

    private function parseOpenAIResponse(array $response): array
    {
        if (isset($response['error'])) {
            $errorMsg = $response['message'] ?? 'Unknown error';
            if (isset($response['data']['error'])) {
                $errorMsg = $response['data']['error']['message'] ?? json_encode($response['data']['error']);
            }
            return ['error' => $errorMsg];
        }

        $data = $response['data'] ?? null;
        if (!$data) {
            return ['error' => 'Empty response from API'];
        }

        if (isset($data['error'])) {
            return ['error' => $data['error']['message'] ?? json_encode($data['error'])];
        }

        $content = '';
        if (isset($data['choices'][0]['message']['content'])) {
            $content = $data['choices'][0]['message']['content'];
        }

        $usage = [
            'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
            'total_tokens' => $data['usage']['total_tokens'] ?? 0,
        ];

        return [
            'content' => $content,
            'usage' => $usage,
            'model' => $data['model'] ?? '',
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? '',
        ];
    }

    private function parseAnthropicResponse(array $response): array
    {
        if (isset($response['error'])) {
            $errorMsg = $response['message'] ?? 'Unknown error';
            if (isset($response['data']['error'])) {
                $errorMsg = $response['data']['error']['message'] ?? json_encode($response['data']['error']);
            }
            return ['error' => $errorMsg];
        }

        $data = $response['data'] ?? null;
        if (!$data) {
            return ['error' => 'Empty response from API'];
        }

        if (isset($data['error'])) {
            return ['error' => $data['error']['message'] ?? json_encode($data['error'])];
        }

        $content = '';
        if (isset($data['content'][0]['text'])) {
            $content = $data['content'][0]['text'];
        }

        $usage = [
            'prompt_tokens' => $data['usage']['input_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['output_tokens'] ?? 0,
            'total_tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
        ];

        return [
            'content' => $content,
            'usage' => $usage,
            'model' => $data['model'] ?? '',
            'finish_reason' => $data['stop_reason'] ?? '',
        ];
    }

    private function parseGoogleResponse(array $response): array
    {
        if (isset($response['error'])) {
            $errorMsg = $response['message'] ?? 'Unknown error';
            if (isset($response['data']['error'])) {
                $errorMsg = $response['data']['error']['message'] ?? json_encode($response['data']['error']);
            }
            return ['error' => $errorMsg];
        }

        $data = $response['data'] ?? null;
        if (!$data) {
            return ['error' => 'Empty response from API'];
        }

        if (isset($data['error'])) {
            return ['error' => $data['error']['message'] ?? json_encode($data['error'])];
        }

        $content = '';
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $data['candidates'][0]['content']['parts'][0]['text'];
        }

        $usage = [
            'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
            'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
            'total_tokens' => $data['usageMetadata']['totalTokenCount'] ?? 0,
        ];

        return [
            'content' => $content,
            'usage' => $usage,
            'model' => '',
            'finish_reason' => $data['candidates'][0]['finishReason'] ?? '',
        ];
    }

    private function parseHuggingFaceResponse(array $response): array
    {
        if (isset($response['error'])) {
            $errorMsg = $response['message'] ?? 'Unknown error';
            if (isset($response['data']['error'])) {
                $errorMsg = $response['data']['error'] ?? json_encode($response['data']);
            }
            return ['error' => $errorMsg];
        }

        $data = $response['data'] ?? null;
        if (!$data) {
            return ['error' => 'Empty response from API'];
        }

        // HF can return array or error string
        if (is_string($data)) {
            return ['error' => $data];
        }

        $content = '';
        if (is_array($data) && isset($data[0]['generated_text'])) {
            $content = $data[0]['generated_text'];
        } elseif (is_array($data) && isset($data[0]['summary_text'])) {
            $content = $data[0]['summary_text'];
        }

        return [
            'content' => $content,
            'usage' => [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ],
            'model' => '',
            'finish_reason' => '',
        ];
    }

    private function parseOllamaResponse(array $response): array
    {
        if (isset($response['error'])) {
            $errorMsg = $response['message'] ?? 'Unknown error';
            if (isset($response['data']['error'])) {
                $errorMsg = $response['data']['error'];
            }
            return ['error' => $errorMsg];
        }

        $data = $response['data'] ?? null;
        if (!$data) {
            return ['error' => 'Empty response from API'];
        }

        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }

        $content = $data['message']['content'] ?? '';

        return [
            'content' => $content,
            'usage' => [
                'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
                'completion_tokens' => $data['eval_count'] ?? 0,
                'total_tokens' => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
            ],
            'model' => $data['model'] ?? '',
            'finish_reason' => 'stop',
        ];
    }

    private function getBaseUrl(array $provider): string
    {
        if (!empty($provider['base_url'])) {
            return rtrim($provider['base_url'], '/');
        }

        $defaults = self::getProviderDefaults($provider['provider_type']);
        return $defaults['default_base_url'] ?? '';
    }

    public static function getProviderDefaults(string $type): array
    {
        $defaults = [
            'openai' => [
                'default_base_url' => 'https://api.openai.com/v1',
                'default_model' => 'gpt-4',
                'default_max_tokens' => 4096,
                'default_temperature' => 0.7,
                'requires_api_key' => true,
                'supports_streaming' => true,
                'supports_system_prompt' => true,
                'api_format' => 'openai',
            ],
            'anthropic' => [
                'default_base_url' => 'https://api.anthropic.com/v1',
                'default_model' => 'claude-3-5-sonnet-20241022',
                'default_max_tokens' => 4096,
                'default_temperature' => 0.7,
                'requires_api_key' => true,
                'supports_streaming' => true,
                'supports_system_prompt' => true,
                'api_format' => 'anthropic',
            ],
            'google' => [
                'default_base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'default_model' => 'gemini-pro',
                'default_max_tokens' => 4096,
                'default_temperature' => 0.7,
                'requires_api_key' => true,
                'supports_streaming' => true,
                'supports_system_prompt' => true,
                'api_format' => 'google',
            ],
            'huggingface' => [
                'default_base_url' => 'https://api-inference.huggingface.co/models',
                'default_model' => 'meta-llama/Llama-2-70b-chat-hf',
                'default_max_tokens' => 2048,
                'default_temperature' => 0.7,
                'requires_api_key' => true,
                'supports_streaming' => false,
                'supports_system_prompt' => false,
                'api_format' => 'huggingface',
            ],
            'ollama' => [
                'default_base_url' => 'http://localhost:11434/api',
                'default_model' => 'llama2',
                'default_max_tokens' => 4096,
                'default_temperature' => 0.7,
                'requires_api_key' => false,
                'supports_streaming' => true,
                'supports_system_prompt' => true,
                'api_format' => 'ollama',
            ],
            'lmstudio' => [
                'default_base_url' => 'http://localhost:1234/v1',
                'default_model' => 'default',
                'default_max_tokens' => 4096,
                'default_temperature' => 0.7,
                'requires_api_key' => false,
                'supports_streaming' => true,
                'supports_system_prompt' => true,
                'api_format' => 'openai',
            ],
            'openrouter' => [
                'default_base_url' => 'https://openrouter.ai/api/v1',
                'default_model' => 'openai/gpt-3.5-turbo',
                'default_max_tokens' => 4096,
                'default_temperature' => 0.7,
                'requires_api_key' => true,
                'supports_streaming' => true,
                'supports_system_prompt' => true,
                'api_format' => 'openai',
            ],
            'universal' => [
                'default_base_url' => '',
                'default_model' => '',
                'default_max_tokens' => 4096,
                'default_temperature' => 0.7,
                'requires_api_key' => true,
                'supports_streaming' => false,
                'supports_system_prompt' => true,
                'api_format' => 'openai',
            ],
        ];

        return $defaults[$type] ?? $defaults['universal'];
    }
}
