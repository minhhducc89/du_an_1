<?php

class BookingController
{
    // Danh sách booking
    public function index(): void
    {
        requireAdmin();

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $sql = "
            SELECT b.*, t.name AS tour_name, ts.name AS status_name, u.name AS guide_name
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN tour_statuses ts ON ts.id = b.status
            LEFT JOIN users u ON u.id = b.assigned_guide_id
            ORDER BY b.created_at DESC
        ";
        $stmt = $pdo->query($sql);
        $bookings = $stmt->fetchAll();

        ob_start();
        include view_path('admin.bookings.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Quản lý booking',
            'pageTitle'  => 'Danh sách booking',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Booking', 'url' => BASE_URL . '?act=bookings', 'active' => true],
            ],
        ]);
    }

    // Form tạo booking
    public function create(): void
    {
        requireAdmin();

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $tours = Tour::all();
        $customers = Customer::all(true); // Chỉ lấy customers đang hoạt động
        $errors = [];
        $old = [
            'tour_id'             => '',
            'customer_id'         => '',
            'customer_name'       => '',
            'customer_phone'      => '',
            'customer_email'      => '',
            'customer_address'    => '',
            'booking_type'        => '',
            'adult_qty'           => 1,
            'child_qty'           => 0,
            'start_date'          => '',
            'end_date'            => '',
            'special_requirements'=> '',
            'notes'               => '',
        ];

        ob_start();
        include view_path('admin.bookings.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Tạo booking mới',
            'pageTitle'  => 'Tạo booking mới',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Booking', 'url' => BASE_URL . '?act=bookings'],
                ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=booking-create', 'active' => true],
            ],
        ]);
    }

    // Lưu booking mới
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=booking-create');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $tourId          = (int)($_POST['tour_id'] ?? 0);
        $customerId      = (int)($_POST['customer_id'] ?? 0);
        $customerName    = trim($_POST['customer_name'] ?? '');
        $customerPhone   = trim($_POST['customer_phone'] ?? '');
        $customerEmail   = trim($_POST['customer_email'] ?? '');
        $customerAddress = trim($_POST['customer_address'] ?? '');
        $bookingType     = trim($_POST['booking_type'] ?? '');
        $adultQty        = (int)($_POST['adult_qty'] ?? 0);
        $childQty            = (int)($_POST['child_qty'] ?? 0);
        $startDate           = $_POST['start_date'] ?? '';
        $endDate             = $_POST['end_date'] ?? '';
        $specialRequirements = trim($_POST['special_requirements'] ?? '');
        $notes               = trim($_POST['notes'] ?? '');

        $errors = [];

        if ($tourId <= 0) {
            $errors[] = 'Vui lòng chọn tour.';
        }
        // Nếu chọn customer từ danh sách, lấy thông tin từ đó
        if ($customerId > 0) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $customerName    = $customer->name;
                $customerPhone   = $customer->phone;
                $customerEmail   = $customer->email ?? '';
                $customerAddress = $customer->address ?? '';
            }
        }

        if ($customerName === '') {
            $errors[] = 'Vui lòng nhập họ tên khách hoặc chọn khách hàng từ danh sách.';
        }
        if ($customerPhone === '') {
            $errors[] = 'Vui lòng nhập số điện thoại hoặc chọn khách hàng từ danh sách.';
        }
        if ($adultQty + $childQty <= 0) {
            $errors[] = 'Số lượng khách phải lớn hơn 0.';
        }
        if ($startDate === '') {
            $errors[] = 'Vui lòng chọn ngày khởi hành.';
        }

        $tour = $tourId > 0 ? Tour::find($tourId) : null;
        if (!$tour) {
            $errors[] = 'Tour không tồn tại.';
        }

        $totalGuests = $adultQty + $childQty;
        
        // Tự động xác định loại booking nếu chưa chọn (dựa trên số lượng khách)
        if ($bookingType === '' && $totalGuests > 0) {
            $bookingType = ($totalGuests <= 2) ? 'individual' : 'group';
        }
        
        // Nếu vẫn chưa có booking_type sau khi tự động xác định, báo lỗi
        if ($bookingType === '') {
            $errors[] = 'Vui lòng chọn loại booking (khách lẻ hoặc đoàn).';
        }

        // Lấy giá người lớn / trẻ em
        $adultPrice = $tour ? $tour->price : null;
        $childPrice = null;
        if ($tour && $tour->prices) {
            $p = json_decode($tour->prices, true);
            if (is_array($p)) {
                if (isset($p['adult'])) {
                    $adultPrice = $p['adult'];
                }
                if (isset($p['child'])) {
                    $childPrice = $p['child'];
                }
            }
        }

        $totalAmount = 0;
        if ($adultPrice !== null) {
            $totalAmount += $adultPrice * $adultQty;
        }
        if ($childPrice !== null) {
            $totalAmount += $childPrice * $childQty;
        }

        // Kiểm tra capacity
        if ($tour && $tour->max_guests) {
            $sql = "
                SELECT service_detail
                FROM bookings
                WHERE tour_id = :tour_id
                  AND start_date = :start_date
                  AND status <> 4
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tour_id'    => $tourId,
                ':start_date' => $startDate,
            ]);

            $currentTotal = 0;
            while ($row = $stmt->fetch()) {
                if (!empty($row['service_detail'])) {
                    $sd = json_decode($row['service_detail'], true);
                    if (is_array($sd) && isset($sd['total_guests'])) {
                        $currentTotal += (int)$sd['total_guests'];
                    }
                }
            }

            $capacity = (int)$tour->max_guests;
            if ($capacity > 0 && ($currentTotal + $totalGuests) > $capacity) {
                $errors[] = 'Số khách vượt quá sức chứa tour trong ngày này. Còn trống: ' . max(0, $capacity - $currentTotal);
            }
        }

        $old = [
            'tour_id'             => $tourId,
            'customer_id'         => $customerId,
            'customer_name'       => $customerName,
            'customer_phone'      => $customerPhone,
            'customer_email'      => $customerEmail,
            'customer_address'    => $customerAddress,
            'booking_type'        => $bookingType,
            'adult_qty'           => $adultQty,
            'child_qty'           => $childQty,
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'special_requirements'=> $specialRequirements,
            'notes'               => $notes,
        ];

        $tours = Tour::all();
        $customers = Customer::all(true);

        if (!empty($errors)) {
            $errorsLocal = $errors;
            $errors = $errorsLocal;

            ob_start();
            include view_path('admin.bookings.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Tạo booking mới',
                'pageTitle'  => 'Tạo booking mới',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                    ['label' => 'Booking', 'url' => BASE_URL . '?act=bookings'],
                    ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=booking-create', 'active' => true],
                ],
            ]);
            return;
        }

        $currentUser = getCurrentUser();

        // Nếu chưa có customer trong danh sách và có thông tin đầy đủ, tự động tạo customer mới
        if ($customerId === 0 && $customerPhone !== '') {
            $existingCustomer = null;
            // Kiểm tra xem đã có customer với phone này chưa
            $pdo = getDB();
            if ($pdo !== null) {
                $stmt = $pdo->prepare('SELECT * FROM customers WHERE phone = :phone LIMIT 1');
                $stmt->execute([':phone' => $customerPhone]);
                $existingCustomer = $stmt->fetch();
            }

            if (!$existingCustomer && $customerName !== '' && $customerPhone !== '') {
                // Tạo customer mới
                $newCustomer = new Customer([
                    'name'     => $customerName,
                    'phone'    => $customerPhone,
                    'email'    => $customerEmail ?: null,
                    'address'  => $customerAddress ?: null,
                    'status'   => 1,
                ]);
                $newCustomer->save();
                $customerId = $newCustomer->id;
            } elseif ($existingCustomer) {
                $customerId = (int)$existingCustomer['id'];
            }
        }

        $serviceDetail = json_encode([
            'customer' => [
                'name'    => $customerName,
                'phone'   => $customerPhone,
                'email'   => $customerEmail,
                'address' => $customerAddress,
            ],
            'customer_id'         => $customerId > 0 ? $customerId : null,
            'booking_type'        => $bookingType, // 'individual' hoặc 'group'
            'special_requirements'=> $specialRequirements, // Yêu cầu đặc biệt (ăn chay, bệnh lý, v.v.)
            'adult'              => $adultQty,
            'child'              => $childQty,
            'total_guests'       => $totalGuests,
            'adult_price'        => $adultPrice,
            'child_price'        => $childPrice,
            'total_amount'       => $totalAmount,
        ], JSON_UNESCAPED_UNICODE);

        $statusId = 1; // Chờ xác nhận

        $stmt = $pdo->prepare("
            INSERT INTO bookings
              (tour_id, created_by, assigned_guide_id, status, start_date, end_date,
               schedule_detail, service_detail, diary, lists_file, notes)
            VALUES
              (:tour_id, :created_by, NULL, :status, :start_date, :end_date,
               :schedule_detail, :service_detail, NULL, NULL, :notes)
        ");
        $stmt->execute([
            ':tour_id'        => $tourId,
            ':created_by'     => $currentUser ? $currentUser->id : null,
            ':status'         => $statusId,
            ':start_date'     => $startDate,
            ':end_date'       => $endDate ?: null,
            ':schedule_detail'=> null,
            ':service_detail' => $serviceDetail,
            ':notes'          => $notes,
        ]);

        $bookingId = (int)$pdo->lastInsertId();

        // Log trạng thái ban đầu
        $stmtLog = $pdo->prepare("
            INSERT INTO booking_status_logs
              (booking_id, old_status, new_status, changed_by, note)
            VALUES
              (:booking_id, NULL, :new_status, :changed_by, :note)
        ");
        $stmtLog->execute([
            ':booking_id' => $bookingId,
            ':new_status' => $statusId,
            ':changed_by' => $currentUser ? $currentUser->id : null,
            ':note'       => 'Tạo booking mới',
        ]);

        header('Location: ' . BASE_URL . '?act=bookings');
        exit;
    }

    // Form sửa booking
    public function edit(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $booking = Booking::find($id);
        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        // Kiểm tra booking có thể sửa không (không cho sửa nếu đã hoàn thành hoặc đã hủy)
        if ($booking->status == 3 || $booking->status == 4) {
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
            exit;
        }

        $tours = Tour::all();
        $customers = Customer::all(true);

        // Lấy thông tin service_detail
        $service = [];
        if (!empty($booking->service_detail)) {
            $decoded = json_decode($booking->service_detail, true);
            if (is_array($decoded)) {
                $service = $decoded;
            }
        }

        $old = [
            'tour_id'             => $booking->tour_id,
            'customer_id'         => $service['customer_id'] ?? 0,
            'customer_name'       => $service['customer']['name'] ?? '',
            'customer_phone'      => $service['customer']['phone'] ?? '',
            'customer_email'      => $service['customer']['email'] ?? '',
            'customer_address'    => $service['customer']['address'] ?? '',
            'booking_type'        => $service['booking_type'] ?? '',
            'adult_qty'           => $service['adult'] ?? 1,
            'child_qty'           => $service['child'] ?? 0,
            'start_date'          => $booking->start_date,
            'end_date'            => $booking->end_date,
            'special_requirements'=> $service['special_requirements'] ?? '',
            'notes'               => $booking->notes ?? '',
        ];

        $errors = [];

        ob_start();
        include view_path('admin.bookings.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Sửa booking #' . $booking->id,
            'pageTitle'  => 'Sửa booking',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Booking', 'url' => BASE_URL . '?act=bookings'],
                ['label' => 'Chi tiết', 'url' => BASE_URL . '?act=booking-show&id=' . $id],
                ['label' => 'Sửa', 'url' => BASE_URL . '?act=booking-edit&id=' . $id, 'active' => true],
            ],
        ]);
    }

    // Cập nhật booking
    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $booking = Booking::find($id);

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        // Kiểm tra booking có thể sửa không
        if ($booking->status == 3 || $booking->status == 4) {
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $tourId          = (int)($_POST['tour_id'] ?? 0);
        $customerId      = (int)($_POST['customer_id'] ?? 0);
        $customerName    = trim($_POST['customer_name'] ?? '');
        $customerPhone   = trim($_POST['customer_phone'] ?? '');
        $customerEmail   = trim($_POST['customer_email'] ?? '');
        $customerAddress = trim($_POST['customer_address'] ?? '');
        $bookingType     = trim($_POST['booking_type'] ?? '');
        $adultQty            = (int)($_POST['adult_qty'] ?? 0);
        $childQty            = (int)($_POST['child_qty'] ?? 0);
        $startDate           = $_POST['start_date'] ?? '';
        $endDate             = $_POST['end_date'] ?? '';
        $specialRequirements = trim($_POST['special_requirements'] ?? '');
        $notes               = trim($_POST['notes'] ?? '');

        $errors = [];

        if ($tourId <= 0) {
            $errors[] = 'Vui lòng chọn tour.';
        }
        // Nếu chọn customer từ danh sách, lấy thông tin từ đó
        if ($customerId > 0) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $customerName    = $customer->name;
                $customerPhone   = $customer->phone;
                $customerEmail   = $customer->email ?? '';
                $customerAddress = $customer->address ?? '';
            }
        }

        if ($customerName === '') {
            $errors[] = 'Vui lòng nhập họ tên khách hoặc chọn khách hàng từ danh sách.';
        }
        if ($customerPhone === '') {
            $errors[] = 'Vui lòng nhập số điện thoại hoặc chọn khách hàng từ danh sách.';
        }
        if ($adultQty + $childQty <= 0) {
            $errors[] = 'Số lượng khách phải lớn hơn 0.';
        }
        if ($startDate === '') {
            $errors[] = 'Vui lòng chọn ngày khởi hành.';
        }

        $tour = $tourId > 0 ? Tour::find($tourId) : null;
        if (!$tour) {
            $errors[] = 'Tour không tồn tại.';
        }

        $totalGuests = $adultQty + $childQty;
        
        // Tự động xác định loại booking nếu chưa chọn (dựa trên số lượng khách)
        if ($bookingType === '' && $totalGuests > 0) {
            $bookingType = ($totalGuests <= 2) ? 'individual' : 'group';
        }
        
        // Nếu vẫn chưa có booking_type sau khi tự động xác định, báo lỗi
        if ($bookingType === '') {
            $errors[] = 'Vui lòng chọn loại booking (khách lẻ hoặc đoàn).';
        }

        // Lấy giá người lớn / trẻ em
        $adultPrice = $tour ? $tour->price : null;
        $childPrice = null;
        if ($tour && $tour->prices) {
            $p = json_decode($tour->prices, true);
            if (is_array($p)) {
                if (isset($p['adult'])) {
                    $adultPrice = $p['adult'];
                }
                if (isset($p['child'])) {
                    $childPrice = $p['child'];
                }
            }
        }

        $totalAmount = 0;
        if ($adultPrice !== null) {
            $totalAmount += $adultPrice * $adultQty;
        }
        if ($childPrice !== null) {
            $totalAmount += $childPrice * $childQty;
        }

        // Kiểm tra capacity (trừ booking hiện tại)
        if ($tour && $tour->max_guests) {
            $sql = "
                SELECT service_detail
                FROM bookings
                WHERE tour_id = :tour_id
                  AND start_date = :start_date
                  AND status <> 4
                  AND id <> :booking_id
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tour_id'    => $tourId,
                ':start_date' => $startDate,
                ':booking_id' => $id,
            ]);

            $currentTotal = 0;
            while ($row = $stmt->fetch()) {
                if (!empty($row['service_detail'])) {
                    $sd = json_decode($row['service_detail'], true);
                    if (is_array($sd) && isset($sd['total_guests'])) {
                        $currentTotal += (int)$sd['total_guests'];
                    }
                }
            }

            $capacity = (int)$tour->max_guests;
            if ($capacity > 0 && ($currentTotal + $totalGuests) > $capacity) {
                $errors[] = 'Số khách vượt quá sức chứa tour trong ngày này. Còn trống: ' . max(0, $capacity - $currentTotal);
            }
        }

        $old = [
            'tour_id'             => $tourId,
            'customer_id'         => $customerId,
            'customer_name'       => $customerName,
            'customer_phone'      => $customerPhone,
            'customer_email'      => $customerEmail,
            'customer_address'    => $customerAddress,
            'booking_type'        => $bookingType,
            'adult_qty'           => $adultQty,
            'child_qty'           => $childQty,
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'special_requirements'=> $specialRequirements,
            'notes'               => $notes,
        ];

        $tours = Tour::all();
        $customers = Customer::all(true);

        if (!empty($errors)) {
            $errorsLocal = $errors;
            $errors = $errorsLocal;

            ob_start();
            include view_path('admin.bookings.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Sửa booking #' . $id,
                'pageTitle'  => 'Sửa booking',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                    ['label' => 'Booking', 'url' => BASE_URL . '?act=bookings'],
                    ['label' => 'Chi tiết', 'url' => BASE_URL . '?act=booking-show&id=' . $id],
                    ['label' => 'Sửa', 'url' => BASE_URL . '?act=booking-edit&id=' . $id, 'active' => true],
                ],
            ]);
            return;
        }

        // Nếu chưa có customer trong danh sách và có thông tin đầy đủ, tự động tạo customer mới
        if ($customerId === 0 && $customerPhone !== '') {
            $existingCustomer = null;
            // Kiểm tra xem đã có customer với phone này chưa
            $pdo = getDB();
            if ($pdo !== null) {
                $stmt = $pdo->prepare('SELECT * FROM customers WHERE phone = :phone LIMIT 1');
                $stmt->execute([':phone' => $customerPhone]);
                $existingCustomer = $stmt->fetch();
            }

            if (!$existingCustomer && $customerName !== '' && $customerPhone !== '') {
                // Tạo customer mới
                $newCustomer = new Customer([
                    'name'     => $customerName,
                    'phone'    => $customerPhone,
                    'email'    => $customerEmail ?: null,
                    'address'  => $customerAddress ?: null,
                    'status'   => 1,
                ]);
                $newCustomer->save();
                $customerId = $newCustomer->id;
            } elseif ($existingCustomer) {
                $customerId = (int)$existingCustomer['id'];
            }
        }

        $serviceDetail = json_encode([
            'customer' => [
                'name'    => $customerName,
                'phone'   => $customerPhone,
                'email'   => $customerEmail,
                'address' => $customerAddress,
            ],
            'customer_id'         => $customerId > 0 ? $customerId : null,
            'booking_type'        => $bookingType, // 'individual' hoặc 'group'
            'special_requirements'=> $specialRequirements, // Yêu cầu đặc biệt (ăn chay, bệnh lý, v.v.)
            'adult'              => $adultQty,
            'child'              => $childQty,
            'total_guests'       => $totalGuests,
            'adult_price'        => $adultPrice,
            'child_price'        => $childPrice,
            'total_amount'       => $totalAmount,
        ], JSON_UNESCAPED_UNICODE);

        $stmt = $pdo->prepare("
            UPDATE bookings
            SET tour_id = :tour_id,
                start_date = :start_date,
                end_date = :end_date,
                service_detail = :service_detail,
                notes = :notes
            WHERE id = :id
        ");
        $stmt->execute([
            ':tour_id'        => $tourId,
            ':start_date'     => $startDate,
            ':end_date'       => $endDate ?: null,
            ':service_detail' => $serviceDetail,
            ':notes'          => $notes,
            ':id'             => $id,
        ]);

        header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
        exit;
    }

    // Xem chi tiết booking
    public function show(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $sql = "
            SELECT b.*, t.name AS tour_name, ts.name AS status_name
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN tour_statuses ts ON ts.id = b.status
            WHERE b.id = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        $service = [];
        if (!empty($booking['service_detail'])) {
            $decoded = json_decode($booking['service_detail'], true);
            if (is_array($decoded)) {
                $service = $decoded;
            }
        }

        // Lấy danh sách trạng thái
        $statusStmt = $pdo->query('SELECT * FROM tour_statuses ORDER BY id');
        $statuses = $statusStmt->fetchAll();

        // Lấy danh sách HDV đang active để phân công
        $guideSql = "
            SELECT u.id, u.name, u.email
            FROM users u
            WHERE u.role = 'guide'
              AND u.status = 1
            ORDER BY u.name
        ";
        $guides = $pdo->query($guideSql)->fetchAll();

        // Lấy log trạng thái
        $logSql = "
            SELECT l.*, ts_old.name AS old_status_name, ts_new.name AS new_status_name, u.name AS changed_by_name
            FROM booking_status_logs l
            LEFT JOIN tour_statuses ts_old ON ts_old.id = l.old_status
            LEFT JOIN tour_statuses ts_new ON ts_new.id = l.new_status
            LEFT JOIN users u ON u.id = l.changed_by
            WHERE l.booking_id = :booking_id
            ORDER BY l.changed_at ASC
        ";
        $logStmt = $pdo->prepare($logSql);
        $logStmt->execute([':booking_id' => $id]);
        $logs = $logStmt->fetchAll();

        // Lấy danh sách khách đoàn
        $guests = TourGuest::allByBooking($id);

        ob_start();
        include view_path('admin.bookings.show');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Chi tiết booking #' . $booking['id'],
            'pageTitle'  => 'Chi tiết booking',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
                ['label' => 'Booking', 'url' => BASE_URL . '?act=bookings'],
                ['label' => 'Chi tiết', 'url' => BASE_URL . '?act=booking-show&id=' . $id, 'active' => true],
            ],
        ]);
    }

    // Đổi trạng thái booking
    public function changeStatus(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        $id        = (int)($_GET['id'] ?? 0);
        $newStatus = (int)($_POST['new_status'] ?? 0);
        $note      = trim($_POST['note'] ?? '');

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $stmt = $pdo->prepare('SELECT status FROM bookings WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        $oldStatus = (int)$row['status'];
        if ($oldStatus === $newStatus) {
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
            exit;
        }

        $currentUser = getCurrentUser();

        $stmt = $pdo->prepare('UPDATE bookings SET status = :new_status WHERE id = :id');
        $stmt->execute([
            ':new_status' => $newStatus,
            ':id'         => $id,
        ]);

        $stmtLog = $pdo->prepare("
            INSERT INTO booking_status_logs
              (booking_id, old_status, new_status, changed_by, note)
            VALUES
              (:booking_id, :old_status, :new_status, :changed_by, :note)
        ");
        $stmtLog->execute([
            ':booking_id' => $id,
            ':old_status' => $oldStatus,
            ':new_status' => $newStatus,
            ':changed_by' => $currentUser ? $currentUser->id : null,
            ':note'       => $note,
        ]);

        header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
        exit;
    }

    // Phân công HDV cho booking
    public function assignGuide(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        $id      = (int)($_GET['id'] ?? 0);
        $guideId = (int)($_POST['guide_id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Lấy thông tin booking
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $booking = $stmt->fetch();
        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        if ($guideId === 0) {
            // Bỏ phân công
            $stmt = $pdo->prepare('UPDATE bookings SET assigned_guide_id = NULL WHERE id = :id');
            $stmt->execute([':id' => $id]);
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
            exit;
        }

        // Kiểm tra guide tồn tại và là guide active
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND role = 'guide' AND status = 1 LIMIT 1");
        $stmt->execute([':id' => $guideId]);
        $guide = $stmt->fetch();
        if (!$guide) {
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
            exit;
        }

        // Cập nhật assigned_guide_id
        $stmt = $pdo->prepare('UPDATE bookings SET assigned_guide_id = :guide_id WHERE id = :id');
        $stmt->execute([
            ':guide_id' => $guideId,
            ':id'       => $id,
        ]);

        header('Location: ' . BASE_URL . '?act=booking-show&id=' . $id);
        exit;
    }

    // Xóa booking
    public function delete(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $booking = Booking::find($id);

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        // Xóa booking (tour_guests sẽ tự động xóa nhờ CASCADE)
        $booking->delete();

        header('Location: ' . BASE_URL . '?act=bookings');
        exit;
    }

    // Thêm khách mới vào booking
    public function storeGuest(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        $bookingId      = (int)($_GET['id'] ?? 0);
        $fullname       = trim($_POST['fullname'] ?? '');
        $dob            = $_POST['dob'] ?? '';
        $gender         = trim($_POST['gender'] ?? '');
        $passportNumber = trim($_POST['passport_number'] ?? '');

        $errors = [];
        if ($bookingId <= 0) {
            $errors[] = 'Booking không hợp lệ.';
        }
        if ($fullname === '') {
            $errors[] = 'Vui lòng nhập họ tên khách.';
        }

        // Kiểm tra booking tồn tại
        $booking = Booking::find($bookingId);
        if (!$booking) {
            $errors[] = 'Booking không tồn tại.';
        }

        if (!empty($errors)) {
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $bookingId);
            exit;
        }

        $guest = new TourGuest([
            'booking_id'     => $bookingId,
            'fullname'       => $fullname,
            'dob'            => $dob ?: null,
            'gender'         => $gender ?: null,
            'passport_number'=> $passportNumber ?: null,
        ]);
        $guest->save();

        header('Location: ' . BASE_URL . '?act=booking-show&id=' . $bookingId);
        exit;
    }

    // Xóa khách khỏi booking
    public function deleteGuest(): void
    {
        requireAdmin();

        $id        = (int)($_GET['id'] ?? 0);
        $bookingId = (int)($_GET['booking_id'] ?? 0);

        $guest = TourGuest::find($id);
        if ($guest && $guest->booking_id === $bookingId) {
            $guest->delete();
        }

        header('Location: ' . BASE_URL . '?act=booking-show&id=' . $bookingId);
        exit;
    }

    // Import danh sách khách từ CSV
    public function importGuests(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        $bookingId = (int)($_GET['id'] ?? 0);

        // Kiểm tra booking tồn tại
        $booking = Booking::find($bookingId);
        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        if (empty($_FILES['csv_file']['name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $bookingId);
            exit;
        }

        $tmpFile = $_FILES['csv_file']['tmp_name'];
        $handle  = fopen($tmpFile, 'r');

        if ($handle === false) {
            header('Location: ' . BASE_URL . '?act=booking-show&id=' . $bookingId);
            exit;
        }

        // Bỏ qua dòng header (nếu có)
        $firstLine = fgetcsv($handle);
        $imported  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1) {
                continue;
            }

            // Format: fullname, dob, gender, passport_number
            $fullname       = trim($row[0] ?? '');
            $dob            = !empty($row[1]) ? trim($row[1]) : null;
            $gender         = !empty($row[2]) ? trim($row[2]) : null;
            $passportNumber = !empty($row[3]) ? trim($row[3]) : null;

            if ($fullname === '') {
                continue;
            }

            $guest = new TourGuest([
                'booking_id'     => $bookingId,
                'fullname'       => $fullname,
                'dob'            => $dob,
                'gender'         => $gender,
                'passport_number'=> $passportNumber,
            ]);
            if ($guest->save()) {
                $imported++;
            }
        }

        fclose($handle);

        header('Location: ' . BASE_URL . '?act=booking-show&id=' . $bookingId . '&imported=' . $imported);
        exit;
    }

    // Export danh sách khách ra HTML (để in PDF)
    public function exportGuests(): void
    {
        requireAdmin();

        $bookingId = (int)($_GET['id'] ?? 0);

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Lấy thông tin booking
        $sql = "
            SELECT b.*, t.name AS tour_name, ts.name AS status_name
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN tour_statuses ts ON ts.id = b.status
            WHERE b.id = :id
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $bookingId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            header('Location: ' . BASE_URL . '?act=bookings');
            exit;
        }

        // Lấy danh sách khách
        $guests = TourGuest::allByBooking($bookingId);

        // Render HTML để in
        ob_start();
        include view_path('admin.bookings.export_guests');
        $content = ob_get_clean();

        // Output HTML (user có thể dùng Print to PDF của browser)
        echo $content;
        exit;
    }
}


