<?php
// Controller chịu trách nhiệm xử lý logic cho các trang cơ bản
class HomeController
{
    // Trang welcome - hiển thị cho người chưa đăng nhập
    // Nếu đã đăng nhập thì redirect về trang home
    public function welcome(): void
    {
        // Nếu đã đăng nhập thì redirect theo role
        if (isLoggedIn()) {
            $user = getCurrentUser();
            if ($user && $user->isAdmin()) {
                header('Location: ' . url('dashboard'));
            } elseif ($user && $user->isGuide()) {
                header('Location: ' . url('guide-schedule'));
            } else {
                header('Location: ' . url('home'));
            }
            exit;
        }

        // Hiển thị view welcome
        view('welcome', [
            'title' => 'Chào mừng - Website Quản Lý Tour',
        ]);
    }

    // Trang home - chỉ dành cho người đã đăng nhập
    // Nếu chưa đăng nhập thì redirect về trang welcome
    // Nếu đã đăng nhập thì redirect theo role
    public function home(): void
    {
        // Yêu cầu phải đăng nhập, nếu chưa thì redirect về welcome
        if (!isLoggedIn()) {
            header('Location: ' . url('welcome'));
            exit;
        }

        // Lấy thông tin user hiện tại (đã đảm bảo đăng nhập ở trên)
        $currentUser = getCurrentUser();

        // Redirect theo role
        if ($currentUser && $currentUser->isAdmin()) {
            header('Location: ' . url('dashboard'));
            exit;
        } elseif ($currentUser && $currentUser->isGuide()) {
            header('Location: ' . url('guide-schedule'));
            exit;
        }

        // Hiển thị view home với dữ liệu title và user
        view('home', [
            'title' => 'Trang chủ - Website Quản Lý Tour',
            'user' => $currentUser,
        ]);
    }

    // Trang hiển thị khi route không tồn tại
    public function notFound(): void
    {
        http_response_code(404);
        // Hiển thị view not_found với dữ liệu title
        view('not_found', [
            'title' => 'Không tìm thấy trang',
        ]);
    }
}

