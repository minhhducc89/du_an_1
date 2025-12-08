<?php

function createPasswordHash($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}


class AuthController
{
    // Hiển thị form đăng nhập
    public function login()
    {
        if (isLoggedIn()) {
            $user = getCurrentUser();
            // Redirect theo role
            if ($user && $user->isAdmin()) {
                header('Location: ' . url('dashboard'));
            } elseif ($user && $user->isGuide()) {
                header('Location: ' . url('guide-schedule'));
            } else {
            header('Location: ' . url('home'));
            }
            exit;   
        }

        $redirect = $_GET['redirect'] ?? url('home');

        view('auth.login', [
            'title'    => 'Đăng nhập',
            'redirect' => $redirect,
        ]);
    }

    // Xử lý đăng nhập
    public function checkLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('login'));
            exit;
        }

        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $redirect = $_POST['redirect'] ?? url('home');

        $errors = [];

        if (empty($email)) {
            $errors[] = 'Vui lòng nhập email';
        }

        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }

        if (!empty($errors)) {
            view('auth.login', [
                'title'    => 'Đăng nhập',
                'errors'   => $errors,
                'email'    => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Kết nối DB
        $pdo = getDB();
        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
            view('auth.login', [
                'title'    => 'Đăng nhập',
                'errors'   => $errors,
                'email'    => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Lấy user theo email
        $stmt = $pdo->prepare(
            'SELECT * FROM users WHERE email = :email AND status = 1 LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $userRow = $stmt->fetch();

        // Kiểm tra user + mật khẩu
        if (!$userRow || !password_verify($password, $userRow['password'])) {
            $errors[] = 'Email hoặc mật khẩu không chính xác.';
            view('auth.login', [
                'title'    => 'Đăng nhập',
                'errors'   => $errors,
                'email'    => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Tạo object User
        $user = new User([
            'id'     => $userRow['id'],
            'name'   => $userRow['name'],
            'email'  => $userRow['email'],
            'role'   => $userRow['role'], // admin / guide
            'status' => $userRow['status'],
        ]);

        // Lưu session
        loginUser($user);

        // Redirect theo role
        if ($user->isAdmin()) {
            header('Location: ' . url('dashboard'));
        } elseif ($user->isGuide()) {
            header('Location: ' . url('guide-schedule'));
        } else {
            header('Location: ' . url('home'));
        }
        exit;
    }

    // Đăng xuất
    public function logout()
    {
        logoutUser();
        header('Location: ' . url('welcome'));
        exit;
    }
}
