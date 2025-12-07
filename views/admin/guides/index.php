<?php
/** @var array $profiles */
/** @var array $groupTypes */
/** @var array $filterValues */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Hồ sơ hướng dẫn viên</h3>
        <a href="<?= BASE_URL ?>?act=guide-profile-create" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Tạo hồ sơ
        </a>
      </div>
      <!-- Bộ lọc -->
      <div class="card-body border-bottom">
        <form method="GET" action="<?= BASE_URL ?>?act=guide-profiles" class="row g-3">
          <input type="hidden" name="act" value="guide-profiles">
          
          <div class="col-md-4">
            <label class="form-label">Tìm kiếm</label>
            <input 
              type="text" 
              name="search" 
              class="form-control form-control-sm" 
              placeholder="Tên, email, số điện thoại..."
              value="<?= htmlspecialchars($filterValues['search'] ?? '') ?>"
            >
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Đánh giá tối thiểu</label>
            <select name="rating" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <option value="4.5" <?= ($filterValues['rating'] ?? null) == 4.5 ? 'selected' : '' ?>>4.5+</option>
              <option value="4.0" <?= ($filterValues['rating'] ?? null) == 4.0 ? 'selected' : '' ?>>4.0+</option>
              <option value="3.5" <?= ($filterValues['rating'] ?? null) == 3.5 ? 'selected' : '' ?>>3.5+</option>
              <option value="3.0" <?= ($filterValues['rating'] ?? null) == 3.0 ? 'selected' : '' ?>>3.0+</option>
            </select>
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Loại đoàn</label>
            <select name="group_type" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <?php foreach ($groupTypes as $gt): ?>
                <option value="<?= htmlspecialchars($gt) ?>" <?= ($filterValues['group_type'] ?? null) == $gt ? 'selected' : '' ?>>
                  <?= htmlspecialchars($gt) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a href="<?= BASE_URL ?>?act=guide-profiles" class="btn btn-secondary btn-sm">
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
              <th>Email</th>
              <th>Số ĐT</th>
              <th>Ngôn ngữ</th>
              <th>Đánh giá</th>
              <th>Loại đoàn</th>
              <th style="width: 150px" class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($profiles)): ?>
              <tr>
                <td colspan="8" class="text-center py-4">Chưa có hồ sơ nào.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($profiles as $p): ?>
                <?php
                  $languagesText = '';
                  if (!empty($p['languages'])) {
                      $decoded = json_decode($p['languages'], true);
                      if (is_array($decoded)) {
                          $languagesText = implode(', ', $decoded);
                      }
                  }
                ?>
                <tr>
                  <td><?= (int)$p['id'] ?></td>
                  <td><?= htmlspecialchars($p['user_name'] ?? '') ?></td>
                  <td><?= htmlspecialchars($p['user_email'] ?? '') ?></td>
                  <td><?= htmlspecialchars($p['phone'] ?? '') ?></td>
                  <td><?= htmlspecialchars($languagesText) ?></td>
                  <td>
                    <?php if ($p['rating'] !== null): ?>
                      <span class="badge bg-info">
                        <?= number_format((float)$p['rating'], 1) ?>/5.0
                      </span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($p['group_type']): ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($p['group_type']) ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end" style="white-space: nowrap;">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <a href="<?= BASE_URL ?>?act=guide-profile-show&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="<?= BASE_URL ?>?act=guide-profile-edit&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a
                        href="<?= BASE_URL ?>?act=guide-profile-delete&id=<?= (int)$p['id'] ?>"
                        class="btn btn-sm btn-danger"
                        title="Xóa"
                        onclick="return confirm('Bạn có chắc muốn xóa hồ sơ này? Hành động này không thể hoàn tác!')"
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


