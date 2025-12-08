<?php

class UserController
{
    // Danh sách người dùng (chỉ admin)
    public function index(): void
    {
        requireAdmin();

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $sql = "
            SELECT u.*, 
                   CASE WHEN gp.id IS NOT NULL THEN 1 ELSE 0 END AS has_profile
            FROM users u
            LEFT JOIN guide_profiles gp ON gp.user_id = u.id
            ORDER BY u.created_at DESC
        ";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll();

        ob_start();
        include view_path('admin.users.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Quản lý người dùng',
            'pageTitle'  => 'Danh sách người dùng',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                ['label' => 'Người dùng', 'url' => BASE_URL . '?act=users', 'active' => true],
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
            'email'    => '',
            'password' => '',
            'role'     => 'guide',
            'status'   => 1,
        ];

        ob_start();
        include view_path('admin.users.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Tạo người dùng mới',
            'pageTitle'  => 'Tạo người dùng mới',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                ['label' => 'Người dùng', 'url' => BASE_URL . '?act=users'],
                ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=user-create', 'active' => true],
            ],
        ]);
    }

    // Lưu tạo mới
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=user-create');
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? 'guide');
        $status   = (int)($_POST['status'] ?? 1);

        $errors = [];

        if ($name === '') {
            $errors[] = 'Họ tên không được để trống.';
        }
        if ($email === '') {
            $errors[] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        } elseif (User::existsByEmail($email)) {
            $errors[] = 'Email đã tồn tại, vui lòng chọn email khác.';
        }
        if ($password === '') {
            $errors[] = 'Mật khẩu không được để trống.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }
        if (!in_array($role, ['admin', 'guide'], true)) {
            $errors[] = 'Vai trò không hợp lệ.';
        }

        $old = [
            'name'     => $name,
            'email'    => $email,
            'password' => '',
            'role'     => $role,
            'status'   => $status,
        ];

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.users.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Tạo người dùng mới',
                'pageTitle'  => 'Tạo người dùng mới',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                    ['label' => 'Người dùng', 'url' => BASE_URL . '?act=users'],
                    ['label' => 'Tạo mới', 'url' => BASE_URL . '?act=user-create', 'active' => true],
                ],
            ]);
            return;
        }

        $user = new User([
            'name'   => $name,
            'email'  => $email,
            'role'   => $role,
            'status' => $status,
        ]);
        $user->save($password);

        header('Location: ' . BASE_URL . '?act=users');
        exit;
    }

    // Form sửa
    public function edit(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $user = User::find($id);

        if (!$user) {
            header('Location: ' . BASE_URL . '?act=users');
            exit;
        }

        $errors = [];
        $old = [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'password' => '',
            'role'     => $user->role,
            'status'   => $user->status,
        ];

        ob_start();
        include view_path('admin.users.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Sửa người dùng',
            'pageTitle'  => 'Sửa người dùng',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                ['label' => 'Người dùng', 'url' => BASE_URL . '?act=users'],
                ['label' => 'Chỉnh sửa', 'url' => BASE_URL . '?act=user-edit&id=' . $id, 'active' => true],
            ],
        ]);
    }

    // Cập nhật
    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?act=users');
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? 'guide');
        $status   = (int)($_POST['status'] ?? 1);

        $user = User::find($id);
        if (!$user) {
            header('Location: ' . BASE_URL . '?act=users');
            exit;
        }

        $errors = [];

        if ($name === '') {
            $errors[] = 'Họ tên không được để trống.';
        }
        if ($email === '') {
            $errors[] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        } elseif (User::existsByEmail($email, $id)) {
            $errors[] = 'Email đã tồn tại, vui lòng chọn email khác.';
        }
        if ($password !== '' && strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }
        if (!in_array($role, ['admin', 'guide'], true)) {
            $errors[] = 'Vai trò không hợp lệ.';
        }

        $old = [
            'id'       => $user->id,
            'name'     => $name,
            'email'    => $email,
            'password' => '',
            'role'     => $role,
            'status'   => $status,
        ];

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.users.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Sửa người dùng',
                'pageTitle'  => 'Sửa người dùng',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => BASE_URL . '?act=dashboard'],
                    ['label' => 'Người dùng', 'url' => BASE_URL . '?act=users'],
                    ['label' => 'Chỉnh sửa', 'url' => BASE_URL . '?act=user-edit&id=' . $id, 'active' => true],
                ],
            ]);
            return;
        }

        $user->name   = $name;
        $user->email  = $email;
        $user->role   = $role;
        $user->status = $status;
        
        // Chỉ cập nhật password nếu có nhập
        if ($password !== '') {
            $user->save($password);
        } else {
            $user->save();
        }

        header('Location: ' . BASE_URL . '?act=users');
        exit;
    }

    // Xóa
    public function delete(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $user = User::find($id);

        if (!$user) {
            header('Location: ' . BASE_URL . '?act=users');
            exit;
        }

        // Không cho xóa chính mình
        $currentUser = getCurrentUser();
        if ($currentUser && $currentUser->id === $user->id) {
            header('Location: ' . BASE_URL . '?act=users&error=cannot_delete_self');
            exit;
        }

        // Xóa user
        $user->delete();

        header('Location: ' . BASE_URL . '?act=users');
        exit;
    }
}

