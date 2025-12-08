<?php
/** @var Tour $tour */
/** @var string|null $categoryName */
/** @var array $images */
/** @var string $scheduleText */
/** @var string $policiesText */
/** @var string $suppliersText */
/** @var float|null $adultPrice */
/** @var float|null $childPrice */
?>

<div class="row">
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Thông tin tour</h3>
        <a href="<?= BASE_URL ?>?act=tour-edit&id=<?= (int)$tour->id ?>" class="btn btn-sm btn-warning">
          <i class="bi bi-pencil-square me-1"></i> Sửa
        </a>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Tên tour</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($tour->name) ?></dd>

          <dt class="col-sm-4">Danh mục</dt>
          <dd class="col-sm-8">
            <?= htmlspecialchars($categoryName ?? 'Không xác định') ?>
          </dd>

          <dt class="col-sm-4">Giá người lớn</dt>
          <dd class="col-sm-8">
            <?php if ($adultPrice !== null): ?>
              <?= number_format($adultPrice, 0, ',', '.') ?> đ
            <?php else: ?>
              -
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Giá trẻ em</dt>
          <dd class="col-sm-8">
            <?php if ($childPrice !== null): ?>
              <?= number_format($childPrice, 0, ',', '.') ?> đ
            <?php else: ?>
              -
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Số ngày/đêm</dt>
          <dd class="col-sm-8">
            <?= htmlspecialchars($tour->duration ?? '') ?: '-' ?>
          </dd>

          <dt class="col-sm-4">Số khách tối đa</dt>
          <dd class="col-sm-8">
            <?= $tour->max_guests !== null ? (int)$tour->max_guests : '-' ?>
          </dd>

          <dt class="col-sm-4">Trạng thái</dt>
          <dd class="col-sm-8">
            <?php if ($tour->status == 1): ?>
              <span class="badge bg-success">Đang bán</span>
            <?php else: ?>
              <span class="badge bg-secondary">Ngừng bán</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Mô tả</dt>
          <dd class="col-sm-8">
            <?= nl2br(htmlspecialchars($tour->description)) ?: '<span class="text-muted">Chưa có</span>' ?>
          </dd>
        </dl>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Lịch trình chi tiết</h3>
      </div>
      <div class="card-body">
        <?php 
          $scheduleTextSafe = is_string($scheduleText) ? trim($scheduleText) : '';
        ?>
        <?php if ($scheduleTextSafe !== ''): ?>
          <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($scheduleTextSafe) ?></pre>
        <?php else: ?>
          <p class="text-muted mb-0">Chưa có thông tin lịch trình.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Chính sách</h3>
      </div>
      <div class="card-body">
        <?php 
          $policiesTextSafe = is_string($policiesText) ? trim($policiesText) : '';
        ?>
        <?php if ($policiesTextSafe !== ''): ?>
          <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($policiesTextSafe) ?></pre>
        <?php else: ?>
          <p class="text-muted mb-0">Chưa có chính sách.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Nhà cung cấp</h3>
      </div>
      <div class="card-body">
        <?php 
          $suppliersTextSafe = is_string($suppliersText) ? trim($suppliersText) : '';
        ?>
        <?php if ($suppliersTextSafe !== ''): ?>
          <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($suppliersTextSafe) ?></pre>
        <?php else: ?>
          <p class="text-muted mb-0">Chưa có thông tin nhà cung cấp.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Hình ảnh tour</h3>
      </div>
      <div class="card-body">
        <?php if (!empty($images)): ?>
          <div class="row g-2">
            <?php foreach ($images as $img): ?>
              <div class="col-6">
                <div class="border rounded overflow-hidden">
                  <img
                    src="<?= asset('uploads/tours/' . $img) ?>"
                    alt="Tour image"
                    style="width: 100%; height: 100px; object-fit: cover;"
                  >
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">Chưa có hình ảnh.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>


