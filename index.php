<?php

// Nạp cấu hình chung của ứng dụng
$config = require __DIR__ . '/config/config.php';

// Nạp các file chứa hàm trợ giúp
require_once __DIR__ . '/src/helpers/helpers.php'; // Helper chứa các hàm trợ giúp (hàm xử lý view, block, asset, session, ...)
require_once __DIR__ . '/src/helpers/database.php'; // Helper kết nối database(kết nối với cơ sở dữ liệu)

// Nạp các file chứa model
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/models/Category.php';
require_once __DIR__ . '/src/models/Tour.php';
require_once __DIR__ . '/src/models/Booking.php';
require_once __DIR__ . '/src/models/GuideProfile.php';
require_once __DIR__ . '/src/models/TourGuest.php';
require_once __DIR__ . '/src/models/Customer.php';

// Nạp các file chứa controller
require_once __DIR__ . '/src/controllers/HomeController.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/CategoryController.php';
require_once __DIR__ . '/src/controllers/TourController.php';
require_once __DIR__ . '/src/controllers/BookingController.php';
require_once __DIR__ . '/src/controllers/GuideProfileController.php';
require_once __DIR__ . '/src/controllers/DashboardController.php';
require_once __DIR__ . '/src/controllers/GuideScheduleController.php';
require_once __DIR__ . '/src/controllers/UserController.php';
require_once __DIR__ . '/src/controllers/CustomerController.php';

// Khởi tạo các controller
$homeController = new HomeController();
$authController = new AuthController();
$categoryController = new CategoryController();
$tourController = new TourController();
$bookingController = new BookingController();
$guideProfileController = new GuideProfileController();
$dashboardController = new DashboardController();
$guideScheduleController = new GuideScheduleController();
$userController = new UserController();
$customerController = new CustomerController();

// Xác định route dựa trên tham số act (mặc định là trang chủ '/')
$act = $_GET['act'] ?? '/';

// Match đảm bảo chỉ một action tương ứng được gọi
match ($act) {
    // Trang welcome (cho người chưa đăng nhập) - mặc định khi truy cập '/'
    '/', 'welcome' => $homeController->welcome(),

    // Trang home (cho người đã đăng nhập)
    'home' => $homeController->home(),

    // Dashboard cho admin
    'dashboard' => $dashboardController->index(),
    'dashboard-revenue-data' => $dashboardController->revenueData(),

    // Lịch trình cho guide
    'guide-schedule' => $guideScheduleController->index(),
    'guide-history' => $guideScheduleController->history(),
    'guide-schedule-detail' => $guideScheduleController->showDetail(),
    'guide-edit-special-requirements' => $guideScheduleController->editSpecialRequirements(),
    'guide-save-special-requirements' => $guideScheduleController->saveSpecialRequirements(),
    'guide-checkin' => $guideScheduleController->checkin(),
    'guide-save-checkin' => $guideScheduleController->saveCheckin(),
    'guide-diary' => $guideScheduleController->diary(),
    'guide-save-diary' => $guideScheduleController->saveDiary(),

    // Quản lý người dùng (chỉ admin)
    'users' => $userController->index(),
    'user-create' => $userController->create(),
    'user-store' => $userController->store(),
    'user-edit' => $userController->edit(),
    'user-update' => $userController->update(),
    'user-delete' => $userController->delete(),

    // Quản lý khách hàng (chỉ admin)
    'customers' => $customerController->index(),
    'customer-create' => $customerController->create(),
    'customer-store' => $customerController->store(),
    'customer-edit' => $customerController->edit(),
    'customer-update' => $customerController->update(),
    'customer-delete' => $customerController->delete(),

    // Đường dẫn đăng nhập, đăng xuất
    'login' => $authController->login(),
    'check-login' => $authController->checkLogin(),
    'logout' => $authController->logout(),

    // Quản lý danh mục tour (categories) - chỉ admin
    'categories'       => $categoryController->index(),
    'category-create'  => $categoryController->create(),
    'category-store'   => $categoryController->store(),
    'category-edit'    => $categoryController->edit(),
    'category-update'  => $categoryController->update(),
    'category-delete'  => $categoryController->delete(),

    // Quản lý tour (tours) - chỉ admin
    'tours'            => $tourController->index(),
    'tour-create'      => $tourController->create(),
    'tour-store'       => $tourController->store(),
    'tour-edit'        => $tourController->edit(),
    'tour-update'      => $tourController->update(),
    'tour-change-status' => $tourController->changeStatus(),
    'tour-show'        => $tourController->show(),
    'tour-delete'      => $tourController->delete(),

    // Quản lý booking - chỉ admin
    'bookings'             => $bookingController->index(),
    'booking-create'       => $bookingController->create(),
    'booking-store'        => $bookingController->store(),
    'booking-edit'         => $bookingController->edit(),
    'booking-update'       => $bookingController->update(),
    'booking-show'         => $bookingController->show(),
    'booking-change-status'=> $bookingController->changeStatus(),
    'booking-assign-guide' => $bookingController->assignGuide(),
    'booking-delete'       => $bookingController->delete(),
    'booking-guest-store'  => $bookingController->storeGuest(),
    'booking-guest-delete' => $bookingController->deleteGuest(),
    'booking-guests-import'=> $bookingController->importGuests(),
    'booking-guests-export'=> $bookingController->exportGuests(),

    // Hồ sơ hướng dẫn viên
    'guide-profiles'        => $guideProfileController->index(),
    'guide-profile-create'  => $guideProfileController->create(),
    'guide-profile-store'   => $guideProfileController->store(),
    'guide-profile-edit'    => $guideProfileController->edit(),
    'guide-profile-update'  => $guideProfileController->update(),
    'guide-profile-show'    => $guideProfileController->show(),
    'guide-profile-delete'  => $guideProfileController->delete(),

    // Đường dẫn không tồn tại
    default => $homeController->notFound(),
};
