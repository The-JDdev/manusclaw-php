<?php

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByUsername(string $username): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function create(array $data): int
    {
        $fields = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'] ?? 'user',
            'avatar' => $data['avatar'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ];

        return $this->db->insert('users', $fields);
    }

    public function authenticate(string $username, string $password): array|false
    {
        $user = $this->db->fetch(
            'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
            [$username, $username]
        );

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Remove password_hash from returned data
        unset($user['password_hash']);
        return $user;
    }

    public function update(int $id, array $data): int
    {
        $allowed = ['username', 'email', 'role', 'avatar', 'is_active'];
        $fields = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[$key] = $data[$key];
            }
        }

        if (empty($fields)) {
            return 0;
        }

        $fields['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('users', $fields, 'id = ?', [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete('users', 'id = ?', [$id]);
    }

    public function getAll(): array
    {
        return $this->db->fetchAll('SELECT id, username, email, role, avatar, is_active, created_at, updated_at FROM users ORDER BY created_at DESC');
    }

    public function count(): int
    {
        $result = $this->db->fetch('SELECT COUNT(*) as cnt FROM users');
        return (int) $result['cnt'];
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $affected = $this->db->update('users', [
            'password_hash' => $hash,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        return $affected > 0;
    }

    public function toggleActive(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $affected = $this->db->update('users', [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        return $affected > 0;
    }

    public function getActiveCount(): int
    {
        $result = $this->db->fetch('SELECT COUNT(*) as cnt FROM users WHERE is_active = 1');
        return (int) $result['cnt'];
    }

    public function getAdminCount(): int
    {
        $result = $this->db->fetch('SELECT COUNT(*) as cnt FROM users WHERE role = \'admin\'');
        return (int) $result['cnt'];
    }

    public function search(string $query): array
    {
        $like = '%' . $query . '%';
        return $this->db->fetchAll(
            'SELECT id, username, email, role, avatar, is_active, created_at, updated_at FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC',
            [$like, $like]
        );
    }
}
