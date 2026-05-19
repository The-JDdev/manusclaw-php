<?php

class Task
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByUserId(int $userId, array $filters = []): array
    {
        $sql = 'SELECT t.*, p.name as provider_name, p.provider_type FROM tasks t 
                LEFT JOIN llm_providers p ON t.provider_id = p.id 
                WHERE t.user_id = ?';
        $params = [$userId];

        if (!empty($filters['status'])) {
            $sql .= ' AND t.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['provider_id'])) {
            $sql .= ' AND t.provider_id = ?';
            $params[] = $filters['provider_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (t.title LIKE ? OR t.description LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['date_from'])) {
            $sql .= ' AND t.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= ' AND t.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $sql .= ' ORDER BY t.created_at DESC';

        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= ' OFFSET ' . (int) $filters['offset'];
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT t.*, p.name as provider_name, p.provider_type, u.username 
             FROM tasks t 
             LEFT JOIN llm_providers p ON t.provider_id = p.id 
             LEFT JOIN users u ON t.user_id = u.id 
             WHERE t.id = ?',
            [$id]
        );
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->db->fetchAll(
            'SELECT t.*, p.name as provider_name, u.username 
             FROM tasks t 
             LEFT JOIN llm_providers p ON t.provider_id = p.id 
             LEFT JOIN users u ON t.user_id = u.id 
             ORDER BY t.created_at DESC LIMIT ?',
            [$limit]
        );
    }

    public function findRecentByUser(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            'SELECT t.*, p.name as provider_name, p.provider_type 
             FROM tasks t 
             LEFT JOIN llm_providers p ON t.provider_id = p.id 
             WHERE t.user_id = ? 
             ORDER BY t.created_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public function create(array $data): int
    {
        $fields = [
            'user_id' => $data['user_id'],
            'provider_id' => $data['provider_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'input_message' => $data['input_message'],
            'output_result' => $data['output_result'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'tokens_used' => $data['tokens_used'] ?? 0,
            'execution_time' => $data['execution_time'] ?? 0,
        ];

        return $this->db->insert('tasks', $fields);
    }

    public function update(int $id, array $data): int
    {
        $allowed = ['title', 'description', 'status', 'output_result', 'error_message', 'tokens_used', 'execution_time', 'started_at', 'completed_at'];
        $fields = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[$key] = $data[$key];
            }
        }

        if (empty($fields)) {
            return 0;
        }

        return $this->db->update('tasks', $fields, 'id = ?', [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete('tasks', 'id = ?', [$id]);
    }

    public function updateStatus(int $id, string $status, array $extra = []): int
    {
        $fields = array_merge(['status' => $status], $extra);

        // Set timestamps based on status
        if ($status === 'running') {
            $fields['started_at'] = date('Y-m-d H:i:s');
        }

        if (in_array($status, ['completed', 'failed', 'cancelled'])) {
            $fields['completed_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->update('tasks', $fields, 'id = ?', [$id]);
    }

    public function getStats(?int $userId = null): array
    {
        $where = '';
        $params = [];

        if ($userId !== null) {
            $where = 'WHERE user_id = ?';
            $params = [$userId];
        }

        $statusCounts = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM tasks {$where} GROUP BY status",
            $params
        );

        $stats = [
            'pending' => 0,
            'running' => 0,
            'completed' => 0,
            'failed' => 0,
            'cancelled' => 0,
            'total' => 0,
            'total_tokens' => 0,
            'avg_execution_time' => 0,
        ];

        foreach ($statusCounts as $row) {
            $stats[$row['status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }

        $tokenResult = $this->db->fetch(
            "SELECT COALESCE(SUM(tokens_used), 0) as total_tokens, COALESCE(AVG(execution_time), 0) as avg_time FROM tasks {$where}",
            $params
        );

        $stats['total_tokens'] = (int) $tokenResult['total_tokens'];
        $stats['avg_execution_time'] = round((float) $tokenResult['avg_time'], 2);

        return $stats;
    }

    public function cancel(int $id): bool
    {
        $task = $this->findById($id);
        if (!$task) {
            return false;
        }

        if (!in_array($task['status'], ['pending', 'running'])) {
            return false;
        }

        $this->updateStatus($id, 'cancelled');
        return true;
    }

    public function retry(int $id): ?int
    {
        $task = $this->findById($id);
        if (!$task) {
            return null;
        }

        // Create a new task with the same input
        $newTaskId = $this->create([
            'user_id' => $task['user_id'],
            'provider_id' => $task['provider_id'],
            'title' => $task['title'] . ' (Retry)',
            'description' => $task['description'],
            'input_message' => $task['input_message'],
            'status' => 'pending',
        ]);

        return $newTaskId;
    }

    public function countByUserId(int $userId): int
    {
        $result = $this->db->fetch('SELECT COUNT(*) as cnt FROM tasks WHERE user_id = ?', [$userId]);
        return (int) $result['cnt'];
    }

    public function countByStatus(string $status): int
    {
        $result = $this->db->fetch('SELECT COUNT(*) as cnt FROM tasks WHERE status = ?', [$status]);
        return (int) $result['cnt'];
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT t.*, p.name as provider_name, p.provider_type, u.username 
                FROM tasks t 
                LEFT JOIN llm_providers p ON t.provider_id = p.id 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND t.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= ' AND t.user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (t.title LIKE ? OR u.username LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY t.created_at DESC';

        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int) $filters['limit'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function deleteByUserId(int $userId): int
    {
        return $this->db->delete('tasks', 'user_id = ?', [$userId]);
    }

    public function getDailyCounts(?int $userId = null, int $days = 30): array
    {
        $where = '';
        $params = [];

        if ($userId !== null) {
            $where = 'WHERE user_id = ?';
            $params = [$userId];
        }

        return $this->db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as count, 
                    SUM(tokens_used) as tokens, AVG(execution_time) as avg_time 
             FROM tasks {$where} 
             GROUP BY DATE(created_at) 
             ORDER BY DATE(created_at) DESC LIMIT ?",
            array_merge($params, [$days])
        );
    }
}
