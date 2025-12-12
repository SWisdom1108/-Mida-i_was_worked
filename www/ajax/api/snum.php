<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();


	# 보안카드 API KEY발급
	function setAPIKey($length){
		if(!$length){
			return;
		}
		$char = '0123456789';
		$result = '';
		for ($i = 0; $i < $length; $i++) {
		    $rand = mt_rand(0, strlen($char) - 1);
		    $result .= $char[$rand];
		    $char = substr_replace($char, '', $rand, 1);
		}

		return $result;
	}

	function noOver() {
	    $overArray = [];
	    while (count($overArray) < 30) {
	        $newKey = setAPIKey(4);
	        if (!in_array($newKey, $overArray)) {
	            $overArray[] = $newKey;
	        }
	    }
	    return $overArray;
	}

	$snum_array = noOver();
	for ($i = 0; $i < 30; $i++) {
	    ${"s_num" . sprintf("%02d", $i + 1)} = $snum_array[$i];
	}

	$sql = "
	    INSERT INTO mt_member_snum
	        ( m_idx, s_num01, s_num02, s_num03, s_num04, s_num05, s_num06, s_num07, s_num08, s_num09, s_num10,
	          s_num11, s_num12, s_num13, s_num14, s_num15, s_num16, s_num17, s_num18, s_num19, s_num20,
	          s_num21, s_num22, s_num23, s_num24, s_num25, s_num26, s_num27, s_num28, s_num29, s_num30, reg_idx, reg_ip )
	    VALUES
	        ( '{$user['idx']}', '{$s_num01}', '{$s_num02}', '{$s_num03}', '{$s_num04}', '{$s_num05}', '{$s_num06}', '{$s_num07}', '{$s_num08}', '{$s_num09}', '{$s_num10}',
	          '{$s_num11}', '{$s_num12}', '{$s_num13}', '{$s_num14}', '{$s_num15}', '{$s_num16}', '{$s_num17}', '{$s_num18}', '{$s_num19}', '{$s_num20}',
	          '{$s_num21}', '{$s_num22}', '{$s_num23}', '{$s_num24}', '{$s_num25}', '{$s_num26}', '{$s_num27}', '{$s_num28}', '{$s_num29}', '{$s_num30}', '{$proc_id}', '{$proc_ip}' )
	";

	if(excute($sql) > 0){
		echo "success";
	}  else {
		echo "fail";
	}

?>