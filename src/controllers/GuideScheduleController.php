<?php

<<<<<<< HEAD
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
=======
class GuideProfileController
{
    // Danh sách hồ sơ hướng dẫn viên
    public function index(): void
    {
        requireAdmin();
>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

<<<<<<< HEAD
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
=======
        $sql = "
            SELECT gp.*, u.name AS user_name, u.email AS user_email
            FROM guide_profiles gp
            LEFT JOIN users u ON u.id = gp.user_id
            ORDER BY gp.created_at DESC
        ";
        $stmt = $pdo->query($sql);
        $profiles = $stmt->fetchAll();

        ob_start();
        include view_path('admin.guides.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Hồ sơ hướng dẫn viên',
            'pageTitle'  => 'Hồ sơ hướng dẫn viên',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Hồ sơ HDV', 'url' => BASE_URL . '?act=guide-profiles', 'active' => true],
>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317
            ],
        ]);
    }

<<<<<<< HEAD
    // Lịch sử tour đã làm
    public function history(): void
    {
        requireGuideOrAdmin();

        $currentUser = getCurrentUser();
        if (!$currentUser || !$currentUser->isGuide()) {
            header('Location: ' . BASE_URL);
            exit;
        }
=======
    // Form tạo mới
    public function create(): void
    {
        requireAdmin();
>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

<<<<<<< HEAD
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

=======
        // Lấy danh sách user role=guide chưa có profile
        $sql = "
            SELECT u.*
            FROM users u
            WHERE u.role = 'guide'
              AND u.status = 1
              AND NOT EXISTS (
                SELECT 1 FROM guide_profiles gp WHERE gp.user_id = u.id
              )
            ORDER BY u.name
        ";
        $guides = $pdo->query($sql)->fetchAll();

        $errors = [];
        $old = [
            'user_id'      => '',
            'birthdate'    => '',
            'phone'        => '',
            'certificate'  => '',
            'languages'    => '',
            'experience'   => '',
            'rating'       => '',
            'health_status'=> '',
            'group_type'   => '',
            'speciality'   => '',
        ];

        ob_start();
        include view_path('admin.guides.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Tạo hồ sơ HDV',
            'pageTitle'  => 'Tạo hồ sơ HDV',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Hồ sơ HDV', 'url' => BASE_URL . '?act=guide-profiles'],
                ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=guide-profile-create', 'active' => true],
            ],
        ]);
    }

    // Lưu tạo mới
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=guide-profile-create');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $userId       = (int)($_POST['user_id'] ?? 0);
        $birthdate    = $_POST['birthdate'] ?? '';
        $phone        = trim($_POST['phone'] ?? '');
        $certificate  = trim($_POST['certificate'] ?? '');
        $languages    = trim($_POST['languages'] ?? '');
        $experience   = trim($_POST['experience'] ?? '');
        $rating       = $_POST['rating'] !== '' ? (float)$_POST['rating'] : null;
        $healthStatus = trim($_POST['health_status'] ?? '');
        $groupType    = trim($_POST['group_type'] ?? '');
        $speciality   = trim($_POST['speciality'] ?? '');

        $errors = [];

        if ($userId <= 0) {
            $errors[] = 'Vui lòng chọn hướng dẫn viên.';
        }
        if ($phone === '') {
            $errors[] = 'Vui lòng nhập số điện thoại.';
        }

        $old = [
            'user_id'      => $userId,
            'birthdate'    => $birthdate,
            'phone'        => $phone,
            'certificate'  => $certificate,
            'languages'    => $languages,
            'experience'   => $experience,
            'rating'       => $rating,
            'health_status'=> $healthStatus,
            'group_type'   => $groupType,
            'speciality'   => $speciality,
        ];

        // Lấy lại danh sách guide select
        $sql = "
            SELECT u.*
            FROM users u
            WHERE u.role = 'guide'
              AND u.status = 1
            ORDER BY u.name
        ";
        $guides = $pdo->query($sql)->fetchAll();

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.guides.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Tạo hồ sơ HDV',
                'pageTitle'  => 'Tạo hồ sơ HDV',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                    ['label' => 'Hồ sơ HDV', 'url' => BASE_URL . '?act=guide-profiles'],
                    ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=guide-profile-create', 'active' => true],
                ],
            ]);
            return;
        }

        // Xử lý upload avatar
        $avatarFile = null;
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'guides';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
            $newName = uniqid('guide_', true) . ($safeExt ? '.' . $safeExt : '');
            $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $avatarFile = $newName;
            }
        }

        // Languages -> JSON
        $languagesJson = null;
        if ($languages !== '') {
            $arr = array_values(array_filter(array_map('trim', preg_split('/[,;]/', $languages))));
            if (!empty($arr)) {
                $languagesJson = json_encode($arr, JSON_UNESCAPED_UNICODE);
            }
        }

        $profile = new GuideProfile([
            'user_id'       => $userId,
            'birthdate'     => $birthdate ?: null,
            'avatar'        => $avatarFile,
            'phone'         => $phone,
            'certificate'   => $certificate,
            'languages'     => $languagesJson,
            'experience'    => $experience,
            'rating'        => $rating,
            'health_status' => $healthStatus ?: null,
            'group_type'    => $groupType ?: null,
            'speciality'    => $speciality ?: null,
        ]);
        $profile->save();

        header('Location: ' . BASE_URL . '?act=guide-profiles');
        exit;
    }

    // Form sửa
    public function edit(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $profile = GuideProfile::find($id);

        if (!$profile) {
            header('Location: ' . BASE_URL . '?act=guide-profiles');
            exit;
        }

>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317
        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

<<<<<<< HEAD
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
=======
        // Lấy thông tin user
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $profile->user_id]);
        $user = $stmt->fetch();

        $errors = [];

        $languagesText = '';
        if ($profile->languages) {
            $decoded = json_decode($profile->languages, true);
            if (is_array($decoded)) {
                $languagesText = implode(', ', $decoded);
            }
        }

        $old = [
            'id'           => $profile->id,
            'user_id'      => $profile->user_id,
            'birthdate'    => $profile->birthdate,
            'phone'        => $profile->phone,
            'certificate'  => $profile->certificate,
            'languages'    => $languagesText,
            'experience'   => $profile->experience,
            'avatar'       => $profile->avatar,
            'rating'       => $profile->rating,
            'health_status'=> $profile->health_status,
            'group_type'   => $profile->group_type,
            'speciality'   => $profile->speciality,
        ];

        $guides = $user ? [$user] : [];

        ob_start();
        include view_path('admin.guides.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Sửa hồ sơ HDV',
            'pageTitle'  => 'Sửa hồ sơ HDV',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Hồ sơ HDV', 'url' => BASE_URL . '?act=guide-profiles'],
                ['label' => 'Chỉnh sửa', 'url' => BASE_URL . '?act=guide-profile-edit&id=' . $id, 'active' => true],
>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317
            ],
        ]);
    }

