<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
    
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	if($_GET['code']){
		$code = $_GET['code'];
		$value = array(':use_yn'=> 'Y', ':auth_code' => '003', ':idx' => $_GET['code']);
		$query = "SELECT * FROM mt_member_cmpy WHERE use_yn = :use_yn AND auth_code = :auth_code AND idx = :idx ";
		$codeInfo = view_pdo($query, $value);
		if(!$codeInfo){
			www("/sub/db/dbRecallL");
			return false;
		}
	}

	# 메뉴설정
	$secMenu = "dbRecall";
	$trdMenu = ($code) ? "db{$code}" : "all";
	$codeInfo['company_name'] = dhtml($codeInfo['company_name']);
	
	# 콘텐츠설정
	$contentsTitle = ($code) ? "{$codeInfo['company_name']} DB통합관리" : "전체 DB통합관리";
	$contentsInfo = "생산업체에서 업로드된 DB를 전체 확인하실 수 있으며, 편의에 따라 DB자동분배 또는 DB분배를 담당자에게 분배가 가능합니다.<br>분배된 DB는 목록에서 사라져 DB분배관리에서 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB통합관리");
	array_push($contentsRoots, ($code) ? $codeInfo['company_name'] : "전체보기");
	array_push($contentsRoots, "목록");

	# 가이드 변수명 설정
	$guideName = "dbRecall";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y' AND dist_code = '003'";
	if($code){
		$andQuery .= " AND pm_code = '{$code}'";
	}

	# 초기 정렬
	$_GET['orderBy'] = ($_GET['orderBy']) ? $_GET['orderBy'] : "idx DESC";
	$overlap_yn = ($_GET['overlap_yn']) ? $_GET['overlap_yn'] : "";

	if($overlap_yn){
		$andQuery .= " AND overlap_yn = '{$overlap_yn}'";
	}

	# 데이터 간단정리표

	$value = array(''=>'');
	$query = "
		SELECT
			( SELECT COUNT(*) FROM mt_db {$andQuery} ) AS totalCnt
			, ( SELECT COUNT(*) FROM mt_db {$andQuery} AND reg_date LIKE '".date("Y-m-d")."%' ) AS todayCnt
		FROM dual
	";
	$dashboard = view_pdo($query, $value);

	if($_GET["gradeCode"]){
		$andQuery .= " AND grade_code = '{$_GET["gradeCode"]}'";
	}	

	if($_GET["csStatusCode"]){
		$andQuery .= " AND cs_status_code = '{$_GET["csStatusCode"]}'";
	}

	if($_GET["fcCode"]){
		$andQuery .= " AND m_idx = '{$_GET["fcCode"]}'";
	}
	
	if($_GET["cs_tel"]){
		$cs_tel = $_GET["cs_tel"];
		$andQuery .= " AND cs_tel LIKE '%{$cs_tel}%'";
	}

	if($_GET["cs_name"]){
		$cs_name = $_GET["cs_name"];
		$andQuery .= " AND cs_name LIKE '%{$cs_name}%'";
	}

	if($_GET["pm_code"]){
		$andQuery .= " AND pm_code = '{$_GET["pm_code"]}'";
	}

	if($_GET["made_date"]){
		$made_date = $_GET["made_date"];
		$andQuery .= " AND made_date LIKE '%{$made_date}%'";
	}

	if($_GET["cs_etc01"]){
		$cs_etc01 = $_GET["cs_etc01"];
    $andQuery .= " AND cs_etc01 LIKE '%{$cs_etc01}%'";
	}

	if($_GET["cs_etc02"]){
		$cs_etc02 = $_GET["cs_etc02"];
		$andQuery .= " AND cs_etc02 LIKE '%{$cs_etc02}%'";
	}

	if($_GET["cs_etc03"]){
		$cs_etc03 = $_GET["cs_etc03"];
		$andQuery .= " AND cs_etc03 LIKE '%{$cs_etc03}%'";
	}

	if($_GET["cs_etc04"]){
		$cs_etc04 = $_GET["cs_etc04"];
		$andQuery .= " AND cs_etc04 LIKE '%{$cs_etc04}%'";
	}

	if($_GET["cs_etc05"]){
		$cs_etc05 = $_GET["cs_etc05"];
		$andQuery .= " AND cs_etc05 LIKE '%{$cs_etc05}%'";
	}

	if($_GET["cs_etc06"]){
		$cs_etc06 = $_GET["cs_etc06"];
		$andQuery .= " AND cs_etc06 LIKE '%{$cs_etc06}%'";
	}

	if($_GET["cs_etc07"]){
		$cs_etc07 = $_GET["cs_etc07"];
		$andQuery .= " AND cs_etc07 LIKE '%{$cs_etc07}%'";
	}

	if($_GET["cs_etc08"]){
		$cs_etc08 = $_GET["cs_etc08"];
		$andQuery .= " AND cs_etc08 LIKE '%{$cs_etc08}%'";
	}

	if($_GET["cs_etc09"]){
		$cs_etc09 = $_GET["cs_etc09"];
		$andQuery .= " AND cs_etc09 LIKE '%{$cs_etc09}%'";
	}

	if($_GET["cs_etc10"]){
		$cs_etc10 = $_GET["cs_etc10"];
		$andQuery .= " AND cs_etc10 LIKE '%{$cs_etc10}%'";
	}

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_db");

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];

	$value = array(':use_yn'=> 'Y',':list_yn'=> 'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		AND list_yn = :list_yn
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		$thisdatas['type'] = $row['column_type'];
		
		$columnArr[$columnCnt] = $thisdatas;
	}
	$columnWidth = 60 / ($columnCnt + 2);

