<?php

class ActivityLog
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(?int $userId, string $action, string $details = '', string $ip = ''): int
    {
        return $this->db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip,
        ]);
    }

    public function getRecent(int $limit = 50): array
    {
        return $this->db->fetchAll(
            'SELECT a.*, u.username 
             FROM activity_logs a 
             LEFT JOIN users u ON a.user_id = u.id 
             ORDER BY a.created_at DESC LIMIT ?',
            [$limit]
        );
    }

    public function getByUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            'SELECT a.*, u.username 
             FROM activity_logs a 
             LEFT JOIN users u ON a.user_id = u.id 
             WHERE a.user_id = ? 
             ORDER BY a.created_at DESC LIMIT ?',
            [$userId, $limit]
        );
    }

    public function getByAction(string $action, int $limit = 50): array
    {
        return $this->db->fetchAll(
            'SELECT a.*, u.username 
             FROM activity_logs a 
             LEFT JOIN users u ON a.user_id = u.id 
             WHERE a.action = ? 
             ORDER BY a.created_at DESC LIMIT ?',
            [$action, $limit]
        );
    }

    public function countByDateRange(string $from, string $to): int
    {
        $result = $this->db->fetch(
            'SELECT COUNT(*) as cnt FROM activity_logs WHERE created_at >= ? AND created_at <= ?',
            [$from . ' 00:00:00', $to . ' 23:59:59']
        );
        return (int) $result['cnt'];
    }

    public function cleanOld(int $days = 30): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->db->delete('activity_logs', 'created_at < ?', [$cutoff]);
    }
}
