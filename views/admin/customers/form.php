<?php
/** @var array $old */
/** @var string[] $errors */

$isEdit = !empty($old['id']);
?>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">
          <?= $isEdit ? 'Sửa khách hàng' : 'Tạo khách hàng mới' ?>
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
          action="<?= BASE_URL ?>?act=<?= $isEdit ? 'customer-update' : 'customer-store' ?>"
        >
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$old['id'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control"
              id="name"
              name="name"
              required
              value="<?= htmlspecialchars($old['name'] ?? '') ?>"
            >
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="phone"
                  name="phone"
                  required
                  value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  name="email"
                  value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                >
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <textarea
              class="form-control"
              id="address"
              name="address"
              rows="2"
            ><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="company" class="form-label">Công ty</label>
                <input
                  type="text"
                  class="form-control"
                  id="company"
                  name="company"
                  value="<?= htmlspecialchars($old['company'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="tax_code" class="form-label">Mã số thuế</label>
                <input
                  type="text"
                  class="form-control"
                  id="tax_code"
                  name="tax_code"
                  value="<?= htmlspecialchars($old['tax_code'] ?? '') ?>"
                >
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Ghi chú</label>
            <textarea
              class="form-control"
              id="notes"
              name="notes"
              rows="3"
            ><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-select" id="status" name="status">
              <option value="1" <?= (int)($old['status'] ?? 1) === 1 ? 'selected' : '' ?>>
                Hoạt động
              </option>
              <option value="0" <?= (int)($old['status'] ?? 1) === 0 ? 'selected' : '' ?>>
                Vô hiệu
              </option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?>
          </button>
          <a href="<?= BASE_URL ?>?act=customers" class="btn btn-secondary ms-2">
            Quay lại
          </a>
        </form>
      </div>
    </div>
  </div>
</div>

