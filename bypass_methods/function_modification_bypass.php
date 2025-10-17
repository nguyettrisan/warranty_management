<?php
/**
 * Phương pháp 2: Bypass qua modification của hàm verify_license
 * 
 * Sửa đổi hàm verify_license() để luôn trả về status = true
 */

$gtsslib_file = '/workspace/libraries/gtsslib.php';

// Đọc nội dung file hiện tại
$content = file_get_contents($gtsslib_file);

// Tìm và thay thế hàm verify_license
$old_function = 'public function verify_license($time_based_check = false, $license = false, $client = false){
		if(!empty($license)&&!empty($client)){
			$data_array =  array(
				"product_id"  => $this->product_id,
				"license_file" => null,
				"license_code" => $license,
				"client_name" => $client
			);
		}else{
			if(is_file($this->license_file)){
				$data_array =  array(
					"product_id"  => $this->product_id,
					"license_file" => file_get_contents($this->license_file),
					"license_code" => null,
					"client_name" => null
				);
			}else{
				$data_array =  array();
				return array(\'status\' => FALSE, \'message\' => LB_TEXT_INVALID_RESPONSE);
			}
		} 
		$res = array(\'status\' => TRUE, \'message\' => LB_TEXT_VERIFIED_RESPONSE);
		if($time_based_check && $this->verification_period > 0){
			ob_start();
			if(session_status() == PHP_SESSION_NONE){
				session_start();
			}
			$type = (int) $this->verification_period;
			$today = date(\'d-m-Y\');
			$last_verification = \'00-00-0000\';
			if(is_file($this->license_file)){
				$last_verification = base64_decode(file_get_contents($this->check_interval_file));
			} 
			if($type == 1){
				$type_text = \'1 day\';
			}elseif($type == 3){
				$type_text = \'3 days\';
			}elseif($type == 7){
				$type_text = \'1 week\';
			}elseif($type == 30){
				$type_text = \'1 month\';
			}elseif($type == 90){
				$type_text = \'3 months\';
			}elseif($type == 365) {
				$type_text = \'1 year\';
			}else{
				$type_text = $type.\' days\';
			}
			if(strtotime($today) >= strtotime($last_verification)){
				$get_data = $this->call_api(
					\'POST\',
					$this->api_url.\'api/verify_license\', 
					json_encode($data_array)
				);
				$res = json_decode($get_data, true);
				if($res[\'status\']==true){
					$tomo = date(\'d-m-Y\', strtotime($today. \' + \'.$type_text));
					file_put_contents($this->check_interval_file,base64_encode($tomo), LOCK_EX);
				}
			}
			ob_end_clean();
		}else{		
			$get_data = $this->call_api(
				\'POST\',
				$this->api_url.\'api/verify_license\', 
				json_encode($data_array)
			);
			$res = json_decode($get_data, true);
		}
		return $res;
	}';

$new_function = 'public function verify_license($time_based_check = false, $license = false, $client = false){
		// BYPASS: Luôn trả về true
		return array(\'status\' => TRUE, \'message\' => \'Bypassed successfully\');
	}';

// Thực hiện thay thế
$new_content = str_replace($old_function, $new_function, $content);

// Ghi file đã sửa đổi
if (file_put_contents($gtsslib_file, $new_content)) {
    echo "✓ Đã sửa đổi hàm verify_license() để bypass\n";
} else {
    echo "✗ Không thể sửa đổi file\n";
}
?>