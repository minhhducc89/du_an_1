<?php

class GuideScheduleController
{
    // Lịch trình tour của guide
    public function index(): void
    {
        requireGuideOrAdmin();

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Lấy danh sách booking được phân công cho guide này (status = 2: Đã cọc, 3: Hoàn tất)
        $sql = "
            SELECT b.*, t.name AS tour_name, ts.name AS status_name,
                   u.name AS customer_name, u.email AS customer_email
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN tour_statuses ts ON ts.id = b.status
            LEFT JOIN users u ON u.id = b.created_by
            WHERE b.assigned_guide_id = :guide_id
              AND b.status IN (2, 3)
            ORDER BY b.start_date ASC, b.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':guide_id' => $currentUser->id]);
        $schedules = $stmt->fetchAll();

        // Decode service_detail để lấy số khách
        foreach ($schedules as &$s) {
            if (!empty($s['service_detail'])) {
                $decoded = json_decode($s['service_detail'], true);
                if (is_array($decoded)) {
                    $s['total_guests'] = $decoded['total_guests'] ?? 0;
                }
            }
        }
        unset($s);

        ob_start();
        include view_path('guide.schedule.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Lịch trình tour',
            'pageTitle'  => 'Lịch trình tour của tôi',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Lịch trình', 'url' => BASE_URL . '?act=guide-schedule', 'active' => true],
            ],
        ]);
    }

    // Lịch sử tour đã làm
    public function history(): void
    {
        requireGuideOrAdmin();

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Lấy danh sách booking đã hoàn thành (status = 3)
        $sql = "
            SELECT b.*, t.name AS tour_name, ts.name AS status_name
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN tour_statuses ts ON ts.id = b.status
            WHERE b.assigned_guide_id = :guide_id
              AND b.status = 3
            ORDER BY b.end_date DESC, b.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':guide_id' => $currentUser->id]);
        $history = $stmt->fetchAll();

        ob_start();
        include view_path('guide.schedule.history');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Lịch sử tour',
            'pageTitle'  => 'Lịch sử tour đã làm',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Lịch trình', 'url' => BASE_URL . '?act=guide-schedule'],
                ['label' => 'Lịch sử', 'url' => BASE_URL . '?act=guide-history', 'active' => true],
            ],
        ]);
    }

    // Điểm danh & Check-in
    public function checkin(): void
    {
        requireGuideOrAdmin();

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = (int)($_GET['id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Kiểm tra booking thuộc về guide này
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
        $stmt->execute([':id' => $bookingId, ':guide_id' => $currentUser->id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        // Lấy danh sách khách
        $guests = TourGuest::allByBooking($bookingId);

        // Lấy thông tin tour
        $tour = null;
        if ($booking['tour_id']) {
            $stmt = $pdo->prepare('SELECT * FROM tours WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $booking['tour_id']]);
            $tour = $stmt->fetch();
        }

        ob_start();
        include view_path('guide.checkin.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Điểm danh & Check-in',
            'pageTitle'  => 'Điểm danh khách đoàn',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Lịch trình', 'url' => BASE_URL . '?act=guide-schedule'],
                ['label' => 'Điểm danh', 'url' => BASE_URL . '?act=guide-checkin&id=' . $bookingId, 'active' => true],
            ],
        ]);
    }

    // Lưu điểm danh
    public function saveCheckin(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $attendance = $_POST['attendance'] ?? []; // Array of guest IDs

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Kiểm tra booking thuộc về guide này
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
        $stmt->execute([':id' => $bookingId, ':guide_id' => $currentUser->id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        // Lấy tất cả khách của booking
        $guests = TourGuest::allByBooking($bookingId);

        // Cập nhật trạng thái điểm danh (lưu vào JSON trong schedule_detail hoặc tạo bảng riêng)
        // Tạm thời lưu vào schedule_detail dạng JSON
        $attendanceData = [];
        foreach ($guests as $guest) {
            $attendanceData[$guest->id] = in_array((string)$guest->id, $attendance, true) ? 'present' : 'absent';
        }

        $scheduleDetail = json_decode($booking['schedule_detail'] ?? '{}', true);
        if (!is_array($scheduleDetail)) {
            $scheduleDetail = [];
        }
        $scheduleDetail['attendance'] = $attendanceData;
        $scheduleDetail['checkin_time'] = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('UPDATE bookings SET schedule_detail = :schedule_detail WHERE id = :id');
        $stmt->execute([
            ':schedule_detail' => json_encode($scheduleDetail, JSON_UNESCAPED_UNICODE),
            ':id' => $bookingId,
        ]);

        header('Location: ' . BASE_URL . '?act=guide-checkin&id=' . $bookingId . '&success=1');
        exit;
    }

    // Nhật ký tour
    public function diary(): void
    {
        requireGuideOrAdmin();

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = (int)($_GET['id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Kiểm tra booking thuộc về guide này
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
        $stmt->execute([':id' => $bookingId, ':guide_id' => $currentUser->id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        // Decode diary nếu có
        $diaryEntries = [];
        if (!empty($booking['diary'])) {
            $decoded = json_decode($booking['diary'], true);
            if (is_array($decoded) && isset($decoded['entries'])) {
                $diaryEntries = $decoded['entries'];
            }
        }

        ob_start();
        include view_path('guide.diary.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Nhật ký tour',
            'pageTitle'  => 'Nhật ký tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Lịch trình', 'url' => BASE_URL . '?act=guide-schedule'],
                ['label' => 'Nhật ký', 'url' => BASE_URL . '?act=guide-diary&id=' . $bookingId, 'active' => true],
            ],
        ]);
    }

    // Lưu nhật ký
    public function saveDiary(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $content   = trim($_POST['content'] ?? '');
        $cost      = $_POST['cost'] !== '' ? (float)$_POST['cost'] : null;

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Kiểm tra booking thuộc về guide này
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
        $stmt->execute([':id' => $bookingId, ':guide_id' => $currentUser->id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        $errors = [];
        if ($title === '') {
            $errors[] = 'Tiêu đề không được để trống.';
        }
        if ($content === '') {
            $errors[] = 'Nội dung không được để trống.';
        }

        if (!empty($errors)) {
            header('Location: ' . BASE_URL . '?act=guide-diary&id=' . $bookingId . '&error=' . urlencode(implode(', ', $errors)));
            exit;
        }

        // Xử lý upload ảnh
        $images = [];
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'diary';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['images']['name'] as $idx => $name) {
                if ($_FILES['images']['error'][$idx] !== UPLOAD_ERR_OK) continue;
                $tmpName = $_FILES['images']['tmp_name'][$idx];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                $newName = uniqid('diary_', true) . ($safeExt ? '.' . $safeExt : '');
                $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                if (move_uploaded_file($tmpName, $dest)) {
                    $images[] = $newName;
                }
            }
        }

        // Lấy diary hiện tại
        $diary = ['entries' => []];
        if (!empty($booking['diary'])) {
            $decoded = json_decode($booking['diary'], true);
            if (is_array($decoded) && isset($decoded['entries'])) {
                $diary = $decoded;
            }
        }

        // Thêm entry mới
        $newEntry = [
            'id'      => time(),
            'title'   => $title,
            'content' => $content,
            'cost'    => $cost,
            'images'  => $images,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $diary['entries'][] = $newEntry;

        // Cập nhật diary
        $stmt = $pdo->prepare('UPDATE bookings SET diary = :diary WHERE id = :id');
        $stmt->execute([
            ':diary' => json_encode($diary, JSON_UNESCAPED_UNICODE),
            ':id'    => $bookingId,
        ]);

        header('Location: ' . BASE_URL . '?act=guide-diary&id=' . $bookingId . '&success=1');
        exit;
    }

    // Xem chi tiết lịch trình tour
    public function showDetail(): void
    {
        requireGuideOrAdmin();

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = (int)($_GET['id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Kiểm tra booking thuộc về guide này
        $sql = "
            SELECT b.*, t.name AS tour_name, t.description AS tour_description, 
                   ts.name AS status_name, u.name AS created_by_name
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN tour_statuses ts ON ts.id = b.status
            LEFT JOIN users u ON u.id = b.created_by
            WHERE b.id = :id AND b.assigned_guide_id = :guide_id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $bookingId, ':guide_id' => $currentUser->id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        // Decode service_detail
        $service = [];
        if (!empty($booking['service_detail'])) {
            $decoded = json_decode($booking['service_detail'], true);
            if (is_array($decoded)) {
                $service = $decoded;
            }
        }

        // Lấy danh sách khách đoàn
        $guests = TourGuest::allByBooking($bookingId);

        // Lấy thông tin tour chi tiết
        $tour = null;
        if ($booking['tour_id']) {
            $stmt = $pdo->prepare('SELECT * FROM tours WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $booking['tour_id']]);
            $tour = $stmt->fetch();
        }

        ob_start();
        include view_path('guide.schedule.detail');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Chi tiết lịch trình tour',
            'pageTitle'  => 'Chi tiết lịch trình tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Lịch trình', 'url' => BASE_URL . '?act=guide-schedule'],
                ['label' => 'Chi tiết', 'url' => BASE_URL . '?act=guide-schedule-detail&id=' . $bookingId, 'active' => true],
            ],
        ]);
    }

    // Form cập nhật yêu cầu đặc biệt
    public function editSpecialRequirements(): void
    {
        requireGuideOrAdmin();

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = (int)($_GET['id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Kiểm tra booking thuộc về guide này
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
        $stmt->execute([':id' => $bookingId, ':guide_id' => $currentUser->id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        // Decode service_detail để lấy yêu cầu đặc biệt hiện tại
        $service = [];
        $currentSpecialReqs = '';
        if (!empty($booking['service_detail'])) {
            $decoded = json_decode($booking['service_detail'], true);
            if (is_array($decoded)) {
                $service = $decoded;
                $currentSpecialReqs = $service['special_requirements'] ?? '';
            }
        }

        ob_start();
        include view_path('guide.schedule.edit_special_requirements');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Cập nhật yêu cầu đặc biệt',
            'pageTitle'  => 'Cập nhật yêu cầu đặc biệt',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Lịch trình', 'url' => BASE_URL . '?act=guide-schedule'],
                ['label' => 'Chi tiết', 'url' => BASE_URL . '?act=guide-schedule-detail&id=' . $bookingId],
                ['label' => 'Cập nhật yêu cầu đặc biệt', 'url' => BASE_URL . '?act=guide-edit-special-requirements&id=' . $bookingId, 'active' => true],
            ],
        ]);
    }

    // Lưu cập nhật yêu cầu đặc biệt
    public function saveSpecialRequirements(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $specialRequirements = trim($_POST['special_requirements'] ?? '');

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Kiểm tra booking thuộc về guide này
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
        $stmt->execute([':id' => $bookingId, ':guide_id' => $currentUser->id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=guide-schedule');
            exit;
        }

        // Lấy service_detail hiện tại
        $serviceDetail = [];
        if (!empty($booking['service_detail'])) {
            $decoded = json_decode($booking['service_detail'], true);
            if (is_array($decoded)) {
                $serviceDetail = $decoded;
            }
        }

        // Cập nhật yêu cầu đặc biệt
        $serviceDetail['special_requirements'] = $specialRequirements;

        // Lưu lại vào database
        $stmt = $pdo->prepare('UPDATE bookings SET service_detail = :service_detail WHERE id = :id');
        $stmt->execute([
            ':service_detail' => json_encode($serviceDetail, JSON_UNESCAPED_UNICODE),
            ':id' => $bookingId,
        ]);

        header('Location: ' . BASE_URL . '?act=guide-schedule-detail&id=' . $bookingId . '&success=1');
        exit;
    }
}

