<?php

class CustomerController
{
    // Danh sách khách hàng
    public function index(): void
    {
        requireAdmin();

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Lấy tham số filter
        $filterSearch = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filterStatus = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;
        $filterHasBooking = isset($_GET['has_booking']) && $_GET['has_booking'] !== '' ? $_GET['has_booking'] : null;

        // Xây dựng query với filter
        $where = [];
        $params = [];

        if ($filterSearch !== '') {
            $where[] = '(c.name LIKE :search OR c.phone LIKE :search OR c.email LIKE :search OR c.company LIKE :search)';
            $params[':search'] = '%' . $filterSearch . '%';
        }

        if ($filterStatus !== null) {
            $where[] = 'c.status = :status';
            $params[':status'] = $filterStatus;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Lấy danh sách customers với filter
        $sql = "SELECT * FROM customers c {$whereClause} ORDER BY c.created_at DESC";
        
        if (!empty($params)) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $pdo->query($sql);
        }
        $customers = $stmt->fetchAll();

        // Đếm số booking cho mỗi customer (dựa trên phone trong service_detail)
        foreach ($customers as &$customer) {
            $phone = $customer['phone'];
            $pattern = '%"phone":"' . str_replace('"', '\\"', $phone) . '"%';
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE service_detail LIKE :pattern');
            $stmt->execute([':pattern' => $pattern]);
            $row = $stmt->fetch();
            $customer['booking_count'] = (int)($row['count'] ?? 0);
        }
        unset($customer);

        // Lọc theo has_booking sau khi đã đếm
        if ($filterHasBooking !== null) {
            if ($filterHasBooking === 'yes') {
                $customers = array_filter($customers, fn($c) => ($c['booking_count'] ?? 0) > 0);
            } elseif ($filterHasBooking === 'no') {
                $customers = array_filter($customers, fn($c) => ($c['booking_count'] ?? 0) == 0);
            }
            // Re-index array sau khi filter
            $customers = array_values($customers);
        }

        // Truyền filter values vào view
        $filterValues = [
            'search' => $filterSearch,
            'status' => $filterStatus,
            'has_booking' => $filterHasBooking,
        ];

        ob_start();
        include view_path('admin.customers.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Quản lý khách hàng',
            'pageTitle'  => 'Danh sách khách hàng',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                ['label' => 'Khách hàng', 'url' => BASE_URL . '?act=customers', 'active' => true],
            ],
        ]);
    }

    // Form tạo mới
    public function create(): void
    {
        requireAdmin();

        $errors = [];
        $old = [
            'name'     => '',
            'phone'    => '',
            'email'    => '',
            'address'  => '',
            'company'  => '',
            'tax_code' => '',
            'notes'    => '',
            'status'   => 1,
        ];

        ob_start();
        include view_path('admin.customers.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Tạo khách hàng mới',
            'pageTitle'  => 'Tạo khách hàng mới',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                ['label' => 'Khách hàng', 'url' => BASE_URL . '?act=customers'],
                ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=customer-create', 'active' => true],
            ],
        ]);
    }

    // Lưu tạo mới
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=customer-create');
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $company  = trim($_POST['company'] ?? '');
        $taxCode  = trim($_POST['tax_code'] ?? '');
        $notes    = trim($_POST['notes'] ?? '');
        $status   = (int)($_POST['status'] ?? 1);

        $errors = [];

        if ($name === '') {
            $errors[] = 'Họ tên không được để trống.';
        }
        if ($phone === '') {
            $errors[] = 'Số điện thoại không được để trống.';
        } elseif (Customer::existsByPhone($phone)) {
            $errors[] = 'Số điện thoại đã tồn tại, vui lòng chọn số khác.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        } elseif ($email !== '' && Customer::existsByEmail($email)) {
            $errors[] = 'Email đã tồn tại, vui lòng chọn email khác.';
        }

        $old = [
            'name'     => $name,
            'phone'    => $phone,
            'email'    => $email,
            'address'  => $address,
            'company'  => $company,
            'tax_code' => $taxCode,
            'notes'    => $notes,
            'status'   => $status,
        ];

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.customers.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Tạo khách hàng mới',
                'pageTitle'  => 'Tạo khách hàng mới',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                    ['label' => 'Khách hàng', 'url' => BASE_URL . '?act=customers'],
                    ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=customer-create', 'active' => true],
                ],
            ]);
            return;
        }

        $customer = new Customer([
            'name'     => $name,
            'phone'    => $phone,
            'email'    => $email ?: null,
            'address'  => $address ?: null,
            'company'  => $company ?: null,
            'tax_code' => $taxCode ?: null,
            'notes'    => $notes ?: null,
            'status'   => $status,
        ]);
        $customer->save();

        header('Location: ' . BASE_URL . '?act=customers');
        exit;
    }

    // Form sửa
    public function edit(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $customer = Customer::find($id);

        if (!$customer) {
            header('Location: ' . BASE_URL . '?act=customers');
            exit;
        }

        $errors = [];
        $old = [
            'id'       => $customer->id,
            'name'     => $customer->name,
            'phone'    => $customer->phone,
            'email'    => $customer->email,
            'address'  => $customer->address,
            'company'  => $customer->company,
            'tax_code' => $customer->tax_code,
            'notes'    => $customer->notes,
            'status'   => $customer->status,
        ];

        ob_start();
        include view_path('admin.customers.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Sửa khách hàng',
            'pageTitle'  => 'Sửa khách hàng',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                ['label' => 'Khách hàng', 'url' => BASE_URL . '?act=customers'],
                ['label' => 'Chỉnh sửa', 'url' => BASE_URL . '?act=customer-edit&id=' . $id, 'active' => true],
            ],
        ]);
    }

    // Cập nhật
    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=customers');
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $company  = trim($_POST['company'] ?? '');
        $taxCode  = trim($_POST['tax_code'] ?? '');
        $notes    = trim($_POST['notes'] ?? '');
        $status   = (int)($_POST['status'] ?? 1);

        $customer = Customer::find($id);
        if (!$customer) {
            header('Location: ' . BASE_URL . '?act=customers');
            exit;
        }

        $errors = [];

        if ($name === '') {
            $errors[] = 'Họ tên không được để trống.';
        }
        if ($phone === '') {
            $errors[] = 'Số điện thoại không được để trống.';
        } elseif (Customer::existsByPhone($phone, $id)) {
            $errors[] = 'Số điện thoại đã tồn tại, vui lòng chọn số khác.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        } elseif ($email !== '' && Customer::existsByEmail($email, $id)) {
            $errors[] = 'Email đã tồn tại, vui lòng chọn email khác.';
        }

        $old = [
            'id'       => $customer->id,
            'name'     => $name,
            'phone'    => $phone,
            'email'    => $email,
            'address'  => $address,
            'company'  => $company,
            'tax_code' => $taxCode,
            'notes'    => $notes,
            'status'   => $status,
        ];

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.customers.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Sửa khách hàng',
                'pageTitle'  => 'Sửa khách hàng',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                    ['label' => 'Khách hàng', 'url' => BASE_URL . '?act=customers'],
                    ['label' => 'Chỉnh sửa', 'url' => BASE_URL . '?act=customer-edit&id=' . $id, 'active' => true],
                ],
            ]);
            return;
        }

        $customer->name     = $name;
        $customer->phone    = $phone;
        $customer->email    = $email ?: null;
        $customer->address  = $address ?: null;
        $customer->company = $company ?: null;
        $customer->tax_code = $taxCode ?: null;
        $customer->notes   = $notes ?: null;
        $customer->status  = $status;
        $customer->save();

        header('Location: ' . BASE_URL . '?act=customers');
        exit;
    }

    // Xóa
    public function delete(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $customer = Customer::find($id);

        if (!$customer) {
            header('Location: ' . BASE_URL . '?act=customers');
            exit;
        }

        // Xóa customer (sẽ tự động soft delete nếu có booking)
        $customer->delete();

        header('Location: ' . BASE_URL . '?act=customers');
        exit;
    }
}

