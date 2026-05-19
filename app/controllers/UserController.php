<?php

class UserController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireAuth();

        $userId = $_SESSION['user_id'];

        $taskModel = new Task();
        $providerModel = new LLMProvider();
        $activityLog = new ActivityLog();

        $taskStats = $taskModel->getStats($userId);
        $recentTasks = $taskModel->findRecentByUser($userId, 5);
        $providerCount = $providerModel->countByUserId($userId);
        $recentActivity = $activityLog->getByUser($userId, 5);

        $this->view('user/dashboard', [
            'pageTitle' => 'Dashboard - ' . APP_NAME,
            'taskStats' => $taskStats,
            'recentTasks' => $recentTasks,
            'providerCount' => $providerCount,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function providers(): void
    {
        $this->requireAuth();

        $userId = $_SESSION['user_id'];
        $providerModel = new LLMProvider();
        $providers = $providerModel->findByUserId($userId);
        $providerTypes = $providerModel->getProviderTypes();

        $this->view('user/providers', [
            'pageTitle' => 'LLM Providers - ' . APP_NAME,
            'providers' => $providers,
            'providerTypes' => $providerTypes,
        ]);
    }

    public function addProvider(): void
    {
        $this->requireAuth();

        $providerModel = new LLMProvider();
        $providerTypes = $providerModel->getProviderTypes();

        $this->view('user/provider_form', [
            'pageTitle' => 'Add Provider - ' . APP_NAME,
            'providerTypes' => $providerTypes,
            'provider' => null,
            'formAction' => '/user/providers/save',
        ]);
    }

    public function saveProvider(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/providers');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/user/providers/add');
            return;
        }

        $userId = $_SESSION['user_id'];
        $data = $this->getPostData();

        $errors = $this->validateProviderData($data);
        if (!empty($errors)) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/user/providers/add');
            return;
        }

        $providerModel = new LLMProvider();
        $providerId = $providerModel->create([
            'user_id' => $userId,
            'name' => $this->sanitizeInput($data['name']),
            'provider_type' => $data['provider_type'],
            'api_key' => $data['api_key'] ?? null,
            'base_url' => !empty($data['base_url']) ? trim($data['base_url']) : null,
            'model_name' => !empty($data['model_name']) ? trim($data['model_name']) : null,
            'is_default' => isset($data['is_default']) ? 1 : 0,
            'max_tokens' => (int) ($data['max_tokens'] ?? 4096),
            'temperature' => (float) ($data['temperature'] ?? 0.7),
            'timeout' => (int) ($data['timeout'] ?? 300),
        ]);

        $activityLog = new ActivityLog();
        $activityLog->log($userId, 'provider_create', "Created provider: {$data['name']}", $this->getClientIp());

        $this->setFlash('success', 'Provider created successfully.');
        $this->redirect('/user/providers');
    }

    public function editProvider(int $id): void
    {
        $this->requireAuth();

        $providerModel = new LLMProvider();
        $provider = $providerModel->findById($id);

        if (!$provider || $provider['user_id'] !== $_SESSION['user_id']) {
            $this->setFlash('error', 'Provider not found.');
            $this->redirect('/user/providers');
            return;
        }

        $providerTypes = $providerModel->getProviderTypes();

        $this->view('user/provider_form', [
            'pageTitle' => 'Edit Provider - ' . APP_NAME,
            'providerTypes' => $providerTypes,
            'provider' => $provider,
            'formAction' => "/user/providers/update/{$id}",
        ]);
    }

    public function updateProvider(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/providers');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect("/user/providers/edit/{$id}");
            return;
        }

        $providerModel = new LLMProvider();
        $provider = $providerModel->findById($id);

        if (!$provider || $provider['user_id'] !== $_SESSION['user_id']) {
            $this->setFlash('error', 'Provider not found.');
            $this->redirect('/user/providers');
            return;
        }

        $data = $this->getPostData();

        $errors = $this->validateProviderData($data, $id);
        if (!empty($errors)) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect("/user/providers/edit/{$id}");
            return;
        }

        $updateData = [
            'name' => $this->sanitizeInput($data['name']),
            'provider_type' => $data['provider_type'],
            'base_url' => !empty($data['base_url']) ? trim($data['base_url']) : null,
            'model_name' => !empty($data['model_name']) ? trim($data['model_name']) : null,
            'is_default' => isset($data['is_default']) ? 1 : 0,
            'max_tokens' => (int) ($data['max_tokens'] ?? 4096),
            'temperature' => (float) ($data['temperature'] ?? 0.7),
            'timeout' => (int) ($data['timeout'] ?? 300),
        ];

        // Only update API key if a new one is provided
        if (!empty($data['api_key'])) {
            $updateData['api_key'] = $data['api_key'];
        }

        $providerModel->update($id, $updateData);

        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], 'provider_update', "Updated provider: {$data['name']}", $this->getClientIp());

        $this->setFlash('success', 'Provider updated successfully.');
        $this->redirect('/user/providers');
    }

    public function deleteProvider(int $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/user/providers');
            return;
        }

        $providerModel = new LLMProvider();
        $provider = $providerModel->findById($id);

        if (!$provider || $provider['user_id'] !== $_SESSION['user_id']) {
            $this->setFlash('error', 'Provider not found.');
            $this->redirect('/user/providers');
            return;
        }

        $providerModel->delete($id);

        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], 'provider_delete', "Deleted provider: {$provider['name']}", $this->getClientIp());

        $this->setFlash('success', 'Provider deleted successfully.');
        $this->redirect('/user/providers');
    }

    public function testProvider(int $id): void
    {
        $this->requireAuth();

        $providerModel = new LLMProvider();
        $provider = $providerModel->findById($id);

        if (!$provider || $provider['user_id'] !== $_SESSION['user_id']) {
            $this->respondWithError('Provider not found.', 404);
            return;
        }

        $result = $providerModel->testConnection($id);
        $this->json($result);
    }

    public function newTask(): void
    {
        $this->requireAuth();

        $userId = $_SESSION['user_id'];
        $providerModel = new LLMProvider();
        $providers = $providerModel->findByUserId($userId);

        // Filter to only active providers
        $activeProviders = array_filter($providers, fn($p) => $p['is_active']);

        if (empty($activeProviders)) {
            $this->setFlash('warning', 'Please add an LLM provider before creating a task.');
            $this->redirect('/user/providers/add');
            return;
        }

        $this->view('user/task_form', [
            'pageTitle' => 'New Task - ' . APP_NAME,
            'providers' => $activeProviders,
        ]);
    }

    public function createTask(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/tasks/new');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/user/tasks/new');
            return;
        }

        $userId = $_SESSION['user_id'];
        $data = $this->getPostData();

        $title = trim($data['title'] ?? '');
        $inputMessage = trim($data['input_message'] ?? '');
        $providerId = (int) ($data['provider_id'] ?? 0);
        $description = trim($data['description'] ?? '');

        if (empty($title) || empty($inputMessage)) {
            $this->setFlash('error', 'Title and message are required.');
            $this->redirect('/user/tasks/new');
            return;
        }

        $providerModel = new LLMProvider();
        $provider = $providerModel->findById($providerId);

        if (!$provider || $provider['user_id'] !== $userId) {
            $this->setFlash('error', 'Invalid provider selected.');
            $this->redirect('/user/tasks/new');
            return;
        }

        // Create task record
        $taskModel = new Task();
        $taskId = $taskModel->create([
            'user_id' => $userId,
            'provider_id' => $providerId,
            'title' => $this->sanitizeInput($title),
            'description' => !empty($description) ? $this->sanitizeInput($description) : null,
            'status' => 'pending',
            'input_message' => $inputMessage,
        ]);

        $activityLog = new ActivityLog();
        $activityLog->log($userId, 'task_create', "Created task: {$title}", $this->getClientIp());

        // Execute the task
        $this->executeTask($taskId, $provider, $inputMessage);

        $this->redirect("/user/tasks/view/{$taskId}");
    }

    private function executeTask(int $taskId, array $provider, string $message): void
    {
        $taskModel = new Task();

        // Mark as running
        $taskModel->updateStatus($taskId, 'running');

        $startTime = microtime(true);

        try {
            $bridge = new ManusClawBridge($provider);
            $messages = [['role' => 'user', 'content' => $message]];
            $result = $bridge->callLLM($provider, $messages);

            $executionTime = round(microtime(true) - $startTime, 2);

            if (isset($result['error'])) {
                $taskModel->updateStatus($taskId, 'failed', [
                    'error_message' => $result['error'],
                    'execution_time' => $executionTime,
                ]);
            } else {
                $tokens = $result['usage']['total_tokens'] ?? 0;
                $taskModel->updateStatus($taskId, 'completed', [
                    'output_result' => $result['content'] ?? '',
                    'tokens_used' => $tokens,
                    'execution_time' => $executionTime,
                ]);
            }
        } catch (Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            $taskModel->updateStatus($taskId, 'failed', [
                'error_message' => $e->getMessage(),
                'execution_time' => $executionTime,
            ]);
        }
    }

    public function tasks(): void
    {
        $this->requireAuth();

        $userId = $_SESSION['user_id'];
        $params = $this->getQueryParams();

        $filters = [];
        if (!empty($params['status'])) {
            $filters['status'] = $params['status'];
        }
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }
        if (!empty($params['provider_id'])) {
            $filters['provider_id'] = (int) $params['provider_id'];
        }

        $taskModel = new Task();
        $providerModel = new LLMProvider();

        $tasks = $taskModel->findByUserId($userId, $filters);
        $providers = $providerModel->findByUserId($userId);
        $taskStats = $taskModel->getStats($userId);

        $this->view('user/tasks', [
            'pageTitle' => 'Tasks - ' . APP_NAME,
            'tasks' => $tasks,
            'providers' => $providers,
            'taskStats' => $taskStats,
            'filters' => $filters,
        ]);
    }

    public function viewTask(int $id): void
    {
        $this->requireAuth();

        $taskModel = new Task();
        $task = $taskModel->findById($id);

        if (!$task || $task['user_id'] !== $_SESSION['user_id']) {
            $this->setFlash('error', 'Task not found.');
            $this->redirect('/user/tasks');
            return;
        }

        $this->view('user/task_view', [
            'pageTitle' => $task['title'] . ' - ' . APP_NAME,
            'task' => $task,
        ]);
    }

    public function cancelTask(int $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/user/tasks');
            return;
        }

        $taskModel = new Task();
        $task = $taskModel->findById($id);

        if (!$task || $task['user_id'] !== $_SESSION['user_id']) {
            $this->setFlash('error', 'Task not found.');
            $this->redirect('/user/tasks');
            return;
        }

        if ($taskModel->cancel($id)) {
            $activityLog = new ActivityLog();
            $activityLog->log($_SESSION['user_id'], 'task_cancel', "Cancelled task: {$task['title']}", $this->getClientIp());
            $this->setFlash('success', 'Task cancelled.');
        } else {
            $this->setFlash('error', 'Task cannot be cancelled.');
        }

        $this->redirect('/user/tasks');
    }

    public function deleteTask(int $id): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/user/tasks');
            return;
        }

        $taskModel = new Task();
        $task = $taskModel->findById($id);

        if (!$task || $task['user_id'] !== $_SESSION['user_id']) {
            $this->setFlash('error', 'Task not found.');
            $this->redirect('/user/tasks');
            return;
        }

        $taskModel->delete($id);

        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], 'task_delete', "Deleted task: {$task['title']}", $this->getClientIp());

        $this->setFlash('success', 'Task deleted.');
        $this->redirect('/user/tasks');
    }

    private function validateProviderData(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Provider name is required.';
        }

        if (empty($data['provider_type'])) {
            $errors[] = 'Provider type is required.';
        }

        $validTypes = ['openai', 'anthropic', 'google', 'huggingface', 'ollama', 'lmstudio', 'openrouter', 'universal'];
        if (!in_array($data['provider_type'] ?? '', $validTypes)) {
            $errors[] = 'Invalid provider type.';
        }

        if (isset($data['max_tokens']) && ((int) $data['max_tokens'] < 1 || (int) $data['max_tokens'] > 100000)) {
            $errors[] = 'Max tokens must be between 1 and 100,000.';
        }

        if (isset($data['temperature'])) {
            $temp = (float) $data['temperature'];
            if ($temp < 0 || $temp > 2) {
                $errors[] = 'Temperature must be between 0 and 2.';
            }
        }

        if (isset($data['timeout']) && ((int) $data['timeout'] < 1 || (int) $data['timeout'] > 3600)) {
            $errors[] = 'Timeout must be between 1 and 3600 seconds.';
        }

        return $errors;
    }
}
