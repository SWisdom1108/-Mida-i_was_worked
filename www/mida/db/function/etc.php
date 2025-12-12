<?php

	// alert / location.href function
	function msg($title="", $msg="", $url=""){
		echo "<script>";
		if($title && $msg && $url){
			echo "msg('{$title}', '{$msg}', false, function(){
				window.location.href = '{$url}'
			})";
		} else if($msg){
			echo "msg('{$title}', '{$msg}')";
		} else if($url){
			echo "window.location.href = '{$url}'";
		}
		echo "</script>";
	}

	function golink($msg="",$url=""){
		echo "<script>";
		echo ($msg) ? "alert('".$msg."'); " : "";
		echo ($url) ? "location.href = '".$url."'; " : "history.back();";
		echo "</script>";
	}

	// page move
	function www($url=""){
		header("location: {$url}");
	}

	// file size get
	function convertFileSize($size=""){
		$base = log($size) / log(1024);
		$suffix = array("byte", "KB", "MB", "GB", "TB");
		$f_base = floor($base);
		return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
	}

	// getClean
	function getClean($data="", $target="", $type=""){
		// type 1 = 처음시작, 그 외는 아님

		$target = preg_replace("/\s+/", "", $target);
		$target = explode(",", $target);
		$result = "";
		
		$data = explode("&", $data);
		for($i = 0; $i < count($data); $i++){
			if(!in_array(explode("=", $data[$i])[0], $target)){
				if($type == 1){
					if(!$result){
						$result .= "?{$data[$i]}";
					} else {
						$result .= "&{$data[$i]}";
					}
				} else {
					$result .= "&{$data[$i]}";
				}
			}
		}
		
		return $result;
	}

	// 원본코드
	// htmlspecialchars_encode
	// function ehtml($data){
	// 	return addslashes(htmlspecialchars($data));
	// }

	// // htmlspecialchars_decode
	function dhtml2($data=""){
		return htmlspecialchars_decode(nl2br($data));
	}
	// 원본코드

	function ehtml($text=""){
		return addslashes($text);
	}

	// function dhtml($text){
	// 	return stripslashes(nl2br($text));
	// }

	function dhtmlBf($text=""){
		return stripslashes(preg_replace('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', '', nl2br($text)));
	}

	function dhtmlInp($text=""){
		return htmlspecialchars($text);
	}
	
	function dhtml_execl($text=""){
		return strip_tags(htmlspecialchars_decode($text));
	}

	function dhtml_script($text=""){
		return strip_tags(preg_replace('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', '', $text));
	}

	function dhtml($data=""){
		$data = htmlspecialchars(htmlspecialchars_decode(nl2br($data)));
		return preg_replace("/[\\\]/i", "", $data);
	 }


	 // function dhtml($data){
		// return html_entity_decode(stripslashes($data));
	 // }
	# 200310 POST값 변수화
	function post2val(){
		foreach($_POST as $key => $val){
			global ${"{$key}"};
			${"{$key}"} = $val;
		}
	}

	# 200408 폴더생성
	function makeDir($info=""){
		system("mkdir -p {$_SERVER['DOCUMENT_ROOT']}{$info}");
		system("chmod -R 777 {$_SERVER['DOCUMENT_ROOT']}{$info}");
	}

	# 200421 알림전송
	function sendNotice($type="", $idx="", $msg="", $url=""){
		global $proc_id, $proc_ip;

		$andColum = "";
		$andValues = "";

		if($url){
			$andColum .= ", url";
			$andValues .= ", '{$url}'";
		}

		excute("
			INSERT INTO mt_notification
				( type_code, m_idx, content, reg_idx, reg_ip {$andColum} )
			VALUES
				( '{$type}', '{$idx}', '{$msg}', '{$proc_id}', '{$proc_ip}' {$andValues} )
		");
	}

	# 200924 SMS
	function smsSend($receiveTel="", $contents="", $sendTel="", $sendDate=""){
		# 데이터 정리
		$data = [];
		$data['receive_tel'] = $receiveTel; # 받을 연락처
		$data['send_tel'] = ($sendTel) ? $sendTel : view_sql("SELECT sent_tel FROM mt_sms_tel WHERE use_yn = 'Y' AND main_yn = 'Y'")["sent_tel"]; # 보낼 연락처
		$data['sms'] = $contents; # 받을 SMS내용
		$data['send_date'] = $sendDate; # 전송일시

		# 연결
		if($data['send_tel']){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_URL, "https://api.mdworks.kr/sms/oneshot");
			$res = curl_exec($ch);
			$res = json_decode($res, true);
			$res["send_name"] = view_sql("SELECT sent_name FROM mt_sms_tel WHERE use_yn = 'Y' AND sent_tel = '{$data["send_tel"]}'")["sent_name"];
			$res["send_tel"] = $data['send_tel'];
			
			return $res;
		} else {
			$res = [];
			$res["msg"] = "fail";
			
			return $res;
		}
	}
