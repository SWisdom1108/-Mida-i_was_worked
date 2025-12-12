<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	if($_GET['code']){
		$code = $_GET['code'];
		$value = array(':idx' => $_GET['code']);
		$query = "SELECT * FROM mt_member_team WHERE use_yn = 'Y' AND idx = :idx";
		$codeInfo = view_pdo($query, $value);
		if(!$codeInfo){
			www("/sub/db/dbTeamL");
			return false;
		}
	}

	# 메뉴설정
	$secMenu = "dbDent";
	$trdMenu = ($code) ? "tm{$code}" : "all";
	
	# 콘텐츠설정
	$contentsTitle = ($code) ? "{$codeInfo['team_name']} 매칭DB관리" : "전체 매칭DB관리";
	$contentsInfo = "{$customLabel["tm"]}의 매칭된 DB를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "매칭DB관리");
	array_push($contentsRoots, ($code) ? $codeInfo['team_name'] : "전체보기");
	array_push($contentsRoots, "목록");

	# 가이드 변수명 설정
	$guideName = "dbAll";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y' AND dist_code = '002' AND chart_num IS NOT NULL AND chart_num != '' ";
	if($code){
		$andQuery .= " AND tm_code = '{$code}'";
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


	# 201102 생산업체정렬

	$value = array(''=>'');

	$query = "SELECT * FROM mt_member_cmpy WHERE use_yn = 'Y' AND auth_code = '003' ORDER BY idx DESC";
	$pmList = list_pdo($query, $value);
	if($_GET["pmCode"]){
		$andQuery .= " AND pm_code = '{$_GET["pmCode"]}'";
	}

	# 200901 팀원 및 상담상태정렬
	$value = array(''=>'');
	$query = "SELECT * FROM mc_db_cs_status WHERE use_yn = 'Y' AND sms_yn = 'N' ORDER BY sort ASC";
	$csStatusList = list_pdo($query, $value);
	if($code){
		$value = array(':code'=>$code);
		$query = "SELECT * FROM mt_member WHERE tm_code = :code";
		$fcList = list_pdo($query, $value);
	}

	# 고객등급 정렬 2022.08.31(수)
	$value = array(''=>'');
	$query = "SELECT * FROM mc_db_grade_info WHERE use_yn = 'Y' ORDER BY grade_code ASC";
	$grade = list_pdo($query, $value);


	if($_GET["gradeCode"]){
		$andQuery .= " AND grade_code = '{$_GET["gradeCode"]}'";
	}	

	if($_GET["csStatusCode"]){
		$andQuery .= " AND cs_status_code = '{$_GET["csStatusCode"]}'";
	}

	if($_GET["fcCode"]){
		$andQuery .= " AND m_idx = '{$_GET["fcCode"]}'";
	}

	# 검색값 정리
	$_SEARCH["cs_status_name"] = " AND cs_status_code IN ( SELECT status_code FROM mc_db_cs_status WHERE status_name LIKE '%{$_GET["value"]}%' )";
	$_SEARCH["fc_id"] = " AND m_idx IN ( SELECT idx FROM mt_member WHERE m_id LIKE '%{$_GET["value"]}%' )";
	$_SEARCH["fc_idx"] = " AND m_idx IN ( SELECT idx FROM mt_member WHERE idx LIKE '%{$_GET["value"]}%' )";
	$_SEARCH["fc_name"] = " AND m_idx IN ( SELECT idx FROM mt_member WHERE m_name LIKE '%{$_GET["value"]}%' )";
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
	$columnWidth = 40 / ($columnCnt + 2);

	# 201102 엑셀관련섹션
	$_SESSION["excelAndQuery"] = $andQuery;
	$_SESSION["excelOrderQuery"] = $orderQuery;

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
			<?php if($code){ ?>
				<div class="btnWrap">
					<button type="button" class="typeBtn btnRed" onclick="popupControl('open', 'excel', '/sub/db/excel/dbTeam?code=<?=$code?>', 'DB 대량업로드');"><i class="fas fa-plus-circle"></i>DB업로드</button>
					<button type="button" class="typeBtn btnGray01 popupBtn" data-type="open" data-target="write" data-url="/sub/db/dbTeamW?code=<?=$code?>" data-name="DB 추가하기" style="height: 35px; line-height: 35px; font-size: 15px; margin-top: 5px;"><i class="fas fa-plus-circle"></i>DB추가</button>
				</div>
			<?php } else { ?>
				<div class="btnWrap">
					<button type="button" class="typeBtn btnRed" onclick="popupControl('open', 'excel', '/sub/db/excel/dbTeamAll', 'DB 대량업로드');"><i class="fas fa-plus-circle"></i>DB업로드</button>
				</div>
			<?php } ?>
		</div>
	</div>

	<!-- 데이터 검색영역 -->
	<div class="searchWrap">
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
						<option value="cs_status_name">상담상태</option>
						<option value="fc_name">담당자명</option>
						<option value="fc_idx">담당자코드</option>
						<option value="fc_id">담당자아이디</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<select class="txtBox" name="setDate">
						<option value="order_by" <?=($_GET['setDate']=='order_by') ? 'selected' : ''?>>분배일시</option>
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
	</div>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
			<span class="cnt" style="margin: 0 15px; color: #CCC; font-weight: 400; top: -1.5px;">|</span>
			<?php if($code){ ?>
				<select class="listSet" id="fcCode">
					<option value="">담당자별 보기</option>
				<?php while($row = $fcList->fetch(PDO::FETCH_ASSOC)){ ?>
					<option value="<?=$row["idx"]?>" <?=($_GET["fcCode"] == $row["idx"]) ? "selected" : ""?>><?=$row["m_name"]?>(<?=$row["m_id"]?>)</option>
				<?php } ?>
				</select>
				<span class="cnt" style="margin: 0 15px; color: #CCC; font-weight: 400; top: -1.5px;">|</span>
			<?php } ?>
			<select class="listSet" id="pmCode" style="margin-right: 10px;">
				<option value="">생산업체 선택</option>
			<?php while($row = $pmList->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?=$row["idx"]?>" <?=($_GET["pmCode"] == $row["idx"]) ? "selected" : ""?>><?=$row["company_name"]?></option>
			<?php } ?>
			</select>
			<select class="listSet" id="csStatusCode">
				<option value="">상담상태별 보기</option>
			<?php while($row = $csStatusList->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?=$row["status_code"]?>" <?=($_GET["csStatusCode"] == $row["status_code"]) ? "selected" : ""?>><?=$row["status_name"]?></option>
			<?php } ?>
			</select>
			<select class="listSet" id="gradeCode" >
				<option value="">고객등급별 보기</option>
			<?php while($row = $grade->fetch(PDO::FETCH_ASSOC)){ ?>
				<option value="<?=$row["grade_code"]?>" <?=($_GET["gradeCode"] == $row["grade_code"]) ? "selected" : ""?>><?=$row["grade_name"]?></option>
			<?php } ?>
			</select>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="order_by_date ASC">분배일시 오름차순</option>
				<option value="order_by_date DESC">분배일시 내림차순</option>
				<option value="reg_date ASC">등록일시 오름차순</option>
				<option value="reg_date DESC">등록일시 내림차순</option>
			</select>
			<select class="listSet" id="listCnt">
				<option value="15">15개씩 보기</option>
				<option value="30">30개씩 보기</option>
				<option value="50">50개씩 보기</option>
				<option value="100">100개씩 보기</option>
				<option value="9999999">전체 보기</option>
			</select>
		</div>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big dbDistBtn"><i class="fas fa-exchange-alt"></i>담당자변경</button>
			<div class="line"></div>
			<button type="button" class="typeBtn btnOrange big dbCsAllBtn"><i class="fas fa-edit"></i>상담일괄등록</button>
		<?php if($code){ ?>
			<button type="button" class="typeBtn btnGray01 big popupBtn" data-type="open" data-target="write" data-url="/sub/db/dbTeamW?code=<?=$code?>" data-name="DB 추가하기"><i class="fas fa-plus-circle"></i>DB추가</button>
		<?php } ?>
			<button type="button" class="typeBtn btnGray02 big dbAllDeleteBtn"><i class="fas fa-trash-alt"></i>DB삭제</button>
			<button type="button" class="typeBtn btnOrange dbCsStatusChange"><i class="fas fa-exchange-alt"></i>상담상태변경</button>
			<button type="button" class="typeBtn btnOrange dbGradeChange" style="background-color:#19234b; border:1px solid #19234b;"><i class="fas fa-exchange-alt"></i>고객등급변경</button>
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnSky smsSendBtn" title="SMS전송"><i class="fas fa-paper-plane"></i>SMS전송</button> 
			<?php if($user['excel_yn'] =="Y"){ ?>
				<button type="button" class="typeBtn btnGreen01" style="background-color: #fff; color: #b3b3b3; border-color: #b3b3b3;" onclick="popupControl('open', 'excel', '/sub/db/excel/dbSelectTeam', '엑셀항목설정<span style=\'color: #999999; margin-left: 20px;font-size: 15px; font-weight: 100;\'> 원하시는 항목을 선택하여 엑셀을 다운로드 받으실 수 있습니다.</span>');"><i class="fas fa-file-excel"></i>엑셀항목설정</button> 
				<div class="line" style="width: 1px; height: 35px; float: left; background-color: #CCC; margin: 0 15px;"></div>
			<?php } ?>
		<?php if($code){ ?>
			<button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbTeam?code=<?=$code?>', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button> 
		<?php } else { ?>
			<button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbTeamAll', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button> 
		<?php } ?>
<?php if($user['excel_yn'] =="Y"){ ?>
			<a href="/excel/db/dbTeamL?code=<?=$code?>" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
			<?php } ?>
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="4%">
				<col width="5%">
				<col width="5%">
				<col width="5%">
				<col width="4%">
			<?php for($i = 0; $i < ($columnCnt + 2); $i++){ ?>
				<col width="<?=$columnWidth?>%">
			<?php } ?>
				<col width="5%">
				<col width="5%">
				<col width="5%">
				<col width="5%">
				<col width="4%">
				<col width="5%">
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
					<th rowspan="2">DB고유번호</th>
					<th rowspan="2">생산업체</th>
					<th rowspan="2">등록일시</th>
					<th rowspan="2">고객등급</th>
					<th colspan="<?=($columnCnt + 2)?>">DB정보</th>
					<th colspan="3">분배정보</th>
					<th rowspan="2">상담상태</th>
					<th colspan="2">기록</th>
				</tr>
				<tr>
					<th><?=$customLabel["cs_name"]?></th>
					<th><?=$customLabel["cs_tel"]?></th>
				<?php foreach($columnArr as $val){ ?>
					<th><?=$val['name']?></th>
				<?php } ?>
					<th>분배일시</th>
					<th><?=$customLabel["tm"]?></th>
					<th>담당자</th>
					
					<th style="line-height: 15px;">상담<br><span style="font-size: 10px;">(전체/오늘)</span></th>
					<th>SMS</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');

				$query = "
					SELECT MT.*
						, ( SELECT status_name FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_status_name
						, ( SELECT number_yn FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS cs_number_yn
						, ( SELECT color FROM mc_db_cs_status WHERE status_code = MT.cs_status_code ) AS color
						, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.pm_code ) AS pm_name
						, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
						, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS m_name
						, ( SELECT grade_name FROM mc_db_grade_info WHERE grade_code = MT.grade_code ) AS grade_name
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
					
					# 201116 상담내용색상
					$csColor = ($row["cs_number_yn"] == "Y") ? $site['main_color'] : "#666";
					$color = $row["color"];
					
					# 201118 오늘자 상담횟수
					$value = array(':idx'=> $row['idx']);
					$query = "SELECT COUNT(*) AS cnt FROM mt_db_cs_log WHERE use_yn = 'Y' AND db_idx = '{$row["idx"]}' AND reg_date LIKE '".date("Y-m-d")."%'";
					$todayCScnt = view_pdo($query, $value)['cnt'];
					
					# 201118 SMS
					$smsCheckTel = str_replace("-", "", $row["cs_tel"]);
					$cs_tel = preg_replace("/^(\d{3})(\d{4})(\d{4})$/", "$1-$2-$3", $row["cs_tel"]);
					$checkTel = preg_replace("/[^0-9]*/s", "", $cs_tel);
					$checkTel2 = preg_replace('/-(\d{4})-/', '-$1', $cs_tel);
					$checkTel3 = preg_replace('/-/', '', $cs_tel, 1);


					$value = array(':receive_name'=> $row["cs_name"]);
					$query = "
						SELECT count(*) as cnt, MAX(reg_date) AS reg_date
						FROM mt_sms_log MT
						WHERE use_yn = 'Y'
						AND receive_name = :receive_name 
						AND receive_tel in ('{$cs_tel}', '{$checkTel}', '{$checkTel2}', '{$checkTel3}')
						ORDER BY idx DESC limit 0,1";

					$smsInfo = view_pdo($query, $value);

					$hex = str_replace('#', '', $color);
					$r = hexdec(substr($hex, 0, 2));
					$g = hexdec(substr($hex, 2, 2));
					$b = hexdec(substr($hex, 4, 2));
					
			?>
				<tr class="rowMove popupBtn" data-type="open" data-target="mod" data-url="/sub/db/dbTeamU?idx=<?=$row['idx']?>" data-name="DB정보" style="background-color: rgba(<?=$r?>, <?=$g?>, <?=$b?>, 0.2);">
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05">D-<?=$row['idx']?></td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['pm_name']){ ?>
						<span><?=dhtml($row["pm_name"])?></span>
						<br><span style="font-size: 12px; color: #AAA;">PM<?=$row["pm_code"]?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					<td class="lp05" style="line-height: 15px;">
						<?=date("Y-m-d", strtotime($row['reg_date']))?>
						<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['reg_date']))?></span>
					</td>
					<td class="lp05">
						<?php if($row['grade_code'] == '000') { ?>
								-
						<?php }else{ ?>
							<?=(dhtml($row['grade_name']))?>
						<?php } ?>
					</td>
					<td><?=($row['cs_name']) ? dhtml($row['cs_name']) : "-"?></td>
					<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
				<?php foreach($columnArr as $val){ ?>
					<td class="lp05 <?=($val['type'] == "file")? "stopProgram" : ""?>"><?php
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
						?></td>
				<?php } ?>
				
					<td class="lp05" style="line-height: 15px;">
						<?=date("Y-m-d", strtotime($row['order_by_date']))?>
						<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['order_by_date']))?></span>
					</td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['tm_name']){ ?>
						<span><?=dhtml($row["tm_name"])?></span>
						<br><span style="font-size: 12px; color: #AAA;">TM<?=$row["tm_code"]?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					<td class="lp05" style="line-height: 15px;">
					<?php if($row['m_name']){ ?>
						<span><?=dhtml($row["m_name"])?></span>
						<br><span style="font-size: 12px; color: #AAA;">FC<?=$row["m_idx"]?></span>
					<?php } else { ?>
						<span>-</span>
					<?php } ?>
					</td>
					
					<td class="lp05" style="line-height: 15px; ">
						<!-- 여기체크해봐야함 -->
						<?php $row["cs_status_name"] = dhtml($row["cs_status_name"]) ?>
						<?=($row['cs_status_name']) ? "<b style='color: {$color};'>{$row["cs_status_name"]}</b>" : "-"?>
						<br><span style="font-size: 12px; color: #AAA;"><?=($row['cs_status_date']) ? date("Y-m-d", strtotime($row['cs_status_date'])) : "-"?></span>
					</td>
					<td class="stopProgram">
						<i class="fas fa-headphones csBtn click dbCsBtn<?=$lastCsStatus?>" style="font-size: 16px;" onclick='popupControl("open", "csLog", "/sub/db/csLogL?idx=<?=$row['idx']?>", "DB 상담기록","","","<?=$prev_idx?>","<?=$next_idx?>","<?=$row['idx']?>");'></i><br>
						<span style="font-size: 11px; color: #AAA;" class="lp05">(<?=number_format($lastCsCnt)?>/<?=number_format($todayCScnt)?>)</span>
					</td>
					<td class="lp05" style="line-height: 15px;">
						<span><?=number_format($smsInfo["cnt"])?>건</span>
						<br><span style="font-size: 11px; color: #AAA;"><?=($smsInfo['reg_date']) ? date("Y-m-d", strtotime($smsInfo['reg_date'])) : "-"?></span>
					</td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="<?=($columnCnt + 14)?>" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big dbDistBtn"><i class="fas fa-exchange-alt"></i>담당자변경</button>
			<div class="line"></div>
			<button type="button" class="typeBtn btnOrange big dbCsAllBtn"><i class="fas fa-edit"></i>상담일괄등록</button>
		<?php if($code){ ?>
			<button type="button" class="typeBtn btnGray01 big popupBtn" data-type="open" data-target="write" data-url="/sub/db/dbTeamW?code=<?=$code?>" data-name="DB 추가하기"><i class="fas fa-plus-circle"></i>DB추가</button>
		<?php } ?>
			<button type="button" class="typeBtn btnGray02 big dbAllDeleteBtn"><i class="fas fa-trash-alt"></i>DB삭제</button>
			<button type="button" class="typeBtn btnOrange dbCsStatusChange"><i class="fas fa-exchange-alt"></i>상담상태변경</button>
			<button type="button" class="typeBtn dbGradeChange"style="background-color:#19234b; border:1px solid #19234b;"><i class="fas fa-exchange-alt"></i>고객등급변경</button>
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnSky smsSendBtn" title="SMS전송"><i class="fas fa-paper-plane"></i>SMS전송</button> 
			<?php if($user['excel_yn'] =="Y"){ ?>
				<button type="button" class="typeBtn btnGreen01" style="background-color: #fff; color: #b3b3b3; border-color: #b3b3b3;" onclick="popupControl('open', 'excel', '/sub/db/excel/dbSelectTeam', '엑셀항목설정<span style=\'color: #999999; margin-left: 20px;font-size: 15px; font-weight: 100;\'> 원하시는 항목을 선택하여 엑셀을 다운로드 받으실 수 있습니다.</span>');"><i class="fas fa-file-excel"></i>엑셀항목설정</button> 
				<div class="line" style="width: 1px; height: 35px; float: left; background-color: #CCC; margin: 0 15px;"></div>
			<?php } ?>
		<?php if($code){ ?>
			<button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbTeam?code=<?=$code?>', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button> 
		<?php } else { ?>
			<button type="button" class="typeBtn btnGreen01" onclick="popupControl('open', 'excel', '/sub/db/excel/dbTeamAll', 'DB 대량업로드');"><i class="fas fa-file-excel"></i>엑셀업로드</button> 
		<?php } ?>
<?php if($user['excel_yn'] =="Y"){ ?>
			<a href="/excel/db/dbTeamL?code=<?=$code?>" class="typeBtn btnGreen02" title="엑셀다운로드"><i class="fas fa-file-excel"></i>엑셀다운로드</a> 
			<?php } ?>
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<script type="text/javascript">
		$(function(){
			var datas = <?=json_encode($db_data)?>

			$(document).on("click",".popup_btns",function(){
				var data = $(this).attr("data-idx");
				var idx = "";
				$(".popupBox").find("iframe").attr("src","/sub/db/csLogL?idx="+data);
				$.each(datas, function(index, item){
					if(item['idx'] == data){
						var prev_idx = datas[index-1];
						var next_idx = datas[index+1];
						if(prev_idx || next_idx){
							if(prev_idx && data != prev_idx['idx']){
								$(".prev_btn").show();
								$(".prev_btn").attr('data-idx',prev_idx['idx']);
							}else{
								$(".prev_btn").hide();
							}
							if(next_idx && data != next_idx['idx']){
								$(".next_btn").show();
								$(".next_btn").attr('data-idx',next_idx['idx']);
							}else{
								$(".next_btn").hide();
							}
						}
					}
				})
				
			})
			
			$(".smsSendBtn").click(function(){
				var code = "<form method='post' action='/sub/sms/sendW' id='smsSendFrm'>";
				var item = $(".listDataCheck:checked");
				for(var i = 0; i < item.length; i++){
					code += '<input type="hidden" name="smsReceiveData[]" value="' + $(item[i]).attr("data-idx") + '">';
				}
				code += "</form>";
				$("body").append(code);
				$("#smsSendFrm").submit();
			});

			$(".dbDistBtn").click(function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("변경할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				popupControl("open", "dist", "/sub/db/dbDistChange", "DB 담당자변경", false, "선택한 DB의 담당자를 변경하실 수 있습니다.");
			});
			$(".dbCsStatusChange").click(function(event) {
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("변경할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				popupControl("open", "csStatus", "/sub/db/dbCsStatusChange", "DB 상담상태변경", false, "선택한 DB의 상담상태를 변경하실 수 있습니다.");

			});

			$(".dbGradeChange").click(function(event) {
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("변경할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				popupControl("open", "grade", "/sub/db/dbGradeChange", "DB 고객등급변경", false, "선택한 DB의 고객등급을 변경하실 수 있습니다.");

			});
			
			$(".dbCsAllBtn").click(function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("등록할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				popupControl("open", "csAll", "/sub/db/dbCsAll", "DB 상담일괄등록", false, "선택한 DB의 상담내역을 일괄등록하실 수 있습니다.");
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>