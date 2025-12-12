<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 통계 일시 지정
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d", strtotime("- 7 days"));
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");

	# 데이터 정리
	$pmList = [];
	$value = array(':use_yn'=>'Y',':auth_code'=>'004',':idx'=>$_GET["code"]);
	$query = "
		SELECT MT.*
		FROM mt_member_team MT
		WHERE use_yn = :use_yn
		AND auth_code = :auth_code
		And idx = :idx
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		array_push($pmList, $row['idx']);
	}

	$totalData = [];
	$chartData = [];
	$newDate = date("Y-m-d", strtotime("+1 day", strtotime($endDate)));
	while(true){
		$newDate = date("Y-m-d", strtotime("-1 day", strtotime($newDate)));
		$thisDateData = [];
		
		foreach($pmList as $pmCode){
			$value = array(':tm_code1'=>$pmCode,':tm_code2'=>$pmCode,':use_yn1'=>'Y',':use_yn2'=>'N',':dist_code'=>'002',':dist_date'=>"{$newDate}%",':edit_date'=>"{$newDate}%");
			$query = "
				SELECT
					  ( SELECT COUNT(*) FROM mt_db WHERE tm_code = :tm_code1 AND use_yn = :use_yn1 AND dist_code = 002 AND dist_date LIKE :dist_date ) AS distCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE tm_code = :tm_code2 AND use_yn = :use_yn2 AND edit_date LIKE :edit_date ) AS deleteCnt
				FROM dual
				";
			$thisPmData = view_pdo($query, $value);
			
			$chartData[$newDate]["distCnt"] += $thisPmData["distCnt"];
			$chartData[$newDate]["deleteCnt"] += $thisPmData["deleteCnt"];
		}
		
		if($newDate == $startDate) break;
	}

?>

	<div class="listWrap">
		<table>
			<colgroup>
				<col width="12%">
				<col width="44%">
				<col width="44%">
			</colgroup>
			<thead>
				<tr>
					<th>일자</th>
					<th>분배DB</th>
					<th>삭제DB</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($chartData as $date => $data){ ?>
					<tr>
						<td class="lp05"><?=$date?></td>
						<td class="lp05"><?=number_format($data["distCnt"])?></td>
						<td class="lp05"><?=number_format($data["deleteCnt"])?></td>
					</tr>
				<?php  } ?>
			</tbody>
		</table>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="dayChart">닫기</button>
	</div>
	
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>