<<<<<<< HEAD
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

=======
    // Cập nhật
    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=guide-profiles');
            exit;
        }

        $id           = (int)($_POST['id'] ?? 0);
        $birthdate    = $_POST['birthdate'] ?? '';
        $phone        = trim($_POST['phone'] ?? '');
        $certificate  = trim($_POST['certificate'] ?? '');
        $languages    = trim($_POST['languages'] ?? '');
        $experience   = trim($_POST['experience'] ?? '');
        $rating       = $_POST['rating'] !== '' ? (float)$_POST['rating'] : null;
        $healthStatus = trim($_POST['health_status'] ?? '');
        $groupType    = trim($_POST['group_type'] ?? '');
        $speciality   = trim($_POST['speciality'] ?? '');

        $profile = GuideProfile::find($id);
        if (!$profile) {
            header('Location: ' . BASE_URL . '?act=guide-profiles');
            exit;
        }

>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317
        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

<<<<<<< HEAD
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
=======
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $profile->user_id]);
        $user = $stmt->fetch();

        $errors = [];

        if ($phone === '') {
            $errors[] = 'Vui lòng nhập số điện thoại.';
        }

        $languagesText = $languages;

        $old = [
            'id'           => $profile->id,
            'user_id'      => $profile->user_id,
            'birthdate'    => $birthdate,
            'phone'        => $phone,
            'certificate'  => $certificate,
            'languages'    => $languagesText,
            'experience'   => $experience,
            'avatar'       => $profile->avatar,
            'rating'       => $rating,
            'health_status'=> $healthStatus,
            'group_type'   => $groupType,
            'speciality'   => $speciality,
        ];

        $guides = $user ? [$user] : [];

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.guides.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Sửa hồ sơ HDV',
                'pageTitle'  => 'Sửa hồ sơ HDV',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                    ['label' => 'Hồ sơ HDV', 'url' => BASE_URL . '?act=guide-profiles'],
                    ['label' => 'Chỉnh sửa', 'url' => BASE_URL . '?act=guide-profile-edit&id=' . $id, 'active' => true],
                ],
            ]);
            return;
        }

        // Upload avatar mới nếu có
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'guides';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
            $newName = uniqid('guide_', true) . ($safeExt ? '.' . $safeExt : '');
            $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $profile->avatar = $newName;
            }
        }

        $languagesJson = null;
        if ($languagesText !== '') {
            $arr = array_values(array_filter(array_map('trim', preg_split('/[,;]/', $languagesText))));
            if (!empty($arr)) {
                $languagesJson = json_encode($arr, JSON_UNESCAPED_UNICODE);
            }
        }

        $profile->birthdate    = $birthdate ?: null;
        $profile->phone        = $phone;
        $profile->certificate  = $certificate;
        $profile->languages    = $languagesJson;
        $profile->experience   = $experience;
        $profile->rating       = $rating;
        $profile->health_status = $healthStatus ?: null;
        $profile->group_type   = $groupType ?: null;
        $profile->speciality   = $speciality ?: null;
        $profile->save();

        header('Location: ' . BASE_URL . '?act=guide-profiles');
        exit;
    }

    // Xem chi tiết hồ sơ
    public function show(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $profile = GuideProfile::find($id);

        if (!$profile) {
            header('Location: ' . BASE_URL . '?act=guide-profiles');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Lấy thông tin user
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $profile->user_id]);
        $user = $stmt->fetch();

        // Decode languages
        $languages = [];
        if ($profile->languages) {
            $decoded = json_decode($profile->languages, true);
            if (is_array($decoded)) {
                $languages = $decoded;
            }
        }

        // Decode history
        $history = [];
        if ($profile->history) {
            $decoded = json_decode($profile->history, true);
            if (is_array($decoded)) {
                $history = $decoded;
            }
        }

        ob_start();
        include view_path('admin.guides.show');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Chi tiết hồ sơ HDV',
            'pageTitle'  => 'Chi tiết hồ sơ HDV',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Hồ sơ HDV', 'url' => BASE_URL . '?act=guide-profiles'],
                ['label' => 'Chi tiết', 'url' => BASE_URL . '?act=guide-profile-show&id=' . $id, 'active' => true],
            ],
        ]);
    }

    // Xóa hồ sơ
    public function delete(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $profile = GuideProfile::find($id);

        if (!$profile) {
            header('Location: ' . BASE_URL . '?act=guide-profiles');
            exit;
        }

        // Xóa hồ sơ
        $profile->delete();

        header('Location: ' . BASE_URL . '?act=guide-profiles');
>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317
        exit;
    }
}

<<<<<<< HEAD
=======

>>>>>>> 59c3a9ba6d90bffe1377127c99c7f9c535a23317
