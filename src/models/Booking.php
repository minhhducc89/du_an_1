<?php

// Model Booking thao tác với bảng bookings
class Booking
{
    public $id;
    public $tour_id;
    public $created_by;
    public $assigned_guide_id;
    public $status;
    public $start_date;
    public $end_date;
    public $schedule_detail;
    public $service_detail;
    public $diary;
    public $lists_file;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        $this->id               = $data['id'] ?? null;
        $this->tour_id          = $data['tour_id'] ?? null;
        $this->created_by       = $data['created_by'] ?? null;
        $this->assigned_guide_id= $data['assigned_guide_id'] ?? null;
        $this->status           = $data['status'] ?? null;
        $this->start_date       = $data['start_date'] ?? null;
        $this->end_date         = $data['end_date'] ?? null;
        $this->schedule_detail  = $data['schedule_detail'] ?? null;
        $this->service_detail   = $data['service_detail'] ?? null;
        $this->diary            = $data['diary'] ?? null;
        $this->lists_file       = $data['lists_file'] ?? null;
        $this->notes            = $data['notes'] ?? null;
        $this->created_at       = $data['created_at'] ?? null;
        $this->updated_at       = $data['updated_at'] ?? null;
    }

    public static function find(int $id): ?Booking
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? new Booking($row) : null;
    }

    /**
     * Xóa booking (hard delete)
     * Lưu ý: 
     * - booking_status_logs cần xóa thủ công trước khi xóa booking (không có CASCADE)
     * - tour_guests có thể tự động xóa nhờ CASCADE, nhưng xóa thủ công để đảm bảo
     */
    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $pdo = getDB();
        if ($pdo === null) {
            return false;
        }

        // Bắt đầu transaction để đảm bảo tính nhất quán
        $pdo->beginTransaction();

        try {
            // Xóa tất cả các log trạng thái liên quan đến booking này
            $stmtLog = $pdo->prepare('DELETE FROM booking_status_logs WHERE booking_id = :booking_id');
            $stmtLog->execute([':booking_id' => $this->id]);

            // Xóa tất cả các khách đoàn liên quan (nếu không có CASCADE)
            // Kiểm tra xem bảng tour_guests có tồn tại không
            try {
                $stmtGuests = $pdo->prepare('DELETE FROM tour_guests WHERE booking_id = :booking_id');
                $stmtGuests->execute([':booking_id' => $this->id]);
            } catch (PDOException $e) {
                // Bảng tour_guests có thể không tồn tại hoặc đã có CASCADE, bỏ qua
            }

            // Xóa booking
            $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = :id');
            $stmt->execute([':id' => $this->id]);

            // Commit transaction
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $pdo->rollBack();
            return false;
        }
    }
}


?>