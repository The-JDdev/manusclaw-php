<?php

class AdminController extends BaseController
{
    public function dashboard(): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $taskModel = new Task();
        $providerModel = new LLMProvider();
        $activityLog = new ActivityLog();

        $totalUsers = $userModel->count();
        $activeUsers = $userModel->getActiveCount();
        $taskStats = $taskModel->getStats();
        $recentTasks = $taskModel->findRecent(5);
        $recentActivity = $activityLog->getRecent(10);
        $totalProviders = count($providerModel->findAll());

        $this->view('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard - ' . APP_NAME,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'taskStats' => $taskStats,
            'recentTasks' => $recentTasks,
            'recentActivity' => $recentActivity,
            'totalProviders' => $totalProviders,
        ]);
    }

    public function users(): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $users = $userModel->getAll();

        $this->view('admin/users', [
            'pageTitle' => 'Manage Users - ' . APP_NAME,
            'users' => $users,
        ]);
    }

    public function editUser(int $id): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        unset($user['password_hash']);

        $this->view('admin/edit-user', [
            'pageTitle' => 'Edit User - ' . APP_NAME,
            'editUser' => $user,
        ]);
    }

    public function updateUser(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect("/admin/users/edit/{$id}");
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $data = $this->getPostData();

        $updateData = [];

        if (!empty($data['username']) && $data['username'] !== $user['username']) {
            $existing = $userModel->findByUsername($data['username']);
            if ($existing && $existing['id'] !== $id) {
                $this->setFlash('error', 'Username is already taken.');
                $this->redirect("/admin/users/edit/{$id}");
                return;
            }
            $updateData['username'] = $this->sanitizeInput($data['username']);
        }

        if (!empty($data['email']) && $data['email'] !== $user['email']) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Invalid email address.');
                $this->redirect("/admin/users/edit/{$id}");
                return;
            }
            $existing = $userModel->findByEmail($data['email']);
            if ($existing && $existing['id'] !== $id) {
                $this->setFlash('error', 'Email is already in use.');
                $this->redirect("/admin/users/edit/{$id}");
                return;
            }
            $updateData['email'] = $data['email'];
        }

        if (isset($data['role']) && in_array($data['role'], ['admin', 'user'])) {
            $updateData['role'] = $data['role'];
        }

        // Reset password if provided
        if (!empty($data['new_password'])) {
            if (strlen($data['new_password']) < 6) {
                $this->setFlash('error', 'Password must be at least 6 characters.');
                $this->redirect("/admin/users/edit/{$id}");
                return;
            }
            $userModel->changePassword($id, $data['new_password']);
        }

        if (!empty($updateData)) {
            $userModel->update($id, $updateData);
        }

        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], 'admin_user_update', "Updated user ID: {$id}", $this->getClientIp());

        $this->setFlash('success', 'User updated successfully.');
        $this->redirect('/admin/users');
    }

    public function deleteUser(int $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/admin/users');
            return;
        }

        // Cannot delete yourself
        if ($id === $_SESSION['user_id']) {
            $this->setFlash('error', 'You cannot delete your own account.');
            $this->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        $userModel->delete($id);

        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], 'admin_user_delete', "Deleted user: {$user['username']}", $this->getClientIp());

        $this->setFlash('success', 'User deleted successfully.');
        $this->redirect('/admin/users');
    }

    public function toggleUserActive(int $id): void
    {
        $this->requireAdmin();

        // Cannot deactivate yourself
        if ($id === $_SESSION['user_id']) {
            $this->respondWithError('You cannot deactivate your own account.');
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            $this->respondWithError('User not found.', 404);
            return;
        }

        $success = $userModel->toggleActive($id);

        $activityLog = new ActivityLog();
        $newStatus = !$user['is_active'] ? 'active' : 'inactive';
        $activityLog->log($_SESSION['user_id'], 'admin_user_toggle', "Set user {$user['username']} to {$newStatus}", $this->getClientIp());

        $this->json(['success' => $success, 'new_status' => $newStatus]);
    }

    public function settings(): void
    {
        $this->requireAdmin();

        $settingModel = new Setting();
        $settings = $settingModel->getAll();

        $this->view('admin/settings', [
            'pageTitle' => 'System Settings - ' . APP_NAME,
            'settings' => $settings,
        ]);
    }

    public function saveSettings(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/admin/settings');
            return;
        }

        $settingModel = new Setting();
        $data = $this->getPostData();

        $settingsMap = [
            'app_name' => 'text',
            'allow_registration' => 'bool',
            'default_provider' => 'text',
            'max_tokens_default' => 'int',
            'temperature_default' => 'text',
            'task_timeout' => 'int',
            'maintenance_mode' => 'bool',
            'log_retention_days' => 'int',
        ];

        foreach ($settingsMap as $key => $type) {
            if (array_key_exists($key, $data)) {
                $value = $data[$key];
                if ($type === 'bool') {
                    $value = isset($data[$key]) ? 1 : 0;
                }
                $settingModel->set($key, $value, $type);
            }
        }

        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], 'admin_settings_save', 'Updated system settings', $this->getClientIp());

        $this->setFlash('success', 'Settings saved successfully.');
        $this->redirect('/admin/settings');
    }

    public function tasks(): void
    {
        $this->requireAdmin();

        $params = $this->getQueryParams();
        $filters = [];

        if (!empty($params['status'])) {
            $filters['status'] = $params['status'];
        }
        if (!empty($params['user_id'])) {
            $filters['user_id'] = (int) $params['user_id'];
        }
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }

        $taskModel = new Task();
        $tasks = $taskModel->findAll($filters);
        $taskStats = $taskModel->getStats();

        $userModel = new User();
        $users = $userModel->getAll();

        $this->view('admin/tasks', [
            'pageTitle' => 'All Tasks - ' . APP_NAME,
            'tasks' => $tasks,
            'taskStats' => $taskStats,
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function viewTask(int $id): void
    {
        $this->requireAdmin();

        $taskModel = new Task();
        $task = $taskModel->findById($id);

        if (!$task) {
            $this->setFlash('error', 'Task not found.');
            $this->redirect('/admin/tasks');
            return;
        }

        $this->view('admin/view-task', [
            'pageTitle' => 'Task: ' . $task['title'] . ' - ' . APP_NAME,
            'task' => $task,
        ]);
    }

    public function deleteTask(int $id): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            $this->redirect('/admin/tasks');
            return;
        }

        $taskModel = new Task();
        $task = $taskModel->findById($id);

        if (!$task) {
            $this->setFlash('error', 'Task not found.');
            $this->redirect('/admin/tasks');
            return;
        }

        $taskModel->delete($id);

        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], 'admin_task_delete', "Deleted task: {$task['title']} (ID: {$id})", $this->getClientIp());

        $this->setFlash('success', 'Task deleted.');
        $this->redirect('/admin/tasks');
    }

    public function providers(): void
    {
        $this->requireAdmin();

        $providerModel = new LLMProvider();
        $providers = $providerModel->findAll();

        $this->view('admin/providers', [
            'pageTitle' => 'All Providers - ' . APP_NAME,
            'providers' => $providers,
        ]);
    }

    public function activityLogs(): void
    {
        $this->requireAdmin();

        $activityLog = new ActivityLog();
        $logs = $activityLog->getRecent(100);

        $this->view('admin/logs', [
            'pageTitle' => 'Activity Logs - ' . APP_NAME,
            'logs' => $logs,
        ]);
    }

    public function systemInfo(): void
    {
        $this->requireAdmin();

        $info = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_path' => DB_PATH,
            'database_size' => file_exists(DB_PATH) ? $this->formatBytes(filesize(DB_PATH)) : 'N/A',
            'php_sapi' => PHP_SAPI,
            'php_os' => PHP_OS_FAMILY,
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'pdo_drivers' => PDO::getAvailableDrivers(),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
            'app_version' => APP_VERSION,
            'storage_path' => __DIR__ . '/../../storage',
            'session_save_path' => SESSION_PATH,
            'upload_path' => UPLOAD_PATH,
            'log_path' => LOG_PATH,
            'loaded_extensions' => get_loaded_extensions(),
        ];

        // Storage directory info
        $storageDirs = [
            'sessions' => SESSION_PATH,
            'uploads' => UPLOAD_PATH,
            'logs' => LOG_PATH,
        ];

        foreach ($storageDirs as $name => $path) {
            $info["storage_{$name}_writable"] = is_writable($path) ? 'Yes' : 'No';
            $info["storage_{$name}_exists"] = is_dir($path) ? 'Yes' : 'No';
        }

        $this->view('admin/system-info', [
            'pageTitle' => 'System Info - ' . APP_NAME,
            'info' => $info,
        ]);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
