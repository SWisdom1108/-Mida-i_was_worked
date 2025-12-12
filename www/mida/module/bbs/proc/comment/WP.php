<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

	if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
		empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			header("location: /");
			return false;
	}

	post2val();
	$con = ehtml($con);

	if(!$con){
		echo "내용을 입력해주시길 바랍니다.";
		return false;
	}

	$value = array(':idx'=>$idx, ':con'=>$con, ':proc_id'=>$proc_id, ':proc_ip'=>$proc_ip);
	$query = "
		INSERT INTO mt_bbs_comment
			( bbs_idx, contents, reg_idx, reg_ip )
		VALUES
			( :idx, :con, :proc_id, :proc_ip )
	";

	$exec = execute_pdo($query, $value);
	if($exec['data']->rowCount() > 0){
		
		# 알림전송
		$value = array(':idx'=>$idx);
		$query = "SELECT * FROM mt_bbs WHERE idx = :idx";
		$view = view_pdo($query, $value);
		sendNotice("007", $view['reg_idx'], "작성한 게시글에 새로운 댓글이 등록되었습니다.", "/sub/bbs/bbs?bbs={$view['bbs_code']}&inc=V&idx={$idx}");
		
		echo "success";
	}  else {
		echo "fail";
	}

?>