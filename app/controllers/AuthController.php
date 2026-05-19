<?php

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/user/dashboard');
            return;
        }

        $this->view('auth/login', [
            'pageTitle' => 'Login - ' . APP_NAME,
        ], 'auth');
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/login');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token. Please try again.');
            $this->redirect('/auth/login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Please enter both username and password.');
            $this->redirect('/auth/login');
            return;
        }

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if (!$user) {
            $activityLog = new ActivityLog();
            $activityLog->log(null, 'login_failed', "Failed login attempt for: {$username}", $this->getClientIp());

            $this->setFlash('error', 'Invalid username or password.');
            $this->redirect('/auth/login');
            return;
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'];

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Log activity
        $activityLog = new ActivityLog();
        $activityLog->log($user['id'], 'login', 'User logged in successfully', $this->getClientIp());

        $this->setFlash('success', 'Welcome back, ' . htmlspecialchars($user['username']) . '!');
        $this->redirect('/user/dashboard');
    }

    public function showRegister(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/user/dashboard');
            return;
        }

        // Check if registration is allowed
        $settingModel = new Setting();
        $allowRegistration = $settingModel->get('allow_registration', true);

        if (!$allowRegistration) {
            $this->setFlash('error', 'Registration is currently disabled.');
            $this->redirect('/auth/login');
            return;
        }

        $this->view('auth/register', [
            'pageTitle' => 'Register - ' . APP_NAME,
        ], 'auth');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/register');
            return;
        }

        // Check if registration is allowed
        $settingModel = new Setting();
        $allowRegistration = $settingModel->get('allow_registration', true);

        if (!$allowRegistration) {
            $this->setFlash('error', 'Registration is currently disabled.');
            $this->redirect('/auth/login');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token. Please try again.');
            $this->redirect('/auth/register');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = [];

        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        $userModel = new User();

        if ($userModel->findByUsername($username)) {
            $errors[] = 'Username is already taken.';
        }

        if ($userModel->findByEmail($email)) {
            $errors[] = 'Email is already registered.';
        }

        if (!empty($errors)) {
            $_SESSION['form_data'] = ['username' => $username, 'email' => $email];
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/auth/register');
            return;
        }

        $userId = $userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'user',
        ]);

        // Auto-login after registration
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_username'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'user';
        $_SESSION['user_avatar'] = null;

        session_regenerate_id(true);

        $activityLog = new ActivityLog();
        $activityLog->log($userId, 'register', 'New user registered', $this->getClientIp());

        $this->setFlash('success', 'Welcome to ' . APP_NAME . '! Your account has been created.');
        $this->redirect('/user/dashboard');
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $activityLog = new ActivityLog();
            $activityLog->log($_SESSION['user_id'], 'logout', 'User logged out', $this->getClientIp());
        }

        // Clear all session data
        $_SESSION = [];
        session_regenerate_id(true);
        session_destroy();

        $this->redirect('/auth/login');
    }

    public function showProfile(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);

        if (!$user) {
            $this->redirect('/auth/login');
            return;
        }

        unset($user['password_hash']);

        $this->view('auth/profile', [
            'pageTitle' => 'My Profile - ' . APP_NAME,
            'user' => $user,
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/auth/profile');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid security token. Please try again.');
            $this->redirect('/auth/profile');
            return;
        }

        $userModel = new User();
        $currentUserId = $_SESSION['user_id'];

        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

        $data = [];

        // Update email if changed
        $currentUser = $userModel->findById($currentUserId);
        if ($email !== $currentUser['email']) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please enter a valid email address.');
                $this->redirect('/auth/profile');
                return;
            }

            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] !== $currentUserId) {
                $this->setFlash('error', 'Email is already in use by another account.');
                $this->redirect('/auth/profile');
                return;
            }

            $data['email'] = $email;
            $_SESSION['user_email'] = $email;
        }

        // Change password if requested
        if (!empty($currentPassword) || !empty($newPassword)) {
            if (!password_verify($currentPassword, $currentUser['password_hash'])) {
                $this->setFlash('error', 'Current password is incorrect.');
                $this->redirect('/auth/profile');
                return;
            }

            if (strlen($newPassword) < 6) {
                $this->setFlash('error', 'New password must be at least 6 characters.');
                $this->redirect('/auth/profile');
                return;
            }

            if ($newPassword !== $confirmNewPassword) {
                $this->setFlash('error', 'New passwords do not match.');
                $this->redirect('/auth/profile');
                return;
            }

            $userModel->changePassword($currentUserId, $newPassword);
        }

        if (!empty($data)) {
            $userModel->update($currentUserId, $data);
        }

        $activityLog = new ActivityLog();
        $activityLog->log($currentUserId, 'profile_update', 'Profile updated', $this->getClientIp());

        $this->setFlash('success', 'Profile updated successfully.');
        $this->redirect('/auth/profile');
    }
}
