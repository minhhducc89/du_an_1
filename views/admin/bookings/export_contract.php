<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hợp đồng dịch vụ du lịch - Booking #<?= (int)$booking['id'] ?></title>
  <style>
    @media print {
      .no-print { display: none; }
      body { margin: 0; padding: 20px; }
      @page {
        margin: 2cm;
      }
    }
    body {
      font-family: 'Times New Roman', serif;
      font-size: 13px;
      line-height: 1.6;
      margin: 20px;
      color: #000;
      background: #fff;
    }
    .no-print {
      margin-bottom: 20px;
      text-align: center;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 5px;
    }
    .no-print button {
      padding: 12px 24px;
      font-size: 16px;
      background-color: #dc3545;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin: 0 10px;
    }
    .no-print button:hover {
      background-color: #c82333;
    }
    .no-print .btn-secondary {
      background-color: #6c757d;
    }
    .no-print .btn-secondary:hover {
      background-color: #5a6268;
    }
    .contract-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 30px;
      background: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 3px solid #000;
      padding-bottom: 15px;
    }
    .header h1 {
      margin: 0;
      font-size: 20px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .header .contract-number {
      margin-top: 10px;
      font-size: 14px;
      font-weight: normal;
    }
    .parties {
      margin-bottom: 25px;
    }
    .party {
      margin-bottom: 20px;
    }
    .party-title {
      font-weight: bold;
      font-size: 14px;
      margin-bottom: 10px;
      text-transform: uppercase;
    }
    .party-info {
      margin-left: 20px;
      line-height: 1.8;
    }
    .section {
      margin-bottom: 25px;
    }
    .section-title {
      font-weight: bold;
      font-size: 14px;
      margin-bottom: 10px;
      text-transform: uppercase;
      border-bottom: 1px solid #000;
      padding-bottom: 5px;
    }
    .section-content {
      margin-left: 20px;
      line-height: 1.8;
    }
    .section-content ul {
      margin: 10px 0;
      padding-left: 30px;
    }
    .section-content li {
      margin-bottom: 8px;
    }
    .signature-section {
      margin-top: 50px;
      display: flex;
      justify-content: space-between;
    }
    .signature-box {
      width: 45%;
      text-align: center;
    }
    .signature-box .title {
      font-weight: bold;
      margin-bottom: 60px;
      text-transform: uppercase;
    }
    .signature-line {
      border-top: 1px solid #000;
      margin-top: 60px;
      padding-top: 5px;
    }
    .footer-note {
      margin-top: 20px;
      font-style: italic;
      color: #666;
      font-size: 12px;
    }
    .highlight {
      font-weight: bold;
    }
    .amount {
      font-size: 15px;
      font-weight: bold;
      color: #d32f2f;
    }
  </style>
</head>
<body>
  <div class="no-print">
    <button onclick="window.print()">
      <i class="bi bi-printer"></i> In / Lưu PDF
    </button>
    <button onclick="window.close()" class="btn-secondary">
      Đóng
    </button>
  </div>

  <div class="contract-container">
    <div class="header">
      <h1>HỢP ĐỒNG DỊCH VỤ DU LỊCH</h1>
      <div class="contract-number">
        Số: HD-<?= date('Y', strtotime($booking['created_at'])) ?>-<?= str_pad($booking['id'], 3, '0', STR_PAD_LEFT) ?>
      </div>
      <div style="margin-top: 10px; font-size: 13px;">
        Ngày: <?= date('d/m/Y', strtotime($booking['created_at'])) ?>
      </div>
    </div>

    <div class="parties">
      <div class="party">
        <div class="party-title">BÊN A (BÊN CUNG CẤP DỊCH VỤ):</div>
        <div class="party-info">
          <strong>CÔNG TY TNHH DU LỊCH ABC</strong><br>
          Địa chỉ: 123 Đường ABC, Quận XYZ, Hà Nội<br>
          Điện thoại: 0243.123.456<br>
          Email: info@dulichabc.com
        </div>
      </div>

      <div class="party">
        <div class="party-title">BÊN B (KHÁCH HÀNG):</div>
        <div class="party-info">
          <?php 
            $customer = $service['customer'] ?? [];
          ?>
          <strong>Họ tên:</strong> <?= htmlspecialchars($customer['name'] ?? 'N/A') ?><br>
          <strong>Số điện thoại:</strong> <?= htmlspecialchars($customer['phone'] ?? 'N/A') ?><br>
          <strong>Email:</strong> <?= htmlspecialchars($customer['email'] ?? 'N/A') ?><br>
          <strong>Địa chỉ:</strong> <?= htmlspecialchars($customer['address'] ?? 'N/A') ?>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-title">ĐIỀU 1: THÔNG TIN TOUR</div>
      <div class="section-content">
        <ul>
          <li><strong>Tên tour:</strong> <?= htmlspecialchars($booking['tour_name'] ?? 'Không xác định') ?></li>
          <li><strong>Ngày khởi hành:</strong> <?= date('d/m/Y', strtotime($booking['start_date'])) ?></li>
          <?php if ($booking['end_date']): ?>
            <li><strong>Ngày kết thúc:</strong> <?= date('d/m/Y', strtotime($booking['end_date'])) ?></li>
          <?php endif; ?>
          <li><strong>Số lượng khách:</strong> 
            <?= (int)($service['adult'] ?? 0) ?> người lớn
            <?php if (($service['child'] ?? 0) > 0): ?>
              , <?= (int)($service['child']) ?> trẻ em
            <?php endif; ?>
            (Tổng: <?= (int)($service['total_guests'] ?? 0) ?> người)
          </li>
          <li><strong>Loại booking:</strong> 
            <?= ($service['booking_type'] ?? 'individual') === 'group' ? 'Đoàn' : 'Khách lẻ' ?>
          </li>
        </ul>
      </div>
    </div>

    <div class="section">
      <div class="section-title">ĐIỀU 2: GIÁ CẢ VÀ THANH TOÁN</div>
      <div class="section-content">
        <ul>
          <?php if (isset($service['adult_price'])): ?>
            <li><strong>Giá người lớn:</strong> <span class="amount"><?= number_format($service['adult_price'], 0, ',', '.') ?> VNĐ/người</span></li>
          <?php endif; ?>
          <?php if (isset($service['child_price'])): ?>
            <li><strong>Giá trẻ em:</strong> <span class="amount"><?= number_format($service['child_price'], 0, ',', '.') ?> VNĐ/người</span></li>
          <?php endif; ?>
          <?php if (isset($service['total_amount'])): ?>
            <li><strong>Tổng giá trị hợp đồng:</strong> <span class="amount"><?= number_format($service['total_amount'], 0, ',', '.') ?> VNĐ</span></li>
          <?php endif; ?>
          <li><strong>Phương thức thanh toán:</strong> Chuyển khoản hoặc tiền mặt</li>
          <li><strong>Tình trạng thanh toán:</strong> 
            <?php
              $statusName = $booking['status_name'] ?? 'Chờ xác nhận';
              if ($booking['status'] == 2) {
                echo 'Đã xác nhận';
              } elseif ($booking['status'] == 3) {
                echo 'Đã hoàn thành';
              } else {
                echo 'Chờ xác nhận';
              }
            ?>
          </li>
        </ul>
      </div>
    </div>

    <?php if (!empty($service['special_requirements'])): ?>
    <div class="section">
      <div class="section-title">ĐIỀU 3: YÊU CẦU ĐẶC BIỆT</div>
      <div class="section-content">
        <p><?= nl2br(htmlspecialchars($service['special_requirements'] ?? '')) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <div class="section">
      <div class="section-title">NỘI DUNG HỢP ĐỒNG</div>
      <div class="section-content" style="white-space: pre-wrap; font-family: 'Times New Roman', serif;">
        <?= htmlspecialchars($booking['contract'] ?? '') ?>
      </div>
    </div>

    <div class="signature-section">
      <div class="signature-box">
        <div class="title">BÊN A</div>
        <div class="signature-line">
          (Ký và đóng dấu)
        </div>
      </div>
      <div class="signature-box">
        <div class="title">BÊN B</div>
        <div class="signature-line">
          (Ký tên)
        </div>
      </div>
    </div>

    <?php if ($booking['status'] == 1): ?>
    <div class="footer-note">
      <p><strong>Lưu ý:</strong> Hợp đồng này đang trong trạng thái chờ xác nhận. Sẽ có hiệu lực sau khi được Bên A xác nhận chính thức.</p>
    </div>
    <?php endif; ?>
  </div>

  <script>
    // Tự động mở hộp thoại in khi tải trang (tùy chọn)
    // window.onload = function() {
    //   setTimeout(function() {
    //     window.print();
    //   }, 500);
    // };
  </script>
</body>
</html>

