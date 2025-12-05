<?php
class TourGuest
{
    public $id;
    public $booking_id;
    public $fullname;
    public $dob;
    public $gender;
    public $passport_number;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id             = $data['id'] ?? null;
        $this->booking_id     = $data['booking_id'] ?? null;
        $this->fullname       = $data['fullname'] ?? '';
        $this->dob            = $data['dob'] ?? null;
        $this->gender         = $data['gender'] ?? null;
        $this->passport_number = $data['passport_number'] ?? null;
        $this->created_at     = $data['created_at'] ?? null;
        $this->updated_at     = $data['updated_at'] ?? null;
    }

    public static function find(int $id): ?TourGuest
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM tour_guests WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new TourGuest($row) : null;
    }

    public static function allByBooking(int $bookingId): array
    {
        $pdo = getDB();
        if ($pdo === null) {
            return [];
        }

        $stmt = $pdo->prepare('SELECT * FROM tour_guests WHERE booking_id = :booking_id ORDER BY id ASC');
        $stmt->execute([':booking_id' => $bookingId]);
        $rows = $stmt->fetchAll();

        $guests = [];
        foreach ($rows as $row) {
            $guests[] = new TourGuest($row);
        }

        return $guests;
    }

    public function save(): bool
    {
        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        if ($this->id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO tour_guests (booking_id, fullname, dob, gender, passport_number)
                 VALUES (:booking_id, :fullname, :dob, :gender, :passport_number)'
            );
            $ok = $stmt->execute([
                ':booking_id'     => $this->booking_id,
                ':fullname'       => $this->fullname,
                ':dob'            => $this->dob ?: null,
                ':gender'         => $this->gender ?: null,
                ':passport_number'=> $this->passport_number ?: null,
            ]);

            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;
        }

        $stmt = $pdo->prepare(
            'UPDATE tour_guests
             SET booking_id = :booking_id,
                 fullname = :fullname,
                 dob = :dob,
                 gender = :gender,
                 passport_number = :passport_number
             WHERE id = :id'
        );
        return $stmt->execute([
            ':booking_id'     => $this->booking_id,
            ':fullname'       => $this->fullname,
            ':dob'            => $this->dob ?: null,
            ':gender'         => $this->gender ?: null,
            ':passport_number'=> $this->passport_number ?: null,
            ':id'             => $this->id,
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

        $stmt = $pdo->prepare('DELETE FROM tour_guests WHERE id = :id');
        return $stmt->execute([':id' => $this->id]);
    }
}
?>