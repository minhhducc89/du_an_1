<?php

class GuideProfile{
        public function index(): void
    {
      requireAdmin();

      $pdo = getDB();
      if($pdo === null){
        throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
      }
      $sql = "
      SELECT gp.*, u.name AS user_name, u.emial AS user_email
      FROM guide_pofiles gp
      LEFT JOIN users u ON u.id = gp.user_id
      ORDER BY gp.create_at DESC";
      $stmt = $pdo->query($sql);
      $profiles = $stmt->fetchAll();

      ob_start();
      include view_path('admin.guide.index');
      $content = ob_get-clean();

      view('layout.Adminlayout', [
        'tilte' => 'Hồ sơ hướng dẫn viên',
        'pageTitle'=> 'Hồ sơ hướng dẫn viên',
        'content'=> $content,
        'breadcrum'=>[
            ['label' => 'Trang chủ', 'url' => BASE_URL .'home' ],
            ['label' => 'Hồ sơ HDV', 'url' => BASE_URL .'?act=guide-profiles', 'active' => true ],

        ],
    ]);
    }

    // Create

    public function create(): void 
    {
        requireAdmin();

      $pdo = getDB();
      if($pdo === null){
        throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
      }

      // danh sach guide chua co profile

      $sql = "
      SELECT u.*
      FROM user u
      WHERE u.role = 'guide'
      AND u.status = 1
      AND NOT EXISTS(
      SELECT 1 FROM guide-profile gp  WHERE gp.user_id = u.id
      )
      ORDER BY u.name
      ";

      $guides = $pdo->query($sql)->fetchAll();
      $errors = [];
      $old = [
        'user_id'  => '',
        'birthdate' => '',
        'phone' => '',
        'certificate' => '',
        'languages' => '',
        'experience' => '',
        'rating' => '',
        'health_status' => '',
        'group_type' => '',
        'speciality' => '',
      ];

        ob_start();
        include view_path('admin.guide.form');
        $content = ob_get_clean();
        view('layout.Adminlayout', [
        'tilte' => 'Tạo hồ sơ HDV',
        'pageTitle'=> 'Tạo hồ sơ HDV',
        'content'=> $content,
        'breadcrum'=>[
            ['label' => 'Trang chủ', 'url' => BASE_URL .'home' ],
            ['label' => 'Hồ sơ HDV', 'url' => BASE_URL .'?act=guide-profiles'],
            ['label' => 'Tạo mới', 'url' => BASE_URL .'?act=guide-profiles-create', 'active' => true ],
        
            ]]
        )
    }

    public function store(): void
    {
        requireAdmin();
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location:' . BASE_URL . '?act=guide-profile-create');
            exit;
        }

         $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        $userId = (int)($_POST['user_id'] ?? 0 );
        $birthdate = $_POST['birthdate'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $certificate = trim($_POST['certificate'] ?? '');
        $languages = trim($_POST['languages'] ?? '');
        $experience = trim($_POST['experience'] ?? '');
        $rating = $_POST['rating'] !== '' ? (float)$_POST['rating'] : null;
        $healthStatus = trim($_POST['health_status'] ?? '');
        $groupType = trim($_POST['group_type'] ?? '');
        $speciality = trim($_POST['speciality'] ?? '');
        
        $errors = [];

    if($userId <= 0){
        $errors[] = 'Vui lòng chọn hướng dẫn viên.';
    }
    if($phone === ''){
        $errors[] = 'Vui lòng nhập số điện thoại.';
    }

    $old = [
        'user_id'  => $userId,
        'birthdate' => $birthdate,
        'phone' => $phone,
        'certificate' => $certificate,
        'languages' => $languages,
        'experience' => $experience,
        'rating' => $rating,
        'health_status' => $healthStatus,
        'group_type' => $groupType,
        'speciality' => $speciality,
    ];

    // lay danh sach guide select

    $sql = "
    SELECT u.*
    FROM user u
    WHERE u.role = 'guide'
    AND u.status = 1
    ORDER BY u.name    
    ";


    $guides = $pdo->query($sql)->fetchAll();
    if(!empty($errors)){
        ob_start();
        view('layout.Adminlayout', [
        'tilte' => 'Tạo hồ sơ HDV',
        'pageTitle'=> 'Tạo hồ sơ HDV',
        'content'=> $content,
        'breadcrum'=>[
            ['label' => 'Trang chủ', 'url' => BASE_URL .'home' ],
            ['label' => 'Hồ sơ HDV', 'url' => BASE_URL .'?act=guide-profiles',],
            ['label' => 'Tạo mới','url' => BASE_URL . '?act-guide-profile-create', 'active' => true],
        ],
    ]);
    return;
}

       //xu ly upload avata
        $avataFile = null;
        if(!empty($_FILES['avata']['name']) && $_FILES['avata']['error'] === UPLOAD_ERR_OK){
            $uploadDir = BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'guides';
            if(!is_dir($uploadDir)){
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

        //laguage json
        $languagesJson = null;
        if($languages !== ''){
            $arr = array_values(array_filter(array_map('trim', preg_split('/[,;]/', $languages))));
            if(!empty($arr)){
                $languagesJson = json_encode($arr , JSON_UNESCAPED_UNICODE);

            }    
        }

        $profile = new GuideProfile({
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
        });
        $profile->save();
        header('Location:' . BASE_URL . '?act=guide-profiles');
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

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

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
            ],
        ]);
    }

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

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

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
        exit;
    }
}



































































































}
