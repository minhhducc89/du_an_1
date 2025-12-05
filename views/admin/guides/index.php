<?php
/** @var array $profiles */
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