// DB 컬럼정리
function createFrm($type="", $placeHolder="", $infoIdx="", $name="", $value=""){
	switch($type){
		case "text":
			return "<input type=\"text\" class=\"txtBox\" value=\"{$value}\" name=\"{$name}\" placeHolder=\"{$placeHolder}\">";
			break;
		case "textarea":
			return "<textarea class=\"txtBox\" name=\"{$name}\" placeHolder=\"{$placeHolder}\">{$value}</textarea>";
			break;
		case "select":
			$placeHolder = "항목선택";
			$options = "";

			$sql = "
				SELECT *
				FROM mt_db_cs_info_detail
				WHERE info_idx = '{$infoIdx}'
				AND use_yn = 'Y'
				ORDER BY sort ASC
			";
			$result = list_sql($sql);
			foreach ( $result as $row ){
				$info_val = dhtml($row['info_val']);
				$selected = ($value == $info_val) ? "selected" : "";
				$options .= "
				<option value=\"{$info_val}\" {$selected}>{$info_val}</option>";
			}
			$html = "
				<select class=\"txtBox\" name=\"{$name}\">
					<option value=\"\">{$placeHolder}</option>
					{$options}
				</select>
			";
			return $html;
			break;
		case "radio":
			$placeHolder = "항목선택";
			$html = "";

			$sql = "
				SELECT *
				FROM mt_db_cs_info_detail
				WHERE info_idx = '{$infoIdx}'
				AND use_yn = 'Y'
				ORDER BY sort ASC
			";
			$result = list_sql($sql);
			$i = 0;
			foreach ( $result as $row ){
				$info_val = dhtml($row['info_val']);
				$i++;
				// if($value == $info_val){
				// 	$checked = "checked";
				// }else {
				// 	$checked = "";
				// }
				$checked = ($value == $info_val ) ? "checked" : "";

				$html .= "
					<input type=\"radio\" name=\"{$name}\" value=\"{$info_val}\" class=\"ainfo_{$i}\" id=\"radio{$infoIdx}_{$i}\" {$checked}>
					<label class=\"radioBox\" for=\"radio{$infoIdx}_{$i}\">
						<i class=\"fas fa-check-circle on\"></i>
						<i class=\"far fa-circle off\"></i>
					</label>
					<label for=\"radio{$infoIdx}_{$i}\">{$info_val}</label>
				";
			}
			return $html;
			break;
		case "checkbox":
			$checkedList = explode(",",$value);
			$details = [];
			$sql = "
				SELECT info_val
				FROM mt_db_cs_info_detail
				WHERE info_idx = '{$infoIdx}'
				AND use_yn = 'Y'
				ORDER BY sort ASC
			";
			$i=0;
			$result = list_sql($sql);
			foreach ( $result as $row ){
				$info_val = dhtml($row['info_val']);
				$i++;
				array_push($details, $info_val);
				$checked = ( in_array($info_val, $checkedList) ) ? "checked" : "";
				$html .= "
					<input type=\"checkbox\" name=\"{$name}[]\" value=\"{$info_val}\"  class=\"item_box\" id=\"check{$infoIdx}_{$i}\" {$checked}>
					<label class=\"checkBox\" for=\"check{$infoIdx}_{$i}\">
						<i class=\"fas fa-check-square on\"></i>
						<i class=\"far fa-square off\"></i>
					</label>
					<label for=\"check{$infoIdx}_{$i}\">{$info_val}</label>
				";
			}
			return $html;
			break;
		case "datepicker":
			return "<label for='date_input_{$name}' class='date_icon'><i class=\"fas fa-calendar-alt\"></i></label> <input type=\"text\" class='txtBox s_date date_input' id='date_input_{$name}' name='{$name}' value='{$value}' placeHolder='{$placeHolder}' dateonly>";
			break;
		case "number":
			return "<input type=\"text\" class=\"txtBox\" value=\"{$value}\" name=\"{$name}\" numonly placeHolder=\"{$placeHolder}\">";
			break;
		case "file":
			$html = "";
			$value = explode( '@#@#', $value );
			$fileName = ($value[1])? "<a href='/upload/db_etc/{$value[0]}' download='{$value[1]}'>{$value[1]} <i class=\"fas fa-download\"></a>" : "파일을 선택해주세요.";
			$placeHolder = ($placeHolder)? "[{$placeHolder}]" : "";
			$html .= "<input type=\"file\" name=\"{$name}\" id=\"{$name}\" class=\"excelFile_etc\">";
			$html .= "<label for=\"{$name}\" class=\"typeBtn btnGreen01\"><i class=\"fas fa-search\"></i>파일선택</label>";
			$html .= "<span id=\"excelFileName\"> {$fileName}</span>";
			return $html;
			break;	
		}
}

