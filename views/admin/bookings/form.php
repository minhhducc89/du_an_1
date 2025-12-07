<?php
/** @var array $old */
/** @var string[] $errors */
/** @var Tour[] $tours */
/** @var Customer[] $customers */
$isEdit = isset($_GET['id']) && (int)$_GET['id'] > 0;
$bookingId = $isEdit ? (int)$_GET['id'] : 0;
?>

<div class="row">
  <div class="col-md-10">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0"><?= $isEdit ? 'Sửa booking #' . $bookingId : 'Tạo booking mới' ?></h3>
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
          action="<?= BASE_URL ?>?act=<?= $isEdit ? 'booking-update&id=' . $bookingId : 'booking-store' ?>"
        >
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $bookingId ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label for="tour_id" class="form-label">Tour</label>
            <select
              class="form-select"
              id="tour_id"
              name="tour_id"
              required
            >
              <option value="">-- Chọn tour --</option>
              <?php foreach ($tours as $tour): ?>
                <?php
                  $adultPrice = $tour->price;
                  $childPrice = null;
                  if ($tour->prices) {
                      $p = json_decode($tour->prices, true);
                      if (is_array($p)) {
                          if (isset($p['adult'])) $adultPrice = $p['adult'];
                          if (isset($p['child'])) $childPrice = $p['child'];
                      }
                  }
                ?>
                <option
                  value="<?= (int)$tour->id ?>"
                  <?= (int)($old['tour_id'] ?? 0) === (int)$tour->id ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($tour->name) ?>
                  (NL: <?= $adultPrice !== null ? number_format($adultPrice, 0, ',', '.') . 'đ' : '-' ?>
                  - TE: <?= $childPrice !== null ? number_format($childPrice, 0, ',', '.') . 'đ' : '-' ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <h5 class="mt-4 mb-2">Thông tin khách hàng</h5>
          
          <div class="mb-3">
            <label for="customer_id" class="form-label">Chọn khách hàng (hoặc nhập thủ công)</label>
            <select
              class="form-select"
              id="customer_id"
              name="customer_id"
            >
              <option value="">-- Chọn khách hàng từ danh sách --</option>
              <?php foreach ($customers as $customer): ?>
                <option
                  value="<?= (int)$customer->id ?>"
                  data-name="<?= htmlspecialchars($customer->name) ?>"
                  data-phone="<?= htmlspecialchars($customer->phone) ?>"
                  data-email="<?= htmlspecialchars($customer->email ?? '') ?>"
                  data-address="<?= htmlspecialchars($customer->address ?? '') ?>"
                  <?= (int)($old['customer_id'] ?? 0) === (int)$customer->id ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($customer->name) ?> - <?= htmlspecialchars($customer->phone) ?>
                  <?php if ($customer->email): ?>
                    (<?= htmlspecialchars($customer->email) ?>)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Nếu chọn khách hàng, thông tin sẽ tự động điền. Hoặc bạn có thể nhập thủ công bên dưới.</small>
          </div>

          <div class="mb-3">
            <a href="<?= BASE_URL ?>?act=customer-create" target="_blank" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-plus-circle"></i> Tạo khách hàng mới
            </a>
          </div>

          <hr class="my-3">

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="customer_name" class="form-label">Họ tên người đại diện <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="customer_name"
                  name="customer_name"
                  required
                  value="<?= htmlspecialchars($old['customer_name'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="customer_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="customer_phone"
                  name="customer_phone"
                  required
                  value="<?= htmlspecialchars($old['customer_phone'] ?? '') ?>"
                >
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="customer_email" class="form-label">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="customer_email"
                  name="customer_email"
                  value="<?= htmlspecialchars($old['customer_email'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="customer_address" class="form-label">Địa chỉ</label>
                <input
                  type="text"
                  class="form-control"
                  id="customer_address"
                  name="customer_address"
                  value="<?= htmlspecialchars($old['customer_address'] ?? '') ?>"
                >
              </div>
            </div>
          </div>

          <h5 class="mt-4 mb-2">Loại booking & Số lượng</h5>
          
          <div class="mb-3">
            <label for="booking_type" class="form-label">Loại booking <span class="text-danger">*</span></label>
            <select
              class="form-select"
              id="booking_type"
              name="booking_type"
              required
            >
              <option value="">-- Chọn loại booking --</option>
              <option value="individual" <?= ($old['booking_type'] ?? '') === 'individual' ? 'selected' : '' ?>>
                Khách lẻ (1-2 người)
              </option>
              <option value="group" <?= ($old['booking_type'] ?? '') === 'group' ? 'selected' : '' ?>>
                Đoàn (3+ người, công ty, tổ chức)
              </option>
            </select>
            <small class="text-muted">Chọn loại booking để phục vụ tốt hơn</small>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="adult_qty" class="form-label">Số người lớn</label>
                <input
                  type="number"
                  min="0"
                  class="form-control"
                  id="adult_qty"
                  name="adult_qty"
                  value="<?= htmlspecialchars((string)($old['adult_qty'] ?? 1)) ?>"
                >
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="child_qty" class="form-label">Số trẻ em</label>
                <input
                  type="number"
                  min="0"
                  class="form-control"
                  id="child_qty"
                  name="child_qty"
                  value="<?= htmlspecialchars((string)($old['child_qty'] ?? 0)) ?>"
                >
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="start_date" class="form-label">Ngày khởi hành <span class="text-danger">*</span></label>
                <input
                  type="date"
                  class="form-control"
                  id="start_date"
                  name="start_date"
                  required
                  value="<?= htmlspecialchars($old['start_date'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="end_date" class="form-label">Ngày kết thúc</label>
                <input
                  type="date"
                  class="form-control"
                  id="end_date"
                  name="end_date"
                  value="<?= htmlspecialchars($old['end_date'] ?? '') ?>"
                >
                <small class="text-muted">Để trống nếu tour chỉ có 1 ngày</small>
              </div>
            </div>
          </div>

          <h5 class="mt-4 mb-2">Yêu cầu đặc biệt</h5>
          
          <div class="mb-3">
            <label for="special_requirements" class="form-label">Yêu cầu đặc biệt của khách</label>
            <textarea
              class="form-control"
              id="special_requirements"
              name="special_requirements"
              rows="4"
              placeholder="Ví dụ: Ăn chay, Bệnh lý (tiểu đường, cao huyết áp...)"
            ><?= htmlspecialchars($old['special_requirements'] ?? '') ?></textarea>
            <small class="text-muted">
              Ghi nhận các yêu cầu đặc biệt của khách (ăn chay, bệnh lý, v.v.) để HDV chuẩn bị phục vụ phù hợp.
            </small>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Ghi chú khác</label>
            <textarea
              class="form-control"
              id="notes"
              name="notes"
              rows="3"
            ><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
          </div>

          <h5 class="mt-4 mb-2">Hợp đồng</h5>
          
          <div class="mb-3">
            <label for="contract" class="form-label">Nội dung hợp đồng</label>
            <div class="d-flex gap-2 mb-2">
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadContractTemplate()">
                <i class="bi bi-file-earmark-text me-1"></i> Sử dụng mẫu hợp đồng
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearContract()">
                <i class="bi bi-x-circle me-1"></i> Xóa nội dung
              </button>
            </div>
            <textarea
              class="form-control font-monospace"
              id="contract"
              name="contract"
              rows="20"
              placeholder="Nhấn 'Sử dụng mẫu hợp đồng' để tự động điền template mẫu..."
              style="font-size: 12px;"
            ><?= htmlspecialchars($old['contract'] ?? '') ?></textarea>
            <small class="text-muted">
              <i class="bi bi-info-circle"></i> Sử dụng mẫu hợp đồng sẽ tự động điền thông tin từ booking vào template chuẩn.
            </small>
          </div>

          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Cập nhật booking' : 'Lưu booking' ?>
          </button>
          <?php if ($isEdit): ?>
            <a href="<?= BASE_URL ?>?act=booking-show&id=<?= $bookingId ?>" class="btn btn-secondary ms-2">
              Quay lại
            </a>
          <?php else: ?>
            <a href="<?= BASE_URL ?>?act=bookings" class="btn btn-secondary ms-2">
              Quay lại
            </a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const customerSelect = document.getElementById('customer_id');
  const customerNameInput = document.getElementById('customer_name');
  const customerPhoneInput = document.getElementById('customer_phone');
  const customerEmailInput = document.getElementById('customer_email');
  const customerAddressInput = document.getElementById('customer_address');

  if (customerSelect) {
    customerSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      
      if (selectedOption.value !== '') {
        // Lấy thông tin từ data attributes
        const name = selectedOption.getAttribute('data-name') || '';
        const phone = selectedOption.getAttribute('data-phone') || '';
        const email = selectedOption.getAttribute('data-email') || '';
        const address = selectedOption.getAttribute('data-address') || '';

        // Điền thông tin vào các input
        if (customerNameInput) customerNameInput.value = name;
        if (customerPhoneInput) customerPhoneInput.value = phone;
        if (customerEmailInput) customerEmailInput.value = email;
        if (customerAddressInput) customerAddressInput.value = address;
      } else {
        // Xóa thông tin nếu chọn "-- Chọn khách hàng --"
        if (customerNameInput) customerNameInput.value = '';
        if (customerPhoneInput) customerPhoneInput.value = '';
        if (customerEmailInput) customerEmailInput.value = '';
        if (customerAddressInput) customerAddressInput.value = '';
      }
    });
  }
});