?>

	<!-- 데이터 간단정리표 -->
	<div class="dataInfoSimpleWrap">
		<div>
			<div class="iconWrap">
				<i class="fas fa-file-import"></i>
			</div>
			<div class="conWrap">
				<ul class="dataCntList">
					<li>
						<span class="label">전체DB</span>
						<span class="value"><?=number_format($dashboard['totalCnt'])?></span>
					</li>
					<li>
						<span class="label">오늘의 업로드DB</span>
						<span class="value"><?=number_format($dashboard['todayCnt'])?></span>
					</li>
				</ul>
			</div>
			<?php if(!$code){ ?>
				<div class="btnWrap">
					<!-- <button type="button" class="typeBtn btnRed" onclick="popupControl('open', 'excel', '/sub/db/excel/dbAll', 'DB 대량업로드');"><i class="fas fa-plus-circle"></i>DB업로드</button>
					<button type="button" class="typeBtn btnGray01 popupBtn" data-type="open" data-target="write" data-url="/sub/db/dbAllW" data-name="DB 추가하기" style="height: 35px; line-height: 35px; font-size: 15px; margin-top: 5px;"><i class="fas fa-plus-circle"></i>DB추가</button> -->
				</div>
			<?php } ?>
		</div>
	</div>

	<!-- 데이터 검색영역 -->
	<!-- <div class="searchWrap">
		<form method="get">
			<input type="hidden" name="code" value="<?=$code?>">
			<ul class="formWrap">
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="cs_name"><?=$customLabel["cs_name"]?></option>
						<option value="cs_tel"><?=$customLabel["cs_tel"]?></option>
					<?php foreach($columnArr as $val){ ?>
						<option value="<?=$val['code']?>"><?=$val['name']?></option>
					<?php } ?>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<select class="txtBox" name="setDate">
						<option value="made" <?=($_GET['setDate']=='made') ? 'selected' : ''?>>생산일자</option>
						<option value="reg" <?=($_GET['setDate']=='reg') ? 'selected' : ''?>>등록일시</option>
					</select>
					<input type="text" class="txtBox s_date" name="s_date" value="<?=$_GET['s_date']?>" dateonly>
					<span class="hypen">~</span>
					<input type="text" class="txtBox e_date" name="e_date" value="<?=$_GET['e_date']?>" dateonly>
					<span class="dateBtn" data-s="<?=date("Y-m-d")?>" data-e="<?=date("Y-m-d")?>">오늘</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 7 days"))?>" data-e="<?=date("Y-m-d")?>">7일</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 1 month"))?>" data-e="<?=date("Y-m-d")?>">1개월</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 3 month"))?>" data-e="<?=date("Y-m-d")?>">3개월</span>
				</li>
			</ul>
			<div class="btnWrap">
				<button type="submit" class="typeBtn">조회</button>
			</div>
		</form>
	</div> -->
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="overlap_yn">
				<option value="">중복여부선택</option>
				<option value="Y">중복</option>
				<option value="N">미중복</option>
			</select>
			<select class="listSet" id="orderBy">
				<option value="idx ASC">고유번호 오름차순</option>
				<option value="idx DESC">고유번호 내림차순</option>
				<option value="made_date ASC">생산일시 오름차순</option>
				<option value="made_date DESC">생산일시 내림차순</option>
				<option value="reg_date ASC">등록일시 오름차순</option>
				<option value="reg_date DESC">등록일시 내림차순</option>
			</select>
			<input type="text" class="txtBox2" id="listCnt" title="리스트에 노출시킬 수량을 입력해주세요">
			<button type="button" class="typeBtn btnGray02" id="listCntBtn" style="width:60px">보기</button>
			<label class="left detailSearch" style="margin-left: 20px;">
				<i class="fas fa-search"></i>상세검색
			</label>
		</div>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		<?php if($codeInfo['use_yn']=="Y" || !$code){ ?>
			<button type="button" class="typeBtn btnRed big dbAutoDistBtn"><i class="fas fa-share-alt"></i>DB자동분배</button>
			<button type="button" class="typeBtn btnMain big dbDistBtn"><i class="fas fa-plus-circle"></i>DB분배</button>
			<div class="line"></div>
		<?php } ?>
		<?php if(!$code){ ?>
			<!-- <button type="button" class="typeBtn btnGray01 big popupBtn" data-type="open" data-target="write" data-url="/sub/db/dbAllW" data-name="DB 추가하기"><i class="fas fa-plus-circle"></i>DB추가</button> -->
		<?php } ?>
			<button type="button" class="typeBtn btnOrange big dbCsDeleteBtn"><i class="fas fa-trash-alt"></i>상담내역삭제</button>
			<button type="button" class="typeBtn btnGray02 big dbAllDeleteBtn"><i class="fas fa-trash-alt"></i>DB삭제</button>
		</div>
		<div class="right">
		<?php if($codeInfo['use_yn']=="Y" || !$code){ ?>
			<?php if($user['excel_yn'] =="Y"){ ?>
				<button type="button" class="typeBtn btnGreen01" style="background-color: #fff; color: #b3b3b3; border-color: #b3b3b3;" onclick="popupControl('open', 'excel', '/sub/db_recall/excel/dbSelectRecall', '엑셀항목설정<span style=\'color: #999999; margin-left: 20px;font-size: 15px; font-weight: 100;\'> 원하시는 항목을 선택하여 엑셀을 다운로드 받으실 수 있습니다.</span>');"><i class="fas fa-file-excel"></i>엑셀항목설정</button> 
				<div class="line" style="width: 1px; height: 35px; float: left; background-color: #CCC; margin: 0 15px;"></div>
			<?php } ?>
		<?php } ?>
		<?php if(!$code){ ?>
			<!-- <button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbAll', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button>  -->
		<?php } ?>
		<?php if($codeInfo['use_yn']=="Y" || !$code){ ?>
			<?php if($user['excel_yn'] =="Y"){ ?>
				<a href="/excel/db/dbRecallL?code=<?=$code?>" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
			<?php } ?>
		<?php } ?>
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="4%">
				<col width="8%">
				<col width="8%">
				<col width="8%">
				<col width="8%">
			<?php for($i = 0; $i < ($columnCnt + 2); $i++){ ?>
				<col width="<?=$columnWidth?>%">
			<?php } ?>
                <col width="4%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">
						<input type="checkbox" id="listDataAllCheck">
						<label class="ch" for="listDataAllCheck">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</th>
					<th rowspan="2">NO</th>
					<th rowspan="2">중복여부</th>
					<th rowspan="2">DB고유번호<br>(업로드일)</th>
					<th rowspan="2">생산업체</th>
					<th rowspan="2">생산일자</th>
					<th colspan="<?=($columnCnt + 2)?>">DB정보</th>
                    <th rowspan="2">상담</th>
				</tr>
				<tr>
					<th><?=$customLabel["cs_name"]?></th>
					<th><?=$customLabel["cs_tel"]?></th>
				<?php foreach($columnArr as $val){ ?>
					<th><?=$val['name']?></th>
				<?php } ?>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT status_name FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_status_name
						, ( SELECT number_yn FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_number_yn
						, ( SELECT grade_name FROM mc_db_grade_info WHERE grade_code = MT.grade_code ) AS grade_name
						, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.pm_code ) AS pm_name
						, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
						, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
					FROM mt_db MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
                $value =  array();
				$query = "SELECT idx,cs_etc10 FROM mt_db {$andQuery} {$orderQuery}";
				$dbDbat = list_pdo($query, $value);

				$db_data = [];
				while($data = $dbDbat->fetch(PDO::FETCH_ASSOC)){
					array_push($db_data,$data);
				}
                
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
                    $db_key = "";
					foreach($db_data as $key => $data){
						if($data['idx'] == $row['idx']){
							$db_key = $key; 
							break;
						}
					}
	
					$prev_idx = $db_data[$db_key-1]['idx'];
					$next_idx = $db_data[$db_key+1]['idx'];

                    # 200624 최종상담값 가져오기
					$value = array(':idx'=> $row['idx']);
					$query = "SELECT COUNT(*) AS cnt FROM mt_db_cs_log WHERE use_yn = 'Y' AND db_idx = :idx";
					$lastCsCnt = view_pdo($query, $value)['cnt'];


					$query = "SELECT status_code FROM mt_db_cs_log WHERE use_yn = 'Y' AND db_idx = :idx ORDER BY idx DESC";
					$lastCsStatus = view_pdo($query, $value)['status_code'];

					if($lastCsStatus){
						$value = array(':status_code'=> $lastCsStatus);
						$query = "SELECT finish_yn FROM mc_db_cs_status WHERE status_code = '{$lastCsStatus}'";
						$lastCsStatus = view_pdo($query, $value)['finish_yn'];
					} else {
						$lastCsStatus = "N";
					}
			?>
				<tr class="rowMove popupBtn" data-type="open" data-target="mod" data-url="/sub/db/dbAllU?idx=<?=$row['idx']?>" data-name="DB정보">
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="stopProgram"><?=($row['overlap_yn']=="Y") ? "<span style='color:#CD3333; font-weight: 500;'>중복</span>" : "미중복"?><?php if($row['overlap_yn']=="Y") { ?><i class="fas fa-bars csBtn click dbCsBtn<?=$lastCsStatus?>" style=" position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 15px;" onclick='popupControl("open", "overL", "/sub/db/overL?idx=<?=$row['idx']?>", "DB 중복리스트");'></i><?php } ?></td>
					<td class="lp05">D-<?=$row['idx']?><br><span style="font-size: 12px; color: #AAA;"><?=date("y-m-d H:i:s", strtotime($row['reg_date']))?></span></td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['pm_name']){ ?>
						<span><?=dhtml($row["pm_name"])?></span>
						<br><span style="font-size: 12px; color: #AAA;">PM<?=$row["pm_code"]?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					<td class="lp05">
						<?=date("Y-m-d", strtotime($row['made_date']))?>
						<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['made_date']))?></span>
					</td>
					<td><?=($row['cs_name']) ? dhtml($row['cs_name']) : "-"?></td>
					<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
				<?php foreach($columnArr as $val){ ?>
					<td class="lp05 <?=($val['type'] == "file")? "stopProgram" : ""?>">
						<?php 
						
						if($val['type'] == "file"){
							if($row["{$val['code']}"]){
								$value = explode( '@#@#', $row["{$val['code']}"] );
								echo "<a href='/upload/db_etc/{$value[0]}' class='db_csdwon' download='{$value[1]}'>{$value[1]}<i class=\"fas fa-download\"></i></a>";	
							}else{
								echo "-";
							}
						} else{
							echo ($row["{$val['code']}"]) ? dhtml2($row["{$val['code']}"]) : "-";
						}
						?>
					</td>
				<?php } ?>
                    <td class="stopProgram">
						<i class="fas fa-headphones csBtn click dbCsBtn<?=$lastCsStatus?>" style="font-size: 16px;" onclick='popupControl("open", "csLog", "/sub/db/csLogL?idx=<?=$row['idx']?>", "DB 상담기록","","","<?=$prev_idx?>","<?=$next_idx?>","<?=$row['idx']?>");'></i><br>
						<span style="font-size: 11px; color: #AAA;" class="lp05">(<?=number_format($lastCsCnt)?>/<?=number_format($todayCScnt)?>)</span>
					</td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="<?=($columnCnt + 10)?>" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		<?php if($codeInfo['use_yn']=="Y" || !$code){ ?>
			<button type="button" class="typeBtn btnRed big dbAutoDistBtn"><i class="fas fa-share-alt"></i>DB자동분배</button>
			<button type="button" class="typeBtn btnMain big dbDistBtn"><i class="fas fa-plus-circle"></i>DB분배</button>
			<div class="line"></div>
		<?php } ?>
		<?php if(!$code){ ?>
			<!-- <button type="button" class="typeBtn btnGray01 big popupBtn" data-type="open" data-target="write" data-url="/sub/db/dbAllW" data-name="DB 추가하기"><i class="fas fa-plus-circle"></i>DB추가</button> -->
		<?php } ?>
		<button type="button" class="typeBtn btnOrange big dbCsDeleteBtn"><i class="fas fa-trash-alt"></i>상담내역삭제</button>
			<button type="button" class="typeBtn btnGray02 big dbAllDeleteBtn"><i class="fas fa-trash-alt"></i>DB삭제</button>
		</div>
		<div class="right">	
		<?php if($codeInfo['use_yn']=="Y" || !$code){ ?>
			<?php if($user['excel_yn'] =="Y"){ ?>
				<button type="button" class="typeBtn btnGreen01" style="background-color: #fff; color: #b3b3b3; border-color: #b3b3b3;" onclick="popupControl('open', 'excel', '/sub/db_recall/excel/dbSelectRecall', '엑셀항목설정<span style=\'color: #999999; margin-left: 20px;font-size: 15px; font-weight: 100;\'> 원하시는 항목을 선택하여 엑셀을 다운로드 받으실 수 있습니다.</span>');"><i class="fas fa-file-excel"></i>엑셀항목설정</button> 
				<div class="line" style="width: 1px; height: 35px; float: left; background-color: #CCC; margin: 0 15px;"></div>
			<?php } ?>
		<?php } ?>
		<?php if(!$code){ ?>
			<!-- <button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbAll', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button>  -->
		<?php } ?>
		<?php if($codeInfo['use_yn']=="Y" || !$code){ ?>
			<?php if($user['excel_yn'] =="Y"){ ?>
				<a href="/excel/db/dbRecallL?code=<?=$code?>" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
			<?php } ?>
		<?php } ?>
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<?php
	
		# 상담기록
		$value = array(':use_yn' => 'Y', ':db_idx' => "{$view['idx']}");
		$query = "
				SELECT COUNT(*) as totalCnt
				FROM mt_db_cs_log MT
				WHERE use_yn = :use_yn
				AND db_idx = :db_idx
				ORDER BY idx DESC
			";

		$totalCnt = view_pdo($query, $value)['totalCnt'];

		$query = "
				SELECT MT.*
					, ( SELECT status_name FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS status_name
					, ( SELECT number_yn FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS number_yn
					, ( SELECT color FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS color
					, ( SELECT number_label FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS number_label
					, ( SELECT m_name FROM mt_member WHERE MT.reg_idx = idx ) AS m_name
					, ( SELECT m_id FROM mt_member WHERE MT.reg_idx = idx ) AS m_id
				FROM mt_db_cs_log MT
				WHERE use_yn = :use_yn
				AND db_idx = :db_idx
				{$andQuerys}
				ORDER BY idx DESC
			";
		$cs = list_pdo($query, $value);

		# 컬럼 정리
		$columnCnt = 0;
		$columnArr = [];
		$value = array(':use_yn' => 'Y');
		$query = "
				SELECT *
				FROM mt_db_cs_info
				WHERE use_yn = :use_yn
				AND list_yn = 'Y'
				ORDER BY sort ASC
			";
		$columnData = list_pdo($query, $value);

		while ($row = $columnData->fetch(PDO::FETCH_ASSOC)) {
			$columnCnt++;

			$thisdatas = [];
			$thisdatas['name'] = $row['column_name'];
			$thisdatas['code'] = $row['column_code'];
			$thisdatas['type'] = $row['column_type'];

			$columnArr[$columnCnt] = $thisdatas;
		}

		# 메인번호 가져오기
		$value = array(':use_yn' => 'Y', ':main_yn' => 'Y');
		$query = "
		SELECT sent_tel FROM mt_sms_tel WHERE use_yn = :use_yn AND main_yn = :main_yn
		";
		$mainTel = view_pdo($query, $value)["sent_tel"];

		$gradeArr = [];
		$value = array('' => '');
		$query = "SELECT grade_name, grade_code FROM mc_db_grade_info WHERE use_yn = 'Y' ORDER BY grade_code ASC";
		$grade = list_pdo($query, $value);

		while($row = $grade->fetch(PDO::FETCH_ASSOC)){
			$thisdatas = [];
			$thisdatas['grade_name'] = $row['grade_name'];
			$thisdatas['grade_code'] = $row['grade_code'];
			
			array_push($gradeArr, $thisdatas);
		}

		$companyArr = [];
		$value = array('' => '');
		$query = "SELECT company_name, pm_code FROM mt_member_cmpy WHERE use_yn = 'Y' AND pm_code IS NOT NULL ORDER BY idx ASC";
		$company = list_pdo($query, $value);

		while($row = $company->fetch(PDO::FETCH_ASSOC)){
			$thisdatas = [];
			$thisdatas['company_name'] = $row['company_name'];
			$thisdatas['pm_code'] = $row['pm_code'];
			
			array_push($companyArr, $thisdatas);
		}

	?>
	<form method="get">
		<div class="popupDetail">
			<div class="popupDetail2">
				<p style="font-size: 16px; color: #333; padding: 20px 33px; padding-bottom: 10px; font-weight: 600;"><span style="color: #17008C">상세필터</span> 설정</p>
				<div class="popupDetailBody">
					<div style="width: 100%; float:left; padding:10px; margin: 0 auto;">
						<div class="detailSearchElement">
							<p class="detailElementTit">이름</p>
							<input type="text" class="txtBox value detailSearchInput" name="cs_name" placeholder="이름" value="<?=$_GET['cs_name']?>">
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">연락처</p>
							<input type="text" class="txtBox value detailSearchInput" name="cs_tel" placeholder="숫자만 입력해주세요" value="<?=$_GET['cs_tel']?>">
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">생산업체</p>
							<select class="detailSearchSelect" name="pm_code">
								<option value="">선택</option>
							<?php foreach($companyArr as $val){ ?>
								<option value="<?=$val['pm_code']?>" <?=($_GET['pm_code'] == $val['pm_code']) ? "selected" : ""?>><?=$val['company_name']?></option>
							<?php } ?>
							</select>
						</div>
		
						<div class="detailSearchElement">
							<p class="detailElementTit">생산일자</p>
							<div class="dateInputWrap">
								<div class="sDate detailSearchInput">
									<input type="text" class="txtBox" name="made_date" id="made_date" dateonly placeholder="생산일자" style="border-radius: 5px;" value="<?=$_GET['made_date']?>" autocomplete="off">
									<i class="fas fa-calendar-alt"></i>
								</div>
								<!-- <div class="dateResetBtn" style="cursor: pointer;">
									<i class="fas fa-redo"></i>
								</div> -->
							</div>
						</div>
					</div>
					
					<?php 
						if ($columnCnt > 0) {
					?>
					<hr class="detailElementLine">
		
					<div style="width: 100%; float:left; padding:10px; margin: 0 auto;">
						<p class="detailElementTit2">DB 상세정보</p>
						<?php 
							$idx = 0;
							foreach ($columnArr as $val) {
								$idx++;
								if ($idx < 10) {
									$idx_num = "cs_etc0" . $idx;
								}
								else {
									$idx_num = "cs_etc" . $idx;
								}
		
								// 텍스트 입력
								if ($val['type'] == 'text' || $val['type'] == 'textarea' || $val['type'] == 'number') { ?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
										<input type="text" class="txtBox value detailSearchInput" name="<?=$val['code']?>" placeholder="<?=$val['name']?>" value="<?=$_GET[$idx_num]?>">
									</div>
								<?php	} ?>
	
								<!-- 단일 선택 -->
								<?php if ($val['type'] == 'select' || $val['type'] == 'radio') { ?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
											<?php
												$selectArr = [];
												$value = array(':idx' => $idx);
												$query = "SELECT info_val FROM mt_db_cs_info_detail WHERE use_yn = 'Y' AND info_idx = :idx ORDER BY sort ASC";
												$select_info = list_pdo($query, $value);
		
												while($row = $select_info->fetch(PDO::FETCH_ASSOC)){
													$thisdatas = [];
													$thisdatas['info_val'] = $row['info_val'];
													
													array_push($selectArr, $thisdatas);
												}	
											?>
										<select class="detailSearchSelect" name="<?=$val['code']?>">
											<option value="">선택</option>
										<?php foreach($selectArr as $r) { ?>
											<option value="<?=$r['info_val']?>" <?=($_GET[$idx_num] == $r['info_val']) ? "selected" : ""?>><?=$r['info_val']?></option>
										<?php } ?>
										</select>
									</div>
								<?php	} ?>
		
								<!-- 날짜선택 -->
								<?php if ($val['type'] == 'datepicker') { ?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
										<div class="dateInputWrap">
											<div class="sDate detailSearchInput " style="width: 220px; position: relative;">
												<input type="text" class="txtBox" style="border-radius: 5px;" name="<?=$val['code']?>" id="<?=$val['code']?>" dateonly placeholder="<?=$val['name']?>"  value="<?=$_GET[$idx_num]?>" autocomplete="off">
												<i class="fas fa-calendar-alt" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color:#D8D8D8;"></i>
											</div>
											<!-- <div class="dateResetBtn" style="cursor: pointer;">
												<i class="fas fa-redo"></i>
											</div> -->
										</div>
									</div>
								<?php	} ?>
								
								<!-- 다중선택 -->
								<?php if ($val['type'] == 'checkbox') { 
										$tmpIdx = -1;	?>
									<div class="detailSearchElement">
										<p class="detailElementTit"><?=$val['name']?></p>
										<?php
											$detailArr = [];
											$value = array(':idx' => $idx);
											$query = "SELECT info_val FROM mt_db_cs_info_detail WHERE use_yn = 'Y' AND info_idx = :idx ORDER BY sort ASC";
											$detail_info = list_pdo($query, $value);
		
											while($row = $detail_info->fetch(PDO::FETCH_ASSOC)){
												$thisdatas = [];
												$thisdatas['info_val'] = $row['info_val'];
												array_push($detailArr, $thisdatas);
											}	?>
		
										<button type="button" class="dropdown-btn detailSearchSelect detailMultiple">옵션 선택</button>
										<div class="multiple-dropdown">
											<input type="hidden" name="<?=$val['code']?>" class="multi_etc" value="<?=$checkedValues?>">
											<?php 
												$multi = explode('@', $_GET[$idx_num]);
	
												foreach($detailArr as $r) { 
													$tmpIdx++; ?>
													<div>
														<input type="checkbox" class="item_box" id="check3_<?=$tmpIdx?>" value="<?=$r['info_val']?>" <?=in_array($r['info_val'], $multi) ? 'checked' : '' ?>>
														<label class="checkBox" for="check3_<?=$tmpIdx?>">
															<i class="fas fa-check-square on"></i>
															<i class="far fa-square off"></i>
														</label>
														<label for="check3_<?=$tmpIdx?>"><?=$r['info_val']?></label>
													</div>
											<?php }	?>
										</div>
									</div>
								<?php	} ?>
							<?php }
							}
						?>
					</div>
				</div>
				<div style="background: #F5F5F5; margin-top: 10px; float: left; width: 100%; height: 60px; padding-top: 8px; border-bottom-right-radius: 20px; border-bottom-left-radius: 20px;">
					<label class="detailSearch right detailReload" style="border:none; color: #cccccc; margin-left: 10px;">
						<i class="fas fa-redo"></i>초기화
					</label>
					<button type="button" class="typeBtn btnGray02 popupCloseBtn left" data-target="csLogL" style="width: 100px; margin-left: 435px; color: #8C8C8C; background-color: #ffffff;">닫기</button>
					<button type="submit">
						<label class="detailSearch2 left" style="margin-left: 10px; background-color: #ffffff;">
							<i class="fas fa-search"></i>검색
						</label>
					</button>
				</div>
			</div>
		</div>
	</form>

	<style>
		.sDate { width: 10%; min-width: 140px; float:left; }
		.sDate .fa-calendar-alt { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #D8D8D8; pointer-events: none; }
		.detailSearchElement { width: 33%; float:left; padding:10px 23px; }
		.detailSearchInput { width: 220px !important; float:left; margin-top: 10px; border-radius: 5px;}
		.detailSearchSelect { width: 220px; float:left; height: 35px; margin-top: 10px; border: 1px solid #EBEBEB; color: #666; border-radius: 5px;}
		.detailElementTit { color: #666666; font-size: 14px;}
		.detailElementTit2 { color: #999999; margin: 10px 0px 10px 23px; font-size: 14px;}
		.detailElementLine { border-top: 1px solid #EFEFEF; width: 748px; align-self: center;}
		.detailMultiple { background: #ffffff; padding-left: 10px; text-align: left; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;}

		.multiple-dropdown {
			display: none;
			position: absolute;
			top: 40px;
			left: 22px;
			background: white;
			color: black;
			padding: 10px;
			border: 1px solid #ccc;
			width: 220px;
			border-radius: 5px;
			z-index: 999;
		}
		.multiple-dropdown > div > label:last-of-type { font-size: 15px; margin-left: 5px;}
		.multiple-dropdown.active { display: block; }
		.dateResetBtn { color: #666; margin-top: 15px; margin-left: 5px; float: left}

		.popupDetailBody { overflow-y: auto; padding: 10px; max-height: calc(80vh - 80px); }
	</style>
	
	<script type="text/javascript">
		$(function(){

			$(document).on("focus", "input[dateonly]", function() {
				$(this).prop("readonly", false);
			});

			$(document).on("input", "input[dateonly]", function() {
				this.value = this.value.replace(/[^0-9-]/g, '');
			});

			$(".detailSearch").click(function() {
				$(".popupDetail").show();
			});

			$(".popupCloseBtn").click(function() {
				$(".popupDetail").hide();
			});

			$(".multiple-dropdown").click(function (e) {
				e.stopPropagation();
			});

			$(".detailReload").click(function () {
				const popup = $(".popupDetail2");

				popup.find("input:not([type='checkbox'])").val("");
				popup.find("textarea").val("");
				popup.find("select").prop("selectedIndex", 0);
				popup.find(".item_box").prop("checked", false);
				popup.find(".detailSearchElement").each(function () {
					$(this).find(".detailMultiple").text("옵션 선택");
				});
			});

			$(".dropdown-btn").click(function (e) {
				e.preventDefault();
				e.stopPropagation();
				const box = $(this).siblings(".multiple-dropdown");
				$(".multiple-dropdown").not(box).removeClass("active");
				box.toggleClass("active");
			});

			$(document).click(function () {
				$(".multiple-dropdown").removeClass("active");
			});

			$(".detailSearchElement").each(function () {
				const parent = $(this);
				const checkedValues = parent.find(".item_box:checked").map(function () {
					return $(this).val();
				}).get();

				if (checkedValues.length > 0) {
					parent.find(".detailMultiple").text(`${checkedValues.length}개 선택 (${checkedValues.join(", ")})`);
				} else {
					parent.find(".detailMultiple").text("옵션 선택");
				}
			});

			$(".item_box").change(function () {
				const parent = $(this).closest(".detailSearchElement");
				const checkedValues = parent.find(".item_box:checked").map(function () {
						return $(this).val();
				}).get();
				
				if (checkedValues.length > 0) {
					var text = `${checkedValues.length}개 선택 (${checkedValues.join(", ")})`;
					parent.find(".detailMultiple").text(text);
				} else {
					parent.find(".detailMultiple").text("옵션 선택");
				}
				parent.find(".multi_etc").val(checkedValues.join("@"));
			});

			$(".dateResetBtn").click(function() {
				$(this).siblings(".sDate").find("input").val("");
			});

			$(".dbDistBtn").click(function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("분배할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				popupControl("open", "dist", "/sub/db/dbDistTM", "DB 수동분배", false, "선택한 DB를 수동으로 분배하실수 있습니다.");
			});

			$(".dbAutoDistBtn").click(function(){
				popupControl("open", "dist", "/sub/db/dbDistAuto?code=<?=$code?>", "DB 자동분배", false, "<?=($code) ? "{$codeInfo['company_name']}의" : "모든"?> DB를 자동으로 분배하실수 있습니다.");
			});

			$("#listCntBtn").click(function(event) {
				var target = "listCnt";
				var val = $("#listCnt").val();
				getClean(target, val);
			});



			$(".dbCsDeleteBtn").click(function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("삭제할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}
			
				idx = idx.join(",");
			
				if(confirm("선택된 DB들을 삭제하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){
						$.ajax({
							url : "/ajax/db_recall/csLogDP",
							data : {
								idx : idx
							},
							type : "POST",
							success : function(result){
								alert("삭제가 완료되었습니다.");
								window.location.reload();
							}
						})
					});
				}
			});

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>