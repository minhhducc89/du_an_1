<?php
/** @var array $users */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách người dùng</h3>
        <a href="<?= BASE_URL ?>?act=user-create" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Tạo người dùng
        </a>
      </div>
      <div class="card-body table-responsive p-0">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'cannot_delete_self'): ?>
          <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <strong>Lỗi!</strong> Bạn không thể xóa chính mình.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <table class="table table-hover table-striped mb-0">
          <thead>
            <tr>
              <th style="width: 60px">ID</th>
              <th>Họ tên</th>
              <th>Email</th>
              <th style="width: 100px">Vai trò</th>
              <th style="width: 100px">Trạng thái</th>
              <th style="width: 100px">Hồ sơ HDV</th>
              <th style="width: 120px">Ngày tạo</th>
              <th style="width: 150px" class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)): ?>
              <tr>
                <td colspan="8" class="text-center py-4">Chưa có người dùng nào.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= (int)$u['id'] ?></td>
                  <td><?= htmlspecialchars($u['name']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td>
                    <?php if ($u['role'] === 'admin'): ?>
                      <span class="badge bg-danger">Admin</span>
                    <?php elseif ($u['role'] === 'guide'): ?>
                      <span class="badge bg-info">Guide</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($u['status'] == 1): ?>
                      <span class="badge bg-success">Hoạt động</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Vô hiệu</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($u['role'] === 'guide'): ?>
                      <?php if ($u['has_profile']): ?>
                        <span class="badge bg-success">Có</span>
                      <?php else: ?>
                        <span class="badge bg-warning">Chưa có</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($u['created_at']) ?></td>
                  <td class="text-end" style="white-space: nowrap;">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <a href="<?= BASE_URL ?>?act=user-edit&id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <?php
                        $currentUser = getCurrentUser();
                        $canDelete = $currentUser && $currentUser->id !== (int)$u['id'];
                      ?>
                      <?php if ($canDelete): ?>
                        <a
                          href="<?= BASE_URL ?>?act=user-delete&id=<?= (int)$u['id'] ?>"
                          class="btn btn-sm btn-danger"
                          title="Xóa"
                          onclick="return confirm('Bạn có chắc muốn xóa người dùng này? Hành động này không thể hoàn tác!')"
                        >
                          <i class="bi bi-trash"></i>
                        </a>
                      <?php endif; ?>
                    </div>
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

