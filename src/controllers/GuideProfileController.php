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
    }



































































































}
