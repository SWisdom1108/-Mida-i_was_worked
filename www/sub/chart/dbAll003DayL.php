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
	$pmNameList = [];


	$value = array(':use_yn'=> 'Y' , ':auth_code'=> '003' , ':idx'=> $_GET["code"]);
	$query = "
		SELECT MT.*
		FROM mt_member_cmpy MT
		WHERE use_yn = :use_yn
		AND auth_code = :auth_code
		AND idx = :idx
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);
	while($row = $sql->fetch(PDO::FETCH_ASSOC)){
		array_push($pmList, $row['idx']);
		$pmNameList[$row['idx']] = $row["company_name"];
	}

	$totalData = [];
	$chartData = [];
	$newDate = date("Y-m-d", strtotime("+1 day", strtotime($endDate)));
	while(true){
		$newDate = date("Y-m-d", strtotime("-1 day", strtotime($newDate)));
		$thisDateData = [];
		
		foreach($pmList as $pmCode){

			$value = array(':pm_code'=> $pmCode , ':newDate' => "{$newDate}%");
			$query = "
					SELECT
					  ( SELECT COUNT(*) FROM mt_db WHERE pm_code = :pm_code AND reg_date LIKE :newDate ) AS uploadCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE pm_code = :pm_code AND use_yn = 'Y' AND dist_code = 002 AND dist_date LIKE :newDate ) AS distCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE pm_code = :pm_code AND use_yn = 'N' AND edit_date LIKE :newDate ) AS deleteCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE pm_code = :pm_code AND use_yn = 'Y' AND dist_code = 001 AND reg_date LIKE :newDate ) AS stockCnt
				FROM dual
			";
			$thisPmData = view_pdo($query, $value);

			// $thisPmData = view_sql("
			// 	SELECT
			// 		  ( SELECT COUNT(*) FROM mt_db_chart_log WHERE type_name = 'upload' AND code_type = 'pm' AND code_value = :pm_code AND reg_date LIKE :newDate ) AS uploadCnt
			// 		, ( SELECT COUNT(*) FROM mt_db_chart_log WHERE type_name = 'dist' AND code_type = 'pm' AND code_value = :pm_code AND reg_date LIKE :newDate ) AS distCnt
			// 		, ( SELECT COUNT(*) FROM mt_db_chart_log WHERE type_name = 'delete' AND code_type = 'pm' AND code_value = :pm_code AND reg_date LIKE :newDate ) AS deleteCnt
			// 		, ( SELECT db_cnt FROM mt_db_chart_log WHERE type_name = 'stock' AND code_type = 'pm' AND code_value = :pm_code AND reg_date LIKE :newDate ) AS stockCnt
			// 	FROM dual
			// ");
			
			$totalData[$newDate]["uploadCnt"] += $thisPmData["uploadCnt"];
			$chartData[$newDate]["uploadCnt"] += $thisPmData["uploadCnt"];
			$chartData[$newDate]["distCnt"] += $thisPmData["distCnt"];
			$chartData[$newDate]["deleteCnt"] += $thisPmData["deleteCnt"];
			$chartData[$newDate]["stockCnt"] += $thisPmData["stockCnt"];
		}
		
		if($newDate == $startDate) break;
	}

?>

	<div class="listWrap">
		<table>
			<colgroup>
				<col width="12%">
				<col width="22%">
				<col width="22%">
				<col width="22%">
				<col width="22%">
			</colgroup>
			<thead>
				<tr>
					<th>일자</th>
					<th>업로드DB</th>
					<th>분배DB</th>
					<th>잔여DB</th>
					<th>삭제DB</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($chartData as $date => $data){ ?>
					<tr>
						<td class="lp05"><?=$date?></td>
						<td class="lp05"><?=number_format($data["uploadCnt"])?></td>
						<td class="lp05"><?=number_format($data["distCnt"])?></td>
						<td class="lp05"><?=number_format($data["stockCnt"])?></td>
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