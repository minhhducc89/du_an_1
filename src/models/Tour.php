<?php
class Tour
{
    public $id;
    public $name;
    public $description;
    public $category_id;
    public $schedule;
    public $images;
    public $prices;
    public $policies;
    public $suppliers;
    public $price;
    public $duration;
    public $max_guests;
    public $status;

    public function __construct(array $data = [])
    {
        $this->id          = $data['id'] ?? null;
        $this->name        = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->category_id = $data['category_id'] ?? null;
        $this->schedule    = $data['schedule'] ?? null;
        $this->images      = $data['images'] ?? null;
        $this->prices      = $data['prices'] ?? null;
        $this->policies    = $data['policies'] ?? null;
        $this->suppliers   = $data['suppliers'] ?? null;
        $this->price       = $data['price'] ?? null;
        $this->duration    = $data['duration'] ?? null;
        $this->max_guests  = $data['max_guests'] ?? null;
        $this->status      = $data['status'] ?? 1;
    }
     public static function all(): array
    {
        $pdo = getDB();
        if ($pdo === null) {
            return [];
        }

        $stmt = $pdo->query('SELECT * FROM tours ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => new Tour($row), $rows);
    }

    public static function find($id): ?Tour
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM tours WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new Tour($row) : null;
    }

    public function save(): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($this->id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO tours (name, description, category_id, schedule, images, prices, policies, suppliers, price, status, duration, max_guests)
                 VALUES (:name, :description, :category_id, :schedule, :images, :prices, :policies, :suppliers, :price, :status, :duration, :max_guests)'
            );
            $ok = $stmt->execute([
                ':name'        => $this->name,
                ':description' => $this->description,
                ':category_id' => $this->category_id,
                ':schedule'    => $this->schedule,
                ':images'      => $this->images,
                ':prices'      => $this->prices,
                ':policies'    => $this->policies,
                ':suppliers'   => $this->suppliers,
                ':price'       => $this->price,
                ':status'      => $this->status,
                ':duration'    => $this->duration,
                ':max_guests'  => $this->max_guests,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;
        }

        $stmt = $pdo->prepare(
            'UPDATE tours
             SET name = :name,
                 description = :description,
                 category_id = :category_id,
                 schedule = :schedule,
                 images = :images,
                 prices = :prices,
                 policies = :policies,
                 suppliers = :suppliers,
                 price = :price,
                 status = :status,
                 duration = :duration,
                 max_guests = :max_guests
             WHERE id = :id'
        );
        return $stmt->execute([
            ':name'        => $this->name,
            ':description' => $this->description,
            ':category_id' => $this->category_id,
            ':schedule'    => $this->schedule,
            ':images'      => $this->images,
            ':prices'      => $this->prices,
            ':policies'    => $this->policies,
            ':suppliers'   => $this->suppliers,
            ':price'       => $this->price,
            ':status'      => $this->status,
            ':duration'    => $this->duration,
            ':max_guests'  => $this->max_guests,
            ':id'          => $this->id,
        ]);
    }

     public function updateStatus(int $status): bool
    {
        $this->status = $status;
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

        // Xóa ảnh nếu có
        if ($this->images) {
            $decoded = json_decode($this->images, true);
            if (is_array($decoded)) {
                $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tours';
                foreach ($decoded as $img) {
                    $filePath = $uploadDir . DIRECTORY_SEPARATOR . $img;
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
        }

        $stmt = $pdo->prepare('DELETE FROM tours WHERE id = :id');
        return $stmt->execute([':id' => $this->id]);
    }
}
?>