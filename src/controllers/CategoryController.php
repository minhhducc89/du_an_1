<?php
// controller qly danh muc(admin)

class CategoryController{
    public function index(): void
    {
        requireAdmin();

        $categories = Category::all(true);

        ob_start();
        include view_path('admin.categories.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Quản lý danh mục tour',
            'pageTitle'  => 'Danh mục tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => url('home')],
                ['label' => 'Danh mục tour', 'url' => url('categories'), 'active' => true],
            ],
        ]);
    }
    //form them

    public function create(): variant_mod{
        requireAdmin();
        $errors = [];
        $old = ['name' => '', 'description' => '', 'status' => 1];

        ob_start();
        include view_path('admin.categories.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Thêm danh mục tour',
            'pageTitle'  => 'Thêm danh mục tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => url('home')],
                ['label' => 'Danh mục tour', 'url' => url('categories')],
                ['label' => 'Thêm mới', 'url' => url('category-create'), 'active' => true],
            ],
        ]);
    }
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('categories'));
            exit;
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = (int)($_POST['status'] ?? 1);

        $errors = [];

        if ($name === '') {
            $errors[] = 'Tên danh mục không được để trống.';
        } elseif (Category::existsByName($name)) {
            $errors[] = 'Tên danh mục đã tồn tại, vui lòng chọn tên khác.';
        }

        $old = [
            'name'        => $name,
            'description' => $description,
            'status'      => $status,
        ];

        if (!empty($errors)) {
            ob_start();
            include view_path('admin.categories.form');
            $content = ob_get_clean();

            view('layouts.AdminLayout', [
                'title'      => 'Thêm danh mục tour',
                'pageTitle'  => 'Thêm danh mục tour',
                'content'    => $content,
                'breadcrumb' => [
                    ['label' => 'Trang chủ', 'url' => url('home')],
                    ['label' => 'Danh mục tour', 'url' => url('categories')],
                    ['label' => 'Thêm mới', 'url' => url('category-create'), 'active' => true],
                ],
            ]);
            return;
        }

        $category = new Category([
            'name'        => $name,
            'description' => $description,
            'status'      => $status,
        ]);
        $category->save();

        header('Location: ' . url('categories'));
        exit;
    }

    // Form sửa
    public function edit(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $category = Category::find($id);

        if (!$category) {
            header('Location: ' . url('categories'));
            exit;
        }

        $errors = [];
        $old = [
            'id'          => $category->id,
            'name'        => $category->name,
            'description' => $category->description,
            'status'      => $category->status,
        ];

        ob_start();
        include view_path('admin.categories.form');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Sửa danh mục tour',
            'pageTitle'  => 'Sửa danh mục tour',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Trang chủ', 'url' => url('home')],
                ['label' => 'Danh mục tour', 'url' => url('categories')],
                ['label' => 'Chỉnh sửa', 'url' => url('category-edit', ['id' => $id]), 'active' => true],
            ],
        ]);
    }
    public function update(): void
}