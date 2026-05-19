<?php

/**
 * ManusClaw PHP - Main Entry Point / Router
 *
 * Routes URL patterns to controller actions:
 *   /controller/action/param
 */

// Load configuration
require_once __DIR__ . '/../config/database.php';

// Autoload models
spl_autoload_register(function (string $class) {
    $modelFile = __DIR__ . '/../app/models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }

    $controllerFile = __DIR__ . '/../app/controllers/' . $class . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }

    $serviceFile = __DIR__ . '/../app/services/' . $class . '.php';
    if (file_exists($serviceFile)) {
        require_once $serviceFile;
        return;
    }
});

// Parse URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

// Determine controller, action, and parameter
$controllerName = !empty($urlParts[0]) ? ucfirst($urlParts[0]) . 'Controller' : null;
$actionName = $urlParts[1] ?? null;
$param = $urlParts[2] ?? null;

// Route mapping for default behavior
if (empty($controllerName) || $controllerName === 'Controller') {
    if (isset($_SESSION['user_id'])) {
        $controllerName = 'UserController';
        $actionName = $actionName ?? 'dashboard';
    } else {
        $controllerName = 'AuthController';
        $actionName = $actionName ?? 'showLogin';
    }
}

// Validate controller exists
$controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 - Page Not Found</title>';
    echo '<style>body{font-family:system-ui,sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#1a1a2e;color:#e0e0e0}';
    echo '.error-container{text-align:center;padding:2rem}';
    echo 'h1{font-size:4rem;margin:0;color:#e94560}';
    echo 'p{font-size:1.2rem;color:#aaa;margin:1rem 0}';
    echo 'a{color:#0f3460;background:#e94560;padding:0.5rem 1.5rem;border-radius:4px;text-decoration:none;font-weight:bold}';
    echo 'a:hover{background:#c81e45}</style></head>';
    echo '<body><div class="error-container">';
    echo '<h1>404</h1>';
    echo '<p>The page you are looking for could not be found.</p>';
    echo '<a href="/">Go Home</a>';
    echo '</div></body></html>';
    exit;
}

// Instantiate controller
$controller = new $controllerName();

// Validate action exists
if (empty($actionName)) {
    // Default action based on controller
    $actionName = match ($controllerName) {
        'AuthController' => 'showLogin',
        'UserController' => 'dashboard',
        'AdminController' => 'dashboard',
        default => 'index',
    };
}

// Convert URL-style action name to method name (e.g., 'show-login' -> 'showLogin')
$actionName = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $actionName))));

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 - Action Not Found</title>';
    echo '<style>body{font-family:system-ui,sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#1a1a2e;color:#e0e0e0}';
    echo '.error-container{text-align:center;padding:2rem}';
    echo 'h1{font-size:4rem;margin:0;color:#e94560}';
    echo 'p{font-size:1.2rem;color:#aaa;margin:1rem 0}';
    echo 'a{color:#0f3460;background:#e94560;padding:0.5rem 1.5rem;border-radius:4px;text-decoration:none;font-weight:bold}';
    echo 'a:hover{background:#c81e45}</style></head>';
    echo '<body><div class="error-container">';
    echo '<h1>404</h1>';
    echo '<p>The requested action could not be found.</p>';
    echo '<a href="/">Go Home</a>';
    echo '</div></body></html>';
    exit;
}

// CSRF validation for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        // Allow some actions without CSRF (AJAX with custom header)
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $hasCsrfHeader = !empty($_SERVER['HTTP_X_CSRF_TOKEN']);

        if (!$isAjax || !$hasCsrfHeader) {
            http_response_code(403);
            echo '<!DOCTYPE html><html><head><title>403 - Security Error</title>';
            echo '<style>body{font-family:system-ui,sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#1a1a2e;color:#e0e0e0}';
            echo '.error-container{text-align:center;padding:2rem}';
            echo 'h1{font-size:4rem;margin:0;color:#e94560}';
            echo 'p{font-size:1.2rem;color:#aaa;margin:1rem 0}';
            echo 'a{color:#0f3460;background:#e94560;padding:0.5rem 1.5rem;border-radius:4px;text-decoration:none;font-weight:bold}';
            echo 'a:hover{background:#c81e45}</style></head>';
            echo '<body><div class="error-container">';
            echo '<h1>403</h1>';
            echo '<p>Invalid security token. Please go back and try again.</p>';
            echo '<a href="/">Go Home</a>';
            echo '</div></body></html>';
            exit;
        }
    }
}

// Call the controller action with parameter if provided
try {
    if ($param !== null) {
        $controller->$actionName($param);
    } else {
        $controller->$actionName();
    }
} catch (Exception $e) {
    error_log("ManusClaw Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>500 - Server Error</title>';
    echo '<style>body{font-family:system-ui,sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#1a1a2e;color:#e0e0e0}';
    echo '.error-container{text-align:center;padding:2rem}';
    echo 'h1{font-size:4rem;margin:0;color:#e94560}';
    echo 'p{font-size:1.2rem;color:#aaa;margin:1rem 0}';
    echo 'a{color:#0f3460;background:#e94560;padding:0.5rem 1.5rem;border-radius:4px;text-decoration:none;font-weight:bold}';
    echo 'a:hover{background:#c81e45}</style></head>';
    echo '<body><div class="error-container">';
    echo '<h1>500</h1>';
    echo '<p>An internal server error occurred.</p>';
    echo '<a href="/">Go Home</a>';
    echo '</div></body></html>';
    exit;
}