// Hàm tải template hợp đồng mẫu
function loadContractTemplate() {
  const contractTextarea = document.getElementById('contract');
  if (!contractTextarea) return;

  // Lấy thông tin từ form
  const tourSelect = document.getElementById('tour_id');
  const tourName = tourSelect ? tourSelect.options[tourSelect.selectedIndex]?.text?.split('(')[0]?.trim() || 'Tour' : 'Tour';
  
  const customerName = document.getElementById('customer_name')?.value || '[Tên khách hàng]';
  const customerPhone = document.getElementById('customer_phone')?.value || '[Số điện thoại]';
  const customerEmail = document.getElementById('customer_email')?.value || '[Email]';
  const customerAddress = document.getElementById('customer_address')?.value || '[Địa chỉ]';
  
  const startDate = document.getElementById('start_date')?.value || '';
  const endDate = document.getElementById('end_date')?.value || '';
  const adultQty = parseInt(document.getElementById('adult_qty')?.value || 0);
  const childQty = parseInt(document.getElementById('child_qty')?.value || 0);
  const bookingType = document.getElementById('booking_type')?.value || 'individual';
  const specialRequirements = document.getElementById('special_requirements')?.value || '';
  
  // Format ngày
  const formatDate = (dateStr) => {
    if (!dateStr) return '[Ngày]';
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN');
  };
  
  const startDateFormatted = formatDate(startDate);
  const endDateFormatted = endDate ? formatDate(endDate) : '[Ngày kết thúc]';
  const today = new Date().toLocaleDateString('vi-VN');
  const currentYear = new Date().getFullYear();
  
  // Xác định loại booking
  const bookingTypeText = bookingType === 'group' ? 'Đoàn' : 'Khách lẻ';
  const totalGuests = adultQty + childQty;
  
  // Tạo số hợp đồng (tạm thời dùng năm hiện tại)
  const contractNumber = `HD-${currentYear}-XXX`;
  
  // Template hợp đồng
  const template = `HỢP ĐỒNG DỊCH VỤ DU LỊCH
Số: ${contractNumber}
Ngày: ${today}

BÊN A (BÊN CUNG CẤP DỊCH VỤ):
CÔNG TY TNHH DU LỊCH ABC
Địa chỉ: 123 Đường ABC, Quận XYZ, Hà Nội
Điện thoại: 0243.123.456
Email: info@dulichabc.com

BÊN B (KHÁCH HÀNG):
Họ tên: ${customerName}
Số điện thoại: ${customerPhone}
Email: ${customerEmail}
Địa chỉ: ${customerAddress}

ĐIỀU 1: THÔNG TIN TOUR
- Tên tour: ${tourName}
- Ngày khởi hành: ${startDateFormatted}
- Ngày kết thúc: ${endDateFormatted}
- Số lượng khách: ${adultQty > 0 ? adultQty + ' người lớn' : ''}${adultQty > 0 && childQty > 0 ? ', ' : ''}${childQty > 0 ? childQty + ' trẻ em' : ''} (Tổng: ${totalGuests} người)
- Loại booking: ${bookingTypeText}

ĐIỀU 2: GIÁ CẢ VÀ THANH TOÁN
- Giá người lớn: [Giá người lớn] VNĐ/người
- Giá trẻ em: [Giá trẻ em] VNĐ/người
- Tổng giá trị hợp đồng: [Tổng tiền] VNĐ
- Phương thức thanh toán: Chuyển khoản hoặc tiền mặt
- Tình trạng thanh toán: [Đã thanh toán / Chờ thanh toán]

ĐIỀU 3: DỊCH VỤ BAO GỒM
- Vé tham quan
- Ăn uống theo chương trình
- Khách sạn
- Xe đưa đón
- Hướng dẫn viên tiếng Việt
- Bảo hiểm du lịch

${specialRequirements ? `ĐIỀU 4: YÊU CẦU ĐẶC BIỆT
${specialRequirements}

ĐIỀU 5: ĐIỀU KHOẢN HỦY TOUR` : `ĐIỀU 4: ĐIỀU KHOẢN HỦY TOUR`}
- Hủy trước 7 ngày: Hoàn 50% giá trị tour
- Hủy sau 7 ngày: Không hoàn tiền

${specialRequirements ? `ĐIỀU 6: CAM KẾT` : `ĐIỀU 5: CAM KẾT`}
Bên A cam kết:
- Cung cấp đầy đủ dịch vụ theo đúng chương trình tour đã thỏa thuận
- Đảm bảo an toàn và sức khỏe cho khách hàng
${specialRequirements ? '- Tôn trọng và đáp ứng các yêu cầu đặc biệt về sức khỏe' : ''}

Bên B cam kết:
- Thanh toán đúng hạn và tuân thủ quy định của tour
${specialRequirements ? '- Cung cấp thông tin chính xác về tình trạng sức khỏe' : ''}
- Tuân thủ hướng dẫn của hướng dẫn viên trong suốt chuyến đi

Hà Nội, ngày ${today.split('/')[0]} tháng ${today.split('/')[1]} năm ${currentYear}

BÊN A                                    BÊN B
(Ký và đóng dấu)                        (Ký tên)
_________________                        _________________`;

  // Điền vào textarea
  contractTextarea.value = template;
  
  // Scroll đến textarea
  contractTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
  contractTextarea.focus();
  
  // Thông báo
  alert('Đã tải mẫu hợp đồng! Vui lòng kiểm tra và điều chỉnh các thông tin trong dấu ngoặc vuông [].');
}

// Hàm xóa nội dung hợp đồng
function clearContract() {
  const contractTextarea = document.getElementById('contract');
  if (contractTextarea && confirm('Bạn có chắc muốn xóa toàn bộ nội dung hợp đồng?')) {
    contractTextarea.value = '';
    contractTextarea.focus();
  }
}
</script>


