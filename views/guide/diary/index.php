<?php
/** @var array $booking */
/** @var array $diaryEntries */
?>

<div class="row">
  <div class="col-12">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Ghi nhật ký tour</h3>
        <a href="<?= BASE_URL ?>?act=guide-schedule" class="btn btn-sm btn-secondary">
          <i class="bi bi-arrow-left"></i> Quay lại
        </a>
      </div>
      <div class="card-body">
        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            Đã lưu nhật ký thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>?act=guide-save-diary" enctype="multipart/form-data">
          <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
          
          <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề sự việc</label>
            <input
              type="text"
              class="form-control"
              id="title"
              name="title"
              required
              placeholder="Ví dụ: Khách đến muộn, Xe hỏng, Thay đổi lịch trình..."
            >
          </div>

          <div class="mb-3">
            <label for="content" class="form-label">Nội dung chi tiết</label>
            <textarea
              class="form-control"
              id="content"
              name="content"
              rows="5"
              required
              placeholder="Mô tả chi tiết sự việc..."
            ></textarea>
          </div>

          <div class="mb-3">
            <label for="cost" class="form-label">Chi phí phát sinh (VNĐ)</label>
            <input
              type="number"
              class="form-control"
              id="cost"
              name="cost"
              min="0"
              step="1000"
              placeholder="Nhập số tiền nếu có"
            >
          </div>

          <div class="mb-3">
            <label for="images" class="form-label">Hình ảnh đính kèm</label>
            <input
              type="file"
              class="form-control"
              id="images"
              name="images[]"
              multiple
              accept="image/*"
            >
            <small class="text-muted">Có thể chọn nhiều ảnh</small>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Lưu nhật ký
          </button>
        </form>
      </div>
    </div>

    <!-- Danh sách nhật ký đã ghi -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Lịch sử nhật ký</h3>
      </div>
      <div class="card-body">
        <?php if (empty($diaryEntries)): ?>
          <p class="text-muted mb-0">Chưa có nhật ký nào.</p>
        <?php else: ?>
          <div class="list-group">
            <?php foreach (array_reverse($diaryEntries) as $entry): ?>
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="mb-0"><?= htmlspecialchars($entry['title'] ?? 'Không có tiêu đề') ?></h5>
                  <small class="text-muted"><?= htmlspecialchars($entry['created_at'] ?? '') ?></small>
                </div>
                <p class="mb-2"><?= nl2br(htmlspecialchars($entry['content'] ?? '')) ?></p>
                <?php if (isset($entry['cost']) && $entry['cost'] > 0): ?>
                  <p class="mb-2">
                    <strong>Chi phí:</strong> 
                    <span class="text-danger"><?= number_format($entry['cost'], 0, ',', '.') ?> đ</span>
                  </p>
                <?php endif; ?>
                <?php if (!empty($entry['images']) && is_array($entry['images'])): ?>
                  <div class="mt-2">
                    <?php foreach ($entry['images'] as $img): ?>
                      <img
                        src="<?= asset('uploads/diary/' . $img) ?>"
                        alt="Diary image"
                        class="img-thumbnail me-2 mb-2"
                        style="max-width: 150px; max-height: 150px; object-fit: cover;"
                      >
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

