<?php
/** @var array $customers */
/** @var array $filterValues */
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
      <!-- Bộ lọc -->
      <div class="card-body border-bottom">
        <form method="GET" action="<?= BASE_URL ?>?act=customers" class="row g-3">
          <input type="hidden" name="act" value="customers">
          
          <div class="col-md-4">
            <label class="form-label">Tìm kiếm</label>
            <input 
              type="text" 
              name="search" 
              class="form-control form-control-sm" 
              placeholder="Tên, SĐT, Email, Công ty..."
              value="<?= htmlspecialchars($filterValues['search'] ?? '') ?>"
            >
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <option value="1" <?= ($filterValues['status'] ?? null) == 1 ? 'selected' : '' ?>>Hoạt động</option>
              <option value="0" <?= ($filterValues['status'] ?? null) === 0 ? 'selected' : '' ?>>Vô hiệu</option>
            </select>
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Có booking</label>
            <select name="has_booking" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <option value="yes" <?= ($filterValues['has_booking'] ?? null) == 'yes' ? 'selected' : '' ?>>Có booking</option>
              <option value="no" <?= ($filterValues['has_booking'] ?? null) == 'no' ? 'selected' : '' ?>>Chưa có booking</option>
            </select>
          </div>
          
          <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a href="<?= BASE_URL ?>?act=customers" class="btn btn-secondary btn-sm">
              <i class="bi bi-x-circle me-1"></i> Xóa bộ lọc
            </a>
          </div>
        </form>
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

