<?php

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dbDir = dirname(DB_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        $this->pdo = new PDO('sqlite:' . DB_PATH);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // Enable WAL mode for better concurrency
        $this->pdo->exec('PRAGMA journal_mode=WAL');
        $this->pdo->exec('PRAGMA foreign_keys=ON');

        $this->initializeTables();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function initializeTables(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT \'user\' CHECK(role IN (\'admin\', \'user\')),
            avatar TEXT DEFAULT NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            updated_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
        )');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS llm_providers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            provider_type TEXT NOT NULL DEFAULT \'openai\' CHECK(provider_type IN (\'openai\', \'anthropic\', \'google\', \'huggingface\', \'ollama\', \'lmstudio\', \'openrouter\', \'universal\')),
            api_key TEXT DEFAULT NULL,
            base_url TEXT DEFAULT NULL,
            model_name TEXT DEFAULT NULL,
            is_default INTEGER NOT NULL DEFAULT 0,
            max_tokens INTEGER NOT NULL DEFAULT 4096,
            temperature REAL NOT NULL DEFAULT 0.7,
            timeout INTEGER NOT NULL DEFAULT 300,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            updated_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            provider_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT DEFAULT NULL,
            status TEXT NOT NULL DEFAULT \'pending\' CHECK(status IN (\'pending\', \'running\', \'completed\', \'failed\', \'cancelled\')),
            input_message TEXT NOT NULL,
            output_result TEXT DEFAULT NULL,
            error_message TEXT DEFAULT NULL,
            tokens_used INTEGER DEFAULT 0,
            execution_time REAL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            started_at TEXT DEFAULT NULL,
            completed_at TEXT DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (provider_id) REFERENCES llm_providers(id) ON DELETE CASCADE
        )');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_name TEXT NOT NULL UNIQUE,
            value_text TEXT DEFAULT NULL,
            value_int INTEGER DEFAULT NULL,
            value_bool INTEGER DEFAULT NULL,
            updated_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
        )');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT NULL,
            action TEXT NOT NULL,
            details TEXT DEFAULT NULL,
            ip_address TEXT DEFAULT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )');

        // Create indexes
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_llm_providers_user_id ON llm_providers(user_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_tasks_user_id ON tasks(user_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_tasks_status ON tasks(status)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_tasks_provider_id ON tasks(provider_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_settings_key ON settings(key_name)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_activity_logs_user_id ON activity_logs(user_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at)');

        $this->seedDefaults();
    }

    private function seedDefaults(): void
    {
        // Seed default admin user if no users exist
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        if ($stmt->fetchColumn() == 0) {
            $this->insert('users', [
                'username' => 'admin',
                'email' => 'admin@manusclaw.local',
                'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                'role' => 'admin',
                'is_active' => 1,
            ]);
        }

        // Seed default settings if none exist
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM settings');
        if ($stmt->fetchColumn() == 0) {
            $defaults = [
                ['app_name', 'ManusClaw', null, null],
                ['app_version', '1.0.0', null, null],
                ['allow_registration', null, null, 1],
                ['default_provider', 'openai', null, null],
                ['max_tokens_default', null, 4096, null],
                ['temperature_default', null, null, null], // stored as text since it's a float
                ['task_timeout', null, 300, null],
                ['maintenance_mode', null, null, 0],
                ['log_retention_days', null, 30, null],
            ];
            $stmt = $this->pdo->prepare('INSERT INTO settings (key_name, value_text, value_int, value_bool) VALUES (?, ?, ?, ?)');
            foreach ($defaults as $row) {
                $stmt->execute($row);
            }
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_map(fn($col) => '"' . $col . '"', array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO \"{$table}\" ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClauses = implode(', ', array_map(fn($col) => '"' . $col . '" = ?', array_keys($data)));
        $sql = "UPDATE \"{$table}\" SET {$setClauses} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM \"{$table}\" WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}
