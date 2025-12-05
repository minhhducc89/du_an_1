<?php
/** @var array $customers */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách khách hàng</h3>
        <a href="<?= BASE_URL ?>?act=customer-create" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Tạo khách hàng
        </a>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0">
          <thead>
            <tr>
              <th style="width: 60px">ID</th>
              <th>Họ tên</th>
              <th>Số điện thoại</th>
              <th>Email</th>
              <th>Công ty</th>
              <th style="width: 100px">Số booking</th>
              <th style="width: 100px">Trạng thái</th>
              <th style="width: 150px" class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($customers)): ?>
              <tr>
                <td colspan="8" class="text-center py-4">Chưa có khách hàng nào.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($customers as $c): ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>
                  <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                  <td><?= htmlspecialchars($c['phone']) ?></td>
                  <td><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($c['company'] ?? '-') ?></td>
                  <td>
                    <span class="badge bg-info"><?= (int)($c['booking_count'] ?? 0) ?></span>
                  </td>
                  <td>
                    <?php if ($c['status'] == 1): ?>
                      <span class="badge bg-success">Hoạt động</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Vô hiệu</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end" style="white-space: nowrap;">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <a href="<?= BASE_URL ?>?act=customer-edit&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a
                        href="<?= BASE_URL ?>?act=customer-delete&id=<?= (int)$c['id'] ?>"
                        class="btn btn-sm btn-danger"
                        title="Xóa"
                        onclick="return confirm('Bạn có chắc muốn xóa khách hàng này? Hành động này không thể hoàn tác!')"
                      >
                        <i class="bi bi-trash"></i>
                      </a>
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