// 파일 확장자 확인
function chkExtension($fileName="") {
	$extList = []; $ext;		// 허용할 확장자 리스트, 파일의 확장자
	array_push($extList, 'mp3', 'm4a', 'wav', 'wma');		// 음성파일
	array_push($extList, 'avi', 'm4v', 'mkv', 'mov', 'mp4', 'webm', 'wmv');		// 영상파일
	array_push($extList, 'bmp', 'gif', 'jpeg', 'jpg', 'png', 'webp');		// 이미지파일
	array_push($extList, 'pptx', 'ppt', 'xls', 'xlsx', 'csv', 'doc', 'docx', 'pdf', 'txt', 'hwp', 'hwpx');		// 문서파일 파워포인트, 엑셀, 워드, pdf, txt, 한글
	array_push($extList, '7z', 'rar', 'zip');		// 압축파일
	
	$ext = array_pop(explode('.', $fileName));

	if(array_search($ext, $extList)) {
		return true;
	} else {
		return false;
	}
}

// 파일 업로드
function fileUpload($file="", $directoryName="") {
	$characters; $charactersLength;		// 난수에 포함 될 수 있는 문자 배열, $characters 배열의 길이
	$tmpFile; $filePath; $fileName; $originalFileName; $fileExt;		// 실제 파일, 파일 저장될 경로, 파일 이름, 원본 파일 이름, 파일 확장자
	$mkdirFlag = false; $oldumask;		// 디렉토리 생성 플래그, 바꾸기 이전의 권한설정값
	$return = [];		// 리턴할 값 배열
	
	if(!chkExtension($file['name'])) {
		$return = array('result'=>false, 'msg'=>'허용되지 않은 확장자', 'fileName'=>$file['name'], 'fileType'=>$file['type']);
		return $return;
	}

	// 파일명 생성 ( 0-9, a-z, A-Z 를 사용한 20자 난수 )
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$charactersLength = strlen($characters);

	do {
		$fileName = '';
		for ($i = 0; $i < 20; $i++) {
			$fileName .= $characters[rand(0, $charactersLength - 1)];
		}
	} while(file_exists($filePath.'/'.$fileName));

	$tmpFile = $file['tmp_name'];
	$filePath = $_SERVER['DOCUMENT_ROOT'].'upload/'.$directoryName;
	$originalFileName = $file['name'];
	$fileExt = array_pop(explode('.', $file['name']));
	$fileSize = $file['size'];

	// 디렉토리가 없을 시 생성
	if(file_exists($filePath)) {
		if(!is_dir($filePath)) {
			$mkdirFlag = true;
		} 
	} else {
		$mkdirFlag = true;
	}

	if($mkdirFlag) {
		$oldumask = umask(0);
		mkdir($filePath, 0777, true);
		umask($oldumask);
	}

	// 파일 업로드
	if(move_uploaded_file($tmpFile, $filePath.'/'.$fileName)) {
		$return = array('result'=>true, 'msg'=>'업로드 성공', 'originalFileName'=>$originalFileName, 'fileName'=>$fileName, 'fileExt'=>$fileExt, 'fileSize'=>$fileSize);
		return $return;
	} else {
		$return = array('result'=>false, 'msg'=>'업로드 실패', 'originalFileName'=>$originalFileName, 'fileName'=>$fileName, 'fileExt'=>$fileExt, 'fileSize'=>$fileSize);
		// $return = array('result'=>false, 'msg'=>'업로드 실패');
		return $return;
	}
}

// 파일 다운로드
function downloadFile($filePath="", $fileName="") {
	if(file_exists($filePath)){
		$fileSize = filesize($filePath);
		header("Pragma: public");
		header("Expires: 0");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".iconv("UTF-8","UHC",$fileName)."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: {$fileSize}");

		ob_clean();
		flush();

		if(readfile($filePath)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

// 이미지 파일 base64 암호화해서 불러오기
function getEncodedImage($filePath="") {
	if($filePath) {
		return base64_encode(fread(fopen($filePath, "r"), filesize($filePath)));
	} else {
		return false;
	}
}

	# 비밀번호 복잡도 체크 함수
	function passwordCheck($data=""){
		$pw = $data;
		$num = preg_match('/[0-9]/u', $pw);
		$eng = preg_match('/[a-z]/u', $pw);
		$eng2 = preg_match('/[A-Z]/u', $pw);
		$spe = preg_match("/(?=.*[\W_])/u", $pw);
	
		if(strlen($pw) < 10)
		{
		return "비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 10자리 이상 입력해 주세요.";
		exit;
		}
		if(preg_match("/\s/u", $pw) == true)
		{
		return "비밀번호는 공백없이 입력해주세요.";
		exit;
		}
		if( $num == 0 || $eng == 0 || $eng2 == 0 || $spe == 0)
		{
		return "비밀번호는 영문 대소문자, 숫자, 특수문자를 혼합하여 최소 10자리 이상 입력해 주세요.";
		exit;
		}
	
		return "success";
  }

?>