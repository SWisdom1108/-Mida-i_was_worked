<link rel="stylesheet" type="text/css" href="/mida/module/bbs/<?=$pmType?>/css/common.css">
<script type="text/javascript" src="/mida/module/bbs/<?=$pmType?>/js/common.js"></script>
<?php
	
	$value = array(':bbs'=>$bbsCode);
	$query = "SELECT * FROM mc_bbs_code WHERE bbs_code = :bbs";
	$bbsInfo = view_pdo($query, $value);
	
	if(!$bbsInfo){
		echo "<script>location.href = '/';</script>";
		return false;
	}
	  
	include_once "function.php";
	 $bbsPath = ($pmType == "m") ? "/m/sub/bbs/bbs" : "/sub/bbs/bbs";

	$andQuery = "WHERE use_yn = 'Y' AND bbs_code = '{$bbsCode}'";
	# 공지사항 전용
	if($bbsCode == "001"){
		$andQuery .= $groupQuery;
	}
	if($incType == "L"){
		$searchLabel = ($_GET['label']) ? $_GET['label'] : "title"; # 검색 선택값
		$searchVal = $_GET['value']; # 검색 입력값
		
		if($searchLabel){
			switch($searchLabel){
				case "name":
					$andQuery .= " AND reg_idx IN ( SELECT idx FROM {$userTable} WHERE {$userNameColum} LIKE '%{$searchVal}%' )";
					break;
				default :
					$andQuery .= " AND {$searchLabel} LIKE '%{$searchVal}%'";
			}
		}
		
		bbsPaging("mt_bbs"); # 페이징
		$_SESSION['listURL'] = $_SERVER['REQUEST_URI']; # 이전주소 잡기
		bbsListSet(); # 게시판 보여지는 갯수 지정
	}
	 
	include_once "{$pmType}/{$userType}/{$bbsInfo['bbs_type']}/{$incType}2.php";
	include_once "{$pmType}/{$userType}/common/button/{$incType}.php"; # 공용 버튼
	
	if($incType == "V"){
		include_once "{$pmType}/{$userType}/common/navigation/nav.php"; # 공용 네비게이션
	}
	  
	if($incType == "L"){
		bbsPaging(); # 페이징
		bbsSearch(); # 검색
	}

?>