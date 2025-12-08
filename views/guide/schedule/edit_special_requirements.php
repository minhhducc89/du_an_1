<?php
/** @var array $booking */
/** @var string $currentSpecialReqs */
?>

<div class="row">
  <div class="col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Cập nhật yêu cầu đặc biệt</h3>
        <a href="<?= BASE_URL ?>?act=guide-schedule-detail&id=<?= (int)$booking['id'] ?>" class="btn btn-sm btn-secondary">
          <i class="bi bi-arrow-left"></i> Quay lại
        </a>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="bi bi-info-circle"></i>
          <strong>Lưu ý:</strong> Bạn có thể thêm hoặc cập nhật yêu cầu đặc biệt của khách (ăn chay, bệnh lý, v.v.) để chuẩn bị phục vụ phù hợp suốt tour.
        </div>

        <form method="post" action="<?= BASE_URL ?>?act=guide-save-special-requirements">
          <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">

          <div class="mb-3">
            <label for="special_requirements" class="form-label">
              Yêu cầu đặc biệt <span class="text-danger">*</span>
            </label>
            <textarea
              class="form-control"
              id="special_requirements"
              name="special_requirements"
              rows="6"
              placeholder="Ví dụ: Ăn chay, Bệnh lý (tiểu đường, cao huyết áp...), Dị ứng thức ăn, Yêu cầu đặc biệt khác..."
              required
            ><?= htmlspecialchars($currentSpecialReqs) ?></textarea>
            <small class="text-muted">
              Ghi rõ các yêu cầu đặc biệt của khách để chuẩn bị phục vụ phù hợp.
            </small>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= BASE_URL ?>?act=guide-schedule-detail&id=<?= (int)$booking['id'] ?>" class="btn btn-secondary">
              <i class="bi bi-x-circle"></i> Hủy
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle"></i> Lưu cập nhật
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

