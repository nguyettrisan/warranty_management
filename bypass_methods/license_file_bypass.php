<?php
/**
 * Phương pháp 1: Bypass qua file license giả mạo
 * 
 * Module kiểm tra file .lic trong thư mục libraries/
 * Nếu file tồn tại, nó sẽ đọc nội dung thay vì gọi API
 */

// Bước 1: Tạo file .lic giả mạo
$license_file_path = '/workspace/libraries/.lic';
$fake_license_content = base64_encode('{"status":true,"message":"Verified! Thanks for purchasing.","expires":"2030-12-31"}');

// Ghi file license giả mạo
file_put_contents($license_file_path, $fake_license_content);

echo "✓ Đã tạo file license giả mạo tại: " . $license_file_path . "\n";

// Bước 2: Tạo file .licint để bypass time-based check
$check_interval_file = '/workspace/libraries/.licint';
$future_date = base64_encode(date('d-m-Y', strtotime('+1 year')));
file_put_contents($check_interval_file, $future_date);

echo "✓ Đã tạo file interval check giả mạo\n";

// Bước 3: Xác minh bypass
if (file_exists($license_file_path)) {
    echo "✓ File license đã tồn tại - Module sẽ không gọi API\n";
} else {
    echo "✗ Không thể tạo file license\n";
}
?>