<?php

// Model User đại diện cho thực thể người dùng trong hệ thống
class User
{
    // Các thuộc tính của User
    public $id;
    public $name;
    public $email;
    public $role;
    public $status;

    // Constructor để khởi tạo thực thể User
    public function __construct($data = [])
    {
        // Nếu truyền vào mảng dữ liệu thì gán vào các thuộc tính
        if (is_array($data)) {
            $this->id = $data['id'] ?? null;
            $this->name = $data['name'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->role = $data['role'] ?? 'huong_dan_vien';
            $this->status = $data['status'] ?? 1;
        } else {
            // Nếu truyền vào string thì coi như tên (tương thích với code cũ)
            $this->name = $data;
        }
    }

    // Trả về tên người dùng để hiển thị
    public function getName()
    {
        return $this->name;
    }

    // Kiểm tra xem user có phải là admin không
    // @return bool true nếu là admin, false nếu không
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Kiểm tra xem user có phải là hướng dẫn viên không
    // @return bool true nếu là hướng dẫn viên, false nếu không
    public function isGuide()
    {
        // Hỗ trợ cả giá trị cũ 'huong_dan_vien' và giá trị trong database 'guide'
        return in_array($this->role, ['huong_dan_vien', 'guide'], true);
    }

    public static function find(int $id): ?User
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new User($row) : null;
    }

    public function save(string $password = null): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($this->id === null) {
            // Tạo mới
            if ($password === null) {
                return false; // Phải có password khi tạo mới
            }
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password, role, status)
                 VALUES (:name, :email, :password, :role, :status)'
            );
            $ok = $stmt->execute([
                ':name'     => $this->name,
                ':email'    => $this->email,
                ':password' => $passwordHash,
                ':role'     => $this->role,
                ':status'   => $this->status,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;
        }

        // Cập nhật
        if ($password !== null) {
            // Cập nhật cả password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     password = :password,
                     role = :role,
                     status = :status
                 WHERE id = :id'
            );
            return $stmt->execute([
                ':name'     => $this->name,
                ':email'    => $this->email,
                ':password' => $passwordHash,
                ':role'     => $this->role,
                ':status'   => $this->status,
                ':id'       => $this->id,
            ]);
        } else {
            // Không cập nhật password
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     role = :role,
                     status = :status
                 WHERE id = :id'
            );
            return $stmt->execute([
                ':name'   => $this->name,
                ':email'  => $this->email,
                ':role'   => $this->role,
                ':status' => $this->status,
                ':id'     => $this->id,
            ]);
        }
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        // Kiểm tra xem user có đang được sử dụng không
        // (có booking, guide_profile, etc.)
        // Tạm thời cho phép xóa, có thể thêm logic kiểm tra sau

        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $this->id]);
    }

    public static function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM users WHERE email = :email AND id != :exclude_id');
            $stmt->execute([':email' => $email, ':exclude_id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
        }
        
        $row = $stmt->fetch();
        return (int)($row['count'] ?? 0) > 0;
    }
}
