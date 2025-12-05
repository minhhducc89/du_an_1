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
          <?= $isEdit ? 'Chỉnh sửa danh mục tour' : 'Thêm danh mục tour' ?>
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
          action="<?= BASE_URL ?>?act=<?= $isEdit ? 'category-update' : 'category-store' ?>"
        >
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$old['id'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label for="name" class="form-label">Tên danh mục</label>
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
            <label for="description" class="form-label">Mô tả</label>
            <textarea
              class="form-control"
              id="description"
              name="description"
              rows="3"
            ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-select" id="status" name="status">
              <option value="1" <?= (int)($old['status'] ?? 1) === 1 ? 'selected' : '' ?>>
                Hoạt động
              </option>
              <option value="0" <?= (int)($old['status'] ?? 1) === 0 ? 'selected' : '' ?>>
                Ngừng sử dụng
              </option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Cập nhật' : 'Thêm mới' ?>
          </button>
          <a href="<?= BASE_URL ?>?act=categories" class="btn btn-secondary ms-2">
            Quay lại
          </a>
        </form>
      </div>
    </div>
  </div>
</div>


