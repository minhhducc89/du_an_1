<?php
class GuideProfile
{
    public $id;
    public $user_id;
    public $birthdate;
    public $avatar;
    public $phone;
    public $certificate;
    public $languages;
    public $experience;
    public $history;
    public $rating;
    public $health_status;
    public $group_type;
    public $speciality;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id            = $data['id'] ?? null;
        $this->user_id       = $data['user_id'] ?? null;
        $this->birthdate     = $data['birthdate'] ?? null;
        $this->avatar        = $data['avatar'] ?? null;
        $this->phone         = $data['phone'] ?? null;
        $this->certificate   = $data['certificate'] ?? null;
        $this->languages     = $data['languages'] ?? null;
        $this->experience    = $data['experience'] ?? null;
        $this->history       = $data['history'] ?? null;
        $this->rating        = $data['rating'] ?? null;
        $this->health_status = $data['health_status'] ?? null;
        $this->group_type    = $data['group_type'] ?? null;
        $this->speciality    = $data['speciality'] ?? null;
        $this->created_at    = $data['created_at'] ?? null;
        $this->updated_at    = $data['updated_at'] ?? null;
    }

    public static function all(): array
    {
        $pdo = getDB();
        if ($pdo === null) {
            return [];
        }

        $sql = "
            SELECT gp.*, u.name AS user_name, u.email AS user_email
            FROM guide_profiles gp
            LEFT JOIN users u ON u.id = gp.user_id
            ORDER BY gp.created_at DESC
        ";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => new GuideProfile($row), $rows);
    }

    public static function find(int $id): ?GuideProfile
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM guide_profiles WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new GuideProfile($row) : null;
    }

    public static function findByUserId(int $userId): ?GuideProfile
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM guide_profiles WHERE user_id = :user_id LIMIT 1');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();

        return $row ? new GuideProfile($row) : null;
    }

    public function save(): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($this->id === null) {
            $stmt = $pdo->prepare("
                INSERT INTO guide_profiles
                  (user_id, birthdate, avatar, phone, certificate, languages, experience, history,
                   rating, health_status, group_type, speciality)
                VALUES
                  (:user_id, :birthdate, :avatar, :phone, :certificate, :languages, :experience, :history,
                   :rating, :health_status, :group_type, :speciality)
            ");
            $ok = $stmt->execute([
                ':user_id'       => $this->user_id,
                ':birthdate'     => $this->birthdate,
                ':avatar'        => $this->avatar,
                ':phone'         => $this->phone,
                ':certificate'   => $this->certificate,
                ':languages'     => $this->languages,
                ':experience'    => $this->experience,
                ':history'       => $this->history,
                ':rating'        => $this->rating,
                ':health_status' => $this->health_status,
                ':group_type'    => $this->group_type,
                ':speciality'    => $this->speciality,
            ]);
            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;
        }

        $stmt = $pdo->prepare("
            UPDATE guide_profiles
            SET user_id = :user_id,
                birthdate = :birthdate,
                avatar = :avatar,
                phone = :phone,
                certificate = :certificate,
                languages = :languages,
                experience = :experience,
                history = :history,
                rating = :rating,
                health_status = :health_status,
                group_type = :group_type,
                speciality = :speciality
            WHERE id = :id
        ");
        return $stmt->execute([
            ':user_id'       => $this->user_id,
            ':birthdate'     => $this->birthdate,
            ':avatar'        => $this->avatar,
            ':phone'         => $this->phone,
            ':certificate'   => $this->certificate,
            ':languages'     => $this->languages,
            ':experience'    => $this->experience,
            ':history'       => $this->history,
            ':rating'        => $this->rating,
            ':health_status' => $this->health_status,
            ':group_type'    => $this->group_type,
            ':speciality'    => $this->speciality,
            ':id'            => $this->id,
        ]);
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

        // Xóa avatar nếu có
        if ($this->avatar) {
            $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'guides';
            $filePath = $uploadDir . DIRECTORY_SEPARATOR . $this->avatar;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $stmt = $pdo->prepare('DELETE FROM guide_profiles WHERE id = :id');
        return $stmt->execute([':id' => $this->id]);
    }
}


