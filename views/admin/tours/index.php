<?php
/** @var Tour[] $tours */
/** @var array $categoryNames */
/** @var string|null $error */
/** @var array $filterValues */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách tour</h3>
        <a href="<?= BASE_URL ?>?act=tour-create" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Thêm tour
        </a>
      </div>
      <!-- Bộ lọc -->
      <div class="card-body border-bottom">
        <form method="GET" action="<?= BASE_URL ?>?act=tours" class="row g-3">
          <input type="hidden" name="act" value="tours">
          
          <div class="col-md-3">
            <label class="form-label">Tìm kiếm</label>
            <input 
              type="text" 
              name="search" 
              class="form-control form-control-sm" 
              placeholder="Tên tour, mô tả..."
              value="<?= htmlspecialchars($filterValues['search'] ?? '') ?>"
            >
          </div>
          
          <div class="col-md-3">
            <label class="form-label">Danh mục</label>
            <select name="category" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <?php foreach ($categoryNames as $catId => $catName): ?>
                <option value="<?= $catId ?>" <?= ($filterValues['category'] ?? null) == $catId ? 'selected' : '' ?>>
                  <?= htmlspecialchars($catName) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="col-md-2">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select form-select-sm">
              <option value="">Tất cả</option>
              <option value="1" <?= ($filterValues['status'] ?? null) == 1 ? 'selected' : '' ?>>Đang bán</option>
              <option value="0" <?= ($filterValues['status'] ?? null) === 0 ? 'selected' : '' ?>>Ngừng bán</option>
            </select>
          </div>
          
          <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a href="<?= BASE_URL ?>?act=tours" class="btn btn-secondary btn-sm">
              <i class="bi bi-x-circle me-1"></i> Xóa bộ lọc
            </a>
          </div>
        </form>
      </div>
      
      <div class="card-body table-responsive p-0">
        <?php if ($error === 'cannot_delete_tour_with_bookings'): ?>
          <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <strong>Lỗi!</strong> Không thể xóa tour này vì đang có booking sử dụng tour này.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <table class="table table-hover table-striped mb-0">
          <thead>
            <tr>
              <th style="width: 60px">ID</th>
              <th style="width: 100px">Ảnh</th>
              <th>Tên tour</th>
              <th>Danh mục</th>
              <th style="width: 140px">Giá cơ bản</th>
              <th style="width: 120px">Trạng thái</th>
              <th style="width: 280px" class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($tours)): ?>
              <tr>
                <td colspan="6" class="text-center py-4">Chưa có tour nào.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($tours as $tour): ?>
                <tr>
                  <td><?= (int)$tour->id ?></td>
                  <td>
                    <?php
                      $thumb = null;
                      if (!empty($tour->images)) {
                          $decoded = json_decode($tour->images, true);
                          if (is_array($decoded) && !empty($decoded)) {
                              $thumb = $decoded[0];
                          }
                      }
                    ?>
                    <?php if ($thumb): ?>
                      <img
                        src="<?= asset('uploads/tours/' . $thumb) ?>"
                        alt="Tour image"
                        style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;"
                      >
                    <?php else: ?>
                      <span class="text-muted">Không có ảnh</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($tour->name) ?></td>
                  <td>
                    <?= htmlspecialchars($categoryNames[$tour->category_id] ?? 'Không xác định') ?>
                  </td>
                  <td>
                    <?php if ($tour->price !== null): ?>
                      <?= number_format($tour->price, 0, ',', '.') ?> đ
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($tour->status == 1): ?>
                      <span class="badge bg-success">Đang bán</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Ngừng bán</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end" style="white-space: nowrap;">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <a href="<?= BASE_URL ?>?act=tour-show&id=<?= (int)$tour->id ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="<?= BASE_URL ?>?act=tour-edit&id=<?= (int)$tour->id ?>" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <?php if ($tour->status == 1): ?>
                        <a
                          href="<?= BASE_URL ?>?act=tour-change-status&id=<?= (int)$tour->id ?>&status=0"
                          class="btn btn-sm btn-outline-secondary"
                          title="Ngừng bán"
                        >
                          <i class="bi bi-pause-circle"></i>
                        </a>
                      <?php else: ?>
                        <a
                          href="<?= BASE_URL ?>?act=tour-change-status&id=<?= (int)$tour->id ?>&status=1"
                          class="btn btn-sm btn-outline-success"
                          title="Mở bán"
                        >
                          <i class="bi bi-play-circle"></i>
                        </a>
                      <?php endif; ?>
                      <a
                        href="<?= BASE_URL ?>?act=tour-delete&id=<?= (int)$tour->id ?>"
                        class="btn btn-sm btn-danger"
                        title="Xóa"
                        onclick="return confirm('Bạn có chắc muốn xóa tour này? Hành động này không thể hoàn tác!')"
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


