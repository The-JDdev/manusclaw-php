<?php

class Setting
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->db->fetch('SELECT * FROM settings WHERE key_name = ?', [$key]);

        if (!$row) {
            return $default;
        }

        // Return value based on which column has data
        if ($row['value_text'] !== null) {
            return $row['value_text'];
        }

        if ($row['value_int'] !== null) {
            return (int) $row['value_int'];
        }

        if ($row['value_bool'] !== null) {
            return (bool) $row['value_bool'];
        }

        return $default;
    }

    public function set(string $key, mixed $value, string $type = 'text'): bool
    {
        $existing = $this->db->fetch('SELECT id FROM settings WHERE key_name = ?', [$key]);

        $data = [
            'value_text' => null,
            'value_int' => null,
            'value_bool' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        switch ($type) {
            case 'text':
                $data['value_text'] = (string) $value;
                break;
            case 'int':
            case 'integer':
                $data['value_int'] = (int) $value;
                break;
            case 'bool':
            case 'boolean':
                $data['value_bool'] = $value ? 1 : 0;
                break;
            default:
                $data['value_text'] = (string) $value;
        }

        if ($existing) {
            $affected = $this->db->update('settings', $data, 'key_name = ?', [$key]);
            return $affected > 0;
        }

        $data['key_name'] = $key;
        $this->db->insert('settings', $data);
        return true;
    }

    public function getAll(): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM settings ORDER BY key_name ASC');
        $settings = [];

        foreach ($rows as $row) {
            $value = $row['value_text'] ?? $row['value_int'] ?? $row['value_bool'];
            $type = 'text';
            if ($row['value_int'] !== null) {
                $type = 'int';
            } elseif ($row['value_bool'] !== null) {
                $type = 'bool';
            }

            $settings[$row['key_name']] = [
                'value' => $value,
                'type' => $type,
                'updated_at' => $row['updated_at'],
            ];
        }

        return $settings;
    }

    public function delete(string $key): bool
    {
        $affected = $this->db->delete('settings', 'key_name = ?', [$key]);
        return $affected > 0;
    }

    public function getAsFlatArray(): array
    {
        $all = $this->getAll();
        $flat = [];
        foreach ($all as $key => $data) {
            $flat[$key] = $data['value'];
        }
        return $flat;
    }
}
