<?php

// Controller quản lý tour (tours) - admin quản lý vòng đời sản phẩm
class TourController
{
    // Danh sách tour
    public function index(): void
    {
        requireAdmin();

        $tours      = Tour::all();
        $categories = Category::all(true);

        // Map category_id -> name để hiển thị nhanh
        $categoryNames = [];
        foreach ($categories as $cat) {
            $categoryNames[$cat->id] = $cat->name;
        }

        // Lấy thông báo lỗi nếu có
        $error = $_GET['error'] ?? null;

        ob_start();
        include view_path('admin.tours.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Quản lý tour',
            'pageTitle'  => 'Danh sách tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => url('home')],
                ['label' => 'Quản lý tour', 'url' => url('tours'), 'active' => true],
            ],
        ]);
    }

    // Form thêm mới
    public function create(): void
    {
        requireAdmin();

        $errors     = [];
        $categories = Category::all();

        $old = [
            'name'        => '',
            'description' => '',
            'category_id' => '',
            'price'       => '',
            'status'      => 1,
        ];

        ob_start();
        include view_path('admin.tours.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Thêm tour mới',
            'pageTitle'  => 'Thêm tour mới',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => url('home')],
                ['label' => 'Quản lý tour', 'url' => url('tours')],
                ['label' => 'Thêm mới', 'url' => url('tour-create'), 'active' => true],
            ],
        ]);
    }

    // Lưu thêm mới
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('tours'));
            exit;
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $adultPrice  = $_POST['price'] !== '' ? (float)$_POST['price'] : null;
        $childPrice  = $_POST['child_price'] !== '' ? (float)$_POST['child_price'] : null;
        $duration    = trim($_POST['duration'] ?? '');
        $maxGuests   = $_POST['max_guests'] !== '' ? (int)$_POST['max_guests'] : null;
        $status      = (int)($_POST['status'] ?? 1);

        // Trường nâng cao
        $scheduleText  = trim($_POST['schedule'] ?? '');
        $policiesText  = trim($_POST['policies'] ?? '');
        $suppliersText = trim($_POST['suppliers'] ?? '');

        $errors = [];

        if ($name === '') {
            $errors[] = 'Tên tour không được để trống.';
        }

        if ($category_id <= 0) {
            $errors[] = 'Vui lòng chọn danh mục.';
        }

        $old = [
            'name'        => $name,
            'description' => $description,
            'category_id' => $category_id,
            'price'       => $adultPrice,
            'child_price' => $childPrice,
            'duration'    => $duration,
            'max_guests'  => $maxGuests,
            'status'      => $status,
            'schedule'    => $scheduleText,
            'policies'    => $policiesText,
            'suppliers'   => $suppliersText,
        ];

        $categories = Category::all();

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.tours.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Thêm tour mới',
                'pageTitle'  => 'Thêm tour mới',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => url('home')],
                    ['label' => 'Quản lý tour', 'url' => url('tours')],
                    ['label' => 'Thêm mới', 'url' => url('tour-create'), 'active' => true],
                ],
            ]);
            return;
        }

        // Chuẩn hóa dữ liệu JSON cho các trường nâng cao
        $scheduleJson = $scheduleText !== '' ? json_encode(['text' => $scheduleText], JSON_UNESCAPED_UNICODE) : null;

        // Xử lý upload ảnh
        $imagesJson = null;
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tours';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            $stored = [];
            foreach ($_FILES['images']['name'] as $idx => $name) {
                if ($_FILES['images']['error'][$idx] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $tmpName = $_FILES['images']['tmp_name'][$idx];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                $newName = uniqid('tour_', true) . ($safeExt ? '.' . $safeExt : '');
                $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                if (move_uploaded_file($tmpName, $dest)) {
                    $stored[] = $newName;
                }
            }

            if (!empty($stored)) {
                $imagesJson = json_encode($stored, JSON_UNESCAPED_UNICODE);
            }
        }

        // Giá chi tiết (prices JSON)
        $pricesJson = null;
        if ($adultPrice !== null || $childPrice !== null) {
            $prices = [];
            if ($adultPrice !== null) {
                $prices['adult'] = $adultPrice;
            }
            if ($childPrice !== null) {
                $prices['child'] = $childPrice;
            }
            $pricesJson = json_encode($prices, JSON_UNESCAPED_UNICODE);
        }

        $policiesJson  = $policiesText !== '' ? json_encode(['text' => $policiesText], JSON_UNESCAPED_UNICODE) : null;
        $suppliersJson = $suppliersText !== '' ? json_encode(['text' => $suppliersText], JSON_UNESCAPED_UNICODE) : null;

        $tour = new Tour([
            'name'        => $name,
            'description' => $description,
            'category_id' => $category_id,
            'schedule'    => $scheduleJson,
            'images'      => $imagesJson,
            'prices'      => $pricesJson,
            'policies'    => $policiesJson,
            'suppliers'   => $suppliersJson,
            'price'       => $adultPrice,
            'status'      => $status,
            'duration'    => $duration,
            'max_guests'  => $maxGuests,
        ]);
        $tour->save();

        header('Location: ' . url('tours'));
        exit;
    }

    // Form sửa
    public function edit(): void
    {
        requireAdmin();

        $id   = (int)($_GET['id'] ?? 0);
        $tour = Tour::find($id);

        if (!$tour) {
            header('Location: ' . url('tours'));
            exit;
        }

        $errors     = [];
        $categories = Category::all();

        // Chuẩn bị dữ liệu hiển thị cho các trường nâng cao khi edit
        $scheduleText = '';
        if ($tour->schedule) {
            $decoded = json_decode($tour->schedule, true);
            $scheduleText = is_array($decoded) && isset($decoded['text']) ? $decoded['text'] : $tour->schedule;
        }

        $imagesList = [];
        if ($tour->images) {
            $decoded = json_decode($tour->images, true);
            if (is_array($decoded)) {
                $imagesList = $decoded;
            }
        }

        $policiesText = '';
        if ($tour->policies) {
            $decoded = json_decode($tour->policies, true);
            $policiesText = is_array($decoded) && isset($decoded['text']) ? $decoded['text'] : $tour->policies;
        }

        $suppliersText = '';
        if ($tour->suppliers) {
            $decoded = json_decode($tour->suppliers, true);
            $suppliersText = is_array($decoded) && isset($decoded['text']) ? $decoded['text'] : $tour->suppliers;
        }

        // Giá chi tiết
        $adultPrice = $tour->price;
        $childPrice = null;
        if ($tour->prices) {
            $decodedPrices = json_decode($tour->prices, true);
            if (is_array($decodedPrices)) {
                if (isset($decodedPrices['adult'])) {
                    $adultPrice = $decodedPrices['adult'];
                }
                if (isset($decodedPrices['child'])) {
                    $childPrice = $decodedPrices['child'];
                }
            }
        }

        $old = [
            'id'          => $tour->id,
            'name'        => $tour->name,
            'description' => $tour->description,
            'category_id' => $tour->category_id,
            'price'       => $adultPrice,
            'child_price' => $childPrice,
            'duration'    => $tour->duration,
            'max_guests'  => $tour->max_guests,
            'status'      => $tour->status,
            'schedule'    => $scheduleText,
            'images_list' => $imagesList,
            'policies'    => $policiesText,
            'suppliers'   => $suppliersText,
        ];

        ob_start();
        include view_path('admin.tours.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Sửa tour',
            'pageTitle'  => 'Sửa tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => url('home')],
                ['label' => 'Quản lý tour', 'url' => url('tours')],
                ['label' => 'Chỉnh sửa', 'url' => url('tour-edit', ['id' => $id]), 'active' => true],
            ],
        ]);
    }

    // Cập nhật
    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('tours'));
            exit;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $adultPrice  = $_POST['price'] !== '' ? (float)$_POST['price'] : null;
        $childPrice  = $_POST['child_price'] !== '' ? (float)$_POST['child_price'] : null;
        $duration    = trim($_POST['duration'] ?? '');
        $maxGuests   = $_POST['max_guests'] !== '' ? (int)$_POST['max_guests'] : null;
        $status      = (int)($_POST['status'] ?? 1);

        // Trường nâng cao
        $scheduleText  = trim($_POST['schedule'] ?? '');
        $policiesText  = trim($_POST['policies'] ?? '');
        $suppliersText = trim($_POST['suppliers'] ?? '');

        $tour = Tour::find($id);
        if (!$tour) {
            header('Location: ' . url('tours'));
            exit;
        }

        $errors = [];

        if ($name === '') {
            $errors[] = 'Tên tour không được để trống.';
        }

        if ($category_id <= 0) {
            $errors[] = 'Vui lòng chọn danh mục.';
        }

        $old = [
            'id'          => $id,
            'name'        => $name,
            'description' => $description,
            'category_id' => $category_id,
            'price'       => $adultPrice,
            'child_price' => $childPrice,
            'duration'    => $duration,
            'max_guests'  => $maxGuests,
            'status'      => $status,
            'schedule'    => $scheduleText,
            'policies'    => $policiesText,
            'suppliers'   => $suppliersText,
        ];

        $categories = Category::all();

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.tours.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Sửa tour',
                'pageTitle'  => 'Sửa tour',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => url('home')],
                    ['label' => 'Quản lý tour', 'url' => url('tours')],
                    ['label' => 'Chỉnh sửa', 'url' => url('tour-edit', ['id' => $id]), 'active' => true],
                ],
            ]);
            return;
        }

        $tour->name        = $name;
        $tour->description = $description;
        $tour->category_id = $category_id;
        $tour->price       = $adultPrice;
        $tour->status      = $status;
        $tour->duration    = $duration;
        $tour->max_guests  = $maxGuests;

        // Chuẩn hóa dữ liệu JSON cho các trường nâng cao
        $scheduleJson = $scheduleText !== '' ? json_encode(['text' => $scheduleText], JSON_UNESCAPED_UNICODE) : null;

        // Xử lý upload ảnh (nếu có) và merge với ảnh cũ
        $currentImages = [];
        if ($tour->images) {
            $decoded = json_decode($tour->images, true);
            if (is_array($decoded)) {
                $currentImages = $decoded;
            }
        }

        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tours';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['images']['name'] as $idx => $name) {
                if ($_FILES['images']['error'][$idx] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $tmpName = $_FILES['images']['tmp_name'][$idx];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                $newName = uniqid('tour_', true) . ($safeExt ? '.' . $safeExt : '');
                $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                if (move_uploaded_file($tmpName, $dest)) {
                    $currentImages[] = $newName;
                }
            }
        }

        $imagesJson = !empty($currentImages) ? json_encode($currentImages, JSON_UNESCAPED_UNICODE) : null;

        // Giá chi tiết (prices JSON)
        $pricesJson = null;
        if ($adultPrice !== null || $childPrice !== null) {
            $prices = [];
            if ($adultPrice !== null) {
                $prices['adult'] = $adultPrice;
            }
            if ($childPrice !== null) {
                $prices['child'] = $childPrice;
            }
            $pricesJson = json_encode($prices, JSON_UNESCAPED_UNICODE);
        }

        $policiesJson  = $policiesText !== '' ? json_encode(['text' => $policiesText], JSON_UNESCAPED_UNICODE) : null;
        $suppliersJson = $suppliersText !== '' ? json_encode(['text' => $suppliersText], JSON_UNESCAPED_UNICODE) : null;

        $tour->schedule  = $scheduleJson;
        $tour->images    = $imagesJson;
        $tour->prices    = $pricesJson;
        $tour->policies  = $policiesJson;
        $tour->suppliers = $suppliersJson;
        $tour->save();

        header('Location: ' . url('tours'));
        exit;
    }

    // Đổi trạng thái (vòng đời sản phẩm)
    public function changeStatus(): void
    {
        requireAdmin();

        $id     = (int)($_GET['id'] ?? 0);
        $status = (int)($_GET['status'] ?? 1);

        $tour = Tour::find($id);
        if ($tour) {
            $tour->updateStatus($status);
        }

        header('Location: ' . url('tours'));
        exit;
    }

    // Xóa tour
    public function delete(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $tour = Tour::find($id);

        if (!$tour) {
            header('Location: ' . url('tours'));
            exit;
        }

        // Kiểm tra xem tour có đang được sử dụng trong booking không
        $pdo = getDB();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE tour_id = :tour_id');
            $stmt->execute([':tour_id' => $id]);
            $result = $stmt->fetch();
            $bookingCount = (int)($result['count'] ?? 0);

            if ($bookingCount > 0) {
                // Có booking đang sử dụng tour này, không cho xóa
                header('Location: ' . url('tours', ['error' => 'cannot_delete_tour_with_bookings']));
                exit;
            }
        }

        // Xóa tour
        $tour->delete();

        header('Location: ' . url('tours'));
        exit;
    }

    // Xem chi tiết tour
    public function show(): void
    {
        requireAdmin();

        $id   = (int)($_GET['id'] ?? 0);
        $tour = Tour::find($id);

        if (!$tour) {
            header('Location: ' . url('tours'));
            exit;
        }

        // Lấy tên danh mục
        $categoryName = null;
        if ($tour->category_id) {
            $category = Category::find($tour->category_id);
            $categoryName = $category ? $category->name : null;
        }

        // Decode ảnh
        $images = [];
        if ($tour->images) {
            $decoded = json_decode($tour->images, true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }

        // Decode lịch trình, chính sách, nhà cung cấp (dạng text)
        $scheduleText = '';
        if ($tour->schedule) {
            $decoded = json_decode($tour->schedule, true);
            $scheduleText = is_array($decoded) && isset($decoded['text']) ? $decoded['text'] : $tour->schedule;
        }

        $policiesText = '';
        if ($tour->policies) {
            $decoded = json_decode($tour->policies, true);
            $policiesText = is_array($decoded) && isset($decoded['text']) ? $decoded['text'] : $tour->policies;
        }

        $suppliersText = '';
        if ($tour->suppliers) {
            $decoded = json_decode($tour->suppliers, true);
            $suppliersText = is_array($decoded) && isset($decoded['text']) ? $decoded['text'] : $tour->suppliers;
        }

        // Giá chi tiết
        $adultPrice = $tour->price;
        $childPrice = null;
        if ($tour->prices) {
            $decodedPrices = json_decode($tour->prices, true);
            if (is_array($decodedPrices)) {
                if (isset($decodedPrices['adult'])) {
                    $adultPrice = $decodedPrices['adult'];
                }
                if (isset($decodedPrices['child'])) {
                    $childPrice = $decodedPrices['child'];
                }
            }
        }

        ob_start();
        include view_path('admin.tours.show');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Chi tiết tour - ' . $tour->name,
            'pageTitle'  => 'Chi tiết tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => url('home')],
                ['label' => 'Quản lý tour', 'url' => url('tours')],
                ['label' => 'Chi tiết', 'url' => url('tour-show', ['id' => $id]), 'active' => true],
            ],
        ]);
    }
}


