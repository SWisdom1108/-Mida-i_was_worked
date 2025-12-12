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
	$dbList = [];
	
	$andQuery = " WHERE m_idx = '{$_GET["code"]}' AND date_format(dist_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d') AND date_format(dist_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	// $andQuery = " AND code_type = 'fc' AND code_value = '{$_GET["code"]}' AND date_format(reg_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d') AND date_format(reg_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
	// $sql = list_sql("SELECT db_idx FROM mt_db_chart_log WHERE 1=1 {$andQuery}");
	// foreach ( $sql as $row ){
	// 	if($row["db_idx"]){
	// 		array_push($dbList, $row["db_idx"]);
	// 	}
	// }

	// $dbList = implode(",", $dbList);
	// $andQuery = " WHERE idx IN ( {$dbList} )";

	# 데이터 정리
	$value = array(''=>'');
	$query = "
		SELECT MT.*
			, ( SELECT status_name FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_status_name
			, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.pm_code ) AS pm_name
			, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
			, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
		FROM mt_db MT
		{$andQuery}
		ORDER BY idx DESC
	";
	$sql = list_pdo($query, $value);

	# 페이징 정리
	paging("mt_db");

?>
	
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<a href="/excel/chart/dbAll005DBL?s_date=<?=$startDate?>&e_date=<?=$endDate?>&code=<?=$_GET["code"]?>" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
		</div>
	</div>

	<div class="listWrap">
		<table>
			<colgroup>
				<col width="6%">
				<col width="12%">
				
				<col width="15%">
				<col width="15%">
				
				<col width="12%">
				<col width="15%">
				<col width="15%">
				
				<col width="10%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th rowspan="2">등록일시</th>
					<th colspan="2">DB정보</th>
					<th colspan="3">분배정보</th>
					<th rowspan="2">삭제여부</th>
				</tr>
				<tr>
					<th>이름</th>
					<th>연락처</th>
					<th>분배일시</th>
					<th><?=$customLabel["tm"]?></th>
					<th style="border-right: 1px solid #FFF;">담당자</th>
				</tr>
			</thead>
			<tbody>
				<?php while($data = $sql->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
						<td class="lp05"><?=listNo()?></td>
						<td class="lp05" style="line-height: 15px;">
							<?=date("Y-m-d", strtotime($data['reg_date']))?>
							<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($data['reg_date']))?></span>
						</td>

						<td><?=($data['cs_name']) ? $data['cs_name'] : "-"?></td>
						<td class="lp05"><?=($data['cs_tel']) ? $data['cs_tel'] : "-"?></td>

						<td class="lp05" style="line-height: 15px;">
						<?php if($data["dist_code"] == "002"){ ?>
							<?=date("Y-m-d", strtotime($data['order_by_date']))?>
							<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($data['order_by_date']))?></span>
						<?php } else { ?>
							<span>-</span>
						<?php } ?>
						</td>
						<td class="lp05"><?=($data['tm_name']) ? "TM{$data['tm_code']}<br>{$data['tm_name']}" : "-"?></td>
						<td class="lp05"><?=($data['m_name']) ? "FC{$data['m_idx']}<br>{$data['m_name']}" : "-"?></td>
						
						<td><?=($data["use_yn"] == "Y") ? '<span style="color: #CCC;">미삭제</span>' : '<span style="color: #DC3333;">삭제</span>'?></td>
					</tr>
				<?php  } ?>
				
				<?php if(!$totalCnt){ ?>
					<tr>
						<td colspan="8" class="no">조회된 데이터가 존재하지 않습니다.</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="dbList">닫기</button>
	</div>
	
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>