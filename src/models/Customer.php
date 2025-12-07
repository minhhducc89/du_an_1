<?php

// Model Customer thao tác với bảng customers
class Customer
{
    public $id;
    public $name;
    public $phone;
    public $email;
    public $address;
    public $company;
    public $tax_code;
    public $notes;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id       = $data['id'] ?? null;
        $this->name     = $data['name'] ?? '';
        $this->phone    = $data['phone'] ?? '';
        $this->email    = $data['email'] ?? null;
        $this->address  = $data['address'] ?? null;
        $this->company  = $data['company'] ?? null;
        $this->tax_code = $data['tax_code'] ?? null;
        $this->notes    = $data['notes'] ?? null;
        $this->status   = $data['status'] ?? 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public static function all(bool $activeOnly = false): array
    {
        $pdo = getDB();
        if ($pdo === null) {
            return [];
        }

        $sql = 'SELECT * FROM customers';
        if ($activeOnly) {
            $sql .= ' WHERE status = 1';
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new Customer($row), $rows);
    }

    public static function find(int $id): ?Customer
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new Customer($row) : null;
    }

    public static function existsByPhone(string $phone, ?int $excludeId = null): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM customers WHERE phone = :phone AND id != :exclude_id');
            $stmt->execute([':phone' => $phone, ':exclude_id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM customers WHERE phone = :phone');
            $stmt->execute([':phone' => $phone]);
        }

        $row = $stmt->fetch();
        return (int)($row['count'] ?? 0) > 0;
    }

    public static function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        if (empty($email)) {
            return false;
        }

        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM customers WHERE email = :email AND id != :exclude_id');
            $stmt->execute([':email' => $email, ':exclude_id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM customers WHERE email = :email');
            $stmt->execute([':email' => $email]);
        }

        $row = $stmt->fetch();
        return (int)($row['count'] ?? 0) > 0;
    }

    public function save(): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($this->id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO customers (name, phone, email, address, company, tax_code, notes, status)
                 VALUES (:name, :phone, :email, :address, :company, :tax_code, :notes, :status)'
            );
            $ok = $stmt->execute([
                ':name'     => $this->name,
                ':phone'    => $this->phone,
                ':email'    => $this->email ?: null,
                ':address'  => $this->address ?: null,
                ':company'  => $this->company ?: null,
                ':tax_code' => $this->tax_code ?: null,
                ':notes'    => $this->notes ?: null,
                ':status'   => $this->status,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;
        }

        $stmt = $pdo->prepare(
            'UPDATE customers
             SET name = :name,
                 phone = :phone,
                 email = :email,
                 address = :address,
                 company = :company,
                 tax_code = :tax_code,
                 notes = :notes,
                 status = :status
             WHERE id = :id'
        );
        return $stmt->execute([
            ':name'     => $this->name,
            ':phone'    => $this->phone,
            ':email'    => $this->email ?: null,
            ':address'  => $this->address ?: null,
            ':company'  => $this->company ?: null,
            ':tax_code' => $this->tax_code ?: null,
            ':notes'    => $this->notes ?: null,
            ':status'   => $this->status,
            ':id'       => $this->id,
        ]);
    }

    public function softDelete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $this->status = 0;
        return $this->save();
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

        // Kiểm tra xem customer có đang được sử dụng trong booking không
        // Tìm booking có service_detail chứa phone của customer này
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE service_detail LIKE :pattern');
        $pattern = '%"phone":"' . str_replace('"', '\\"', $this->phone) . '"%';
        $stmt->execute([':pattern' => $pattern]);
        $row = $stmt->fetch();
        $bookingCount = (int)($row['count'] ?? 0);

        if ($bookingCount > 0) {
            // Có booking đang sử dụng, chỉ soft delete
            return $this->softDelete();
        }

        // Không có booking, hard delete
        $stmt = $pdo->prepare('DELETE FROM customers WHERE id = :id');
        return $stmt->execute([':id' => $this->id]);
    }
}

