<?php
/**
 * Phương pháp 4: Bypass qua network isolation
 * 
 * Chặn kết nối đến server license để module fallback về local check
 */

// Bước 1: Tạo file hosts để chặn domain license
$hosts_file = '/etc/hosts';
$license_domains = [
    'greentech-solutions.com',
    'api.greentech-solutions.com',
    'license.greentech-solutions.com'
];

echo "Thêm các dòng sau vào file /etc/hosts:\n";
foreach ($license_domains as $domain) {
    echo "127.0.0.1 $domain\n";
}

// Bước 2: Tạo file .lic local
$license_file = '/workspace/libraries/.lic';
$local_license = base64_encode('{"status":true,"message":"Local verification","expires":"2030-12-31"}');
file_put_contents($license_file, $local_license);

echo "✓ Đã tạo file license local\n";

// Bước 3: Tạo file .licint
$interval_file = '/workspace/libraries/.licint';
$future_date = base64_encode(date('d-m-Y', strtotime('+1 year')));
file_put_contents($interval_file, $future_date);

echo "✓ Đã tạo file interval check\n";

// Bước 4: Sửa đổi hàm call_api để return local response
$gtsslib_file = '/workspace/libraries/gtsslib.php';
$content = file_get_contents($gtsslib_file);

// Tìm và sửa đổi hàm call_api
$old_call_api_start = 'private function call_api($method, $url, $data = null){
		$curl = curl_init();';

$new_call_api_start = 'private function call_api($method, $url, $data = null){
		// BYPASS: Return local response thay vì gọi API
		return json_encode(array(\'status\' => true, \'message\' => \'Local verification bypassed\'));
		$curl = curl_init();';

$content = str_replace($old_call_api_start, $new_call_api_start, $content);

if (file_put_contents($gtsslib_file, $content)) {
    echo "✓ Đã sửa đổi hàm call_api để bypass network\n";
} else {
    echo "✗ Không thể sửa đổi file\n";
}
?>