<?php
/** @var array $old */
/** @var string[] $errors */
/** @var Category[] $categories */

$isEdit = !empty($old['id']);
?>

<div class="row">
  <div class="col-md-10">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">
          <?= $isEdit ? 'Chỉnh sửa tour' : 'Thêm tour mới' ?>
        </h3>
      </div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form
          method="post"
          enctype="multipart/form-data"
          action="<?= BASE_URL ?>?act=<?= $isEdit ? 'tour-update' : 'tour-store' ?>"
        >
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$old['id'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label for="name" class="form-label">Tên tour</label>
            <input
              type="text"
              class="form-control"
              id="name"
              name="name"
              required
              value="<?= htmlspecialchars($old['name'] ?? '') ?>"
            >
          </div>

          <div class="mb-3">
            <label for="category_id" class="form-label">Danh mục</label>
            <select
              class="form-select"
              id="category_id"
              name="category_id"
              required
            >
              <option value="">-- Chọn danh mục --</option>
              <?php foreach ($categories as $cat): ?>
                <option
                  value="<?= (int)$cat->id ?>"
                  <?= (int)($old['category_id'] ?? 0) === (int)$cat->id ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($cat->name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea
              class="form-control"
              id="description"
              name="description"
              rows="4"
            ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="price" class="form-label">Giá người lớn (VNĐ)</label>
                <input
                  type="number"
                  step="1000"
                  min="0"
                  class="form-control"
                  id="price"
                  name="price"
                  value="<?= htmlspecialchars((string)($old['price'] ?? '')) ?>"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="child_price" class="form-label">Giá trẻ em (VNĐ)</label>
                <input
                  type="number"
                  step="1000"
                  min="0"
                  class="form-control"
                  id="child_price"
                  name="child_price"
                  value="<?= htmlspecialchars((string)($old['child_price'] ?? '')) ?>"
                >
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="duration" class="form-label">Số ngày/đêm</label>
                <input
                  type="text"
                  class="form-control"
                  id="duration"
                  name="duration"
                  placeholder="Ví dụ: 3N2Đ"
                  value="<?= htmlspecialchars((string)($old['duration'] ?? '')) ?>"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="max_guests" class="form-label">Số khách tối đa</label>
                <input
                  type="number"
                  min="1"
                  class="form-control"
                  id="max_guests"
                  name="max_guests"
                  value="<?= htmlspecialchars((string)($old['max_guests'] ?? '')) ?>"
                >
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="schedule" class="form-label">Lịch trình chi tiết</label>
            <textarea
              class="form-control"
              id="schedule"
              name="schedule"
              rows="4"
              placeholder="Ví dụ: Ngày 1: ...&#10;Ngày 2: ..."
            ><?= htmlspecialchars($old['schedule'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="images" class="form-label">Hình ảnh Tour</label>
            <input
              type="file"
              class="form-control"
              id="images"
              name="images[]"
              multiple
              accept="image/*"
            >
            <?php if (!empty($old['images_list']) && is_array($old['images_list'])): ?>
              <div class="form-text mt-2">
                Hình ảnh hiện tại:
                <ul class="mb-0">
                  <?php foreach ($old['images_list'] as $img): ?>
                    <li><?= htmlspecialchars($img) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="policies" class="form-label">Chính sách</label>
            <textarea
              class="form-control"
              id="policies"
              name="policies"
              rows="3"
              placeholder="Ví dụ: Không hoàn tiền khi hủy trong vòng 48h"
            ><?= htmlspecialchars($old['policies'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="suppliers" class="form-label">Nhà cung cấp</label>
            <textarea
              class="form-control"
              id="suppliers"
              name="suppliers"
              rows="3"
              placeholder="Ví dụ: Vinpearl Hotel, Xe Hùng Mạnh"
            ><?= htmlspecialchars($old['suppliers'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-select" id="status" name="status">
              <option value="1" <?= (int)($old['status'] ?? 1) === 1 ? 'selected' : '' ?>>
                Đang bán
              </option>
              <option value="0" <?= (int)($old['status'] ?? 1) === 0 ? 'selected' : '' ?>>
                Ngừng bán
              </option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Cập nhật' : 'Thêm mới' ?>
          </button>
          <a href="<?= BASE_URL ?>?act=tours" class="btn btn-secondary ms-2">
            Quay lại
          </a>
        </form>
      </div>
    </div>
  </div>
</div>


