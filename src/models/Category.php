<?php

// Model Category thao tác với bảng categories
class Category
{
    public $id;
    public $name;
    public $description;
    public $status;

    public function __construct(array $data = [])
    {
        $this->id          = $data['id'] ?? null;
        $this->name        = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->status      = $data['status'] ?? 1;
    }

    /**
     * Lấy toàn bộ danh mục (mặc định chỉ lấy status = 1)
     */
    public static function all($includeInactive = false): array
    {
        $pdo = getDB();
        if ($pdo === null) {
            return [];
        }

        if ($includeInactive) {
            $stmt = $pdo->query('SELECT * FROM categories ORDER BY created_at DESC');
        } else {
            $stmt = $pdo->prepare('SELECT * FROM categories WHERE status = 1 ORDER BY created_at DESC');
            $stmt->execute();
        }

        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Category($row), $rows);
    }

    /**
     * Tìm theo id
     */
    public static function find($id): ?Category
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new Category($row) : null;
    }

    /**
     * Kiểm tra tên có bị trùng không (case-insensitive)
     */
    public static function existsByName(string $name, ?int $excludeId = null): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        $sql = 'SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(:name)';
        $params = [':name' => $name];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Lưu (insert/update)
     */
    public function save(): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($this->id === null) {
            // Insert
            $stmt = $pdo->prepare(
                'INSERT INTO categories (name, description, status) VALUES (:name, :description, :status)'
            );
            $ok = $stmt->execute([
                ':name'        => $this->name,
                ':description' => $this->description,
                ':status'      => $this->status,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;
        }

        // Update
        $stmt = $pdo->prepare(
            'UPDATE categories SET name = :name, description = :description, status = :status WHERE id = :id'
        );
        return $stmt->execute([
            ':name'        => $this->name,
            ':description' => $this->description,
            ':status'      => $this->status,
            ':id'          => $this->id,
        ]);
    }

    /**
     * Xóa mềm: set status = 0
     */
    public function softDelete(): bool
    {
        $this->status = 0;
        return $this->save();
    }
}


