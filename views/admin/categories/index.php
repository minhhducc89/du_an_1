<?php
/** @var Category[] $categories */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách danh mục tour</h3>
        <a href="<?= BASE_URL ?>?act=category-create" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Thêm danh mục
        </a>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0">
          <thead>
            <tr>
              <th style="width: 60px">ID</th>
              <th>Tên danh mục</th>
              <th>Mô tả</th>
              <th style="width: 120px">Trạng thái</th>
              <th style="width: 160px" class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($categories)): ?>
              <tr>
                <td colspan="5" class="text-center py-4">Chưa có danh mục nào.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($categories as $cat): ?>
                <tr>
                  <td><?= (int)$cat->id ?></td>
                  <td><?= htmlspecialchars($cat->name) ?></td>
                  <td><?= htmlspecialchars($cat->description) ?></td>
                  <td>
                    <?php if ($cat->status == 1): ?>
                      <span class="badge bg-success">Hoạt động</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Ngừng sử dụng</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <a href="<?= BASE_URL ?>?act=category-edit&id=<?= (int)$cat->id ?>" class="btn btn-sm btn-warning">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <?php if ($cat->status == 1): ?>
                      <a
                        href="<?= BASE_URL ?>?act=category-delete&id=<?= (int)$cat->id ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');"
                      >
                        <i class="bi bi-trash"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


