<?php

define('DB_PATH', __DIR__ . '/../storage/manusclaw.db');
define('SESSION_PATH', __DIR__ . '/../storage/sessions');
define('UPLOAD_PATH', __DIR__ . '/../storage/uploads');
define('LOG_PATH', __DIR__ . '/../storage/logs');
define('APP_NAME', 'ManusClaw');
define('APP_VERSION', '1.0.0');
define('DEFAULT_TIMEOUT', 300);

// Create directories if not exist
foreach ([SESSION_PATH, UPLOAD_PATH, LOG_PATH] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Session config
ini_set('session.save_path', SESSION_PATH);
session_start();
