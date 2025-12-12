<?php

	# 메뉴설정
	$secMenu = "team";
	$trdMenu = "teamMember";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";
	
	# 콘텐츠설정
	$contentsTitle = "{$customLabel["fc"]} 관리";
	$contentsInfo = "{$customLabel["fc"]}(를)을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "사용자관리");
	array_push($contentsRoots, "{$customLabel["fc"]}관리");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND auth_code IN ( 004, 005 )";

	if($_GET["teamCode"]){
		$andQuery .= " AND tm_code = '{$_GET['teamCode']}'";
	}

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_member");

?>

	<!-- 데이터 검색영역 -->
	<div class="searchWrap">
		<form method="get">
			<ul class="formWrap">
				<li>
					<span class="label"><?=$customLabel["tm"]?></span>
					<select class="txtBox" name="teamCode" id="teamCode">
						<option value=""><?=$customLabel["tm"]?> 선택</option>
					<?php
						$value = array(''=>'');
						$query = "SELECT * FROM mt_member_team WHERE use_yn = 'Y' ORDER BY idx DESC";
						$sql = list_pdo($query, $value);
						while($row = $sql->fetch(PDO::FETCH_ASSOC)){
					?>
						<option value="<?=$row['idx']?>"><?=dhtml($row['team_name'])?></option>
					<?php } ?>
					</select>
				</li>
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="m_name">이름</option>
						<option value="m_id">아이디</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<input type="hidden" name="setDate" value="reg">
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
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
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
			<a href="/sub/group/teamMemberW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i><?=$customLabel["fc"]?> 등록</a>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="16%">
				<col width="10%">
				<col width="23%">
				<col width="23%">
				<col width="10%">
				<col width="10%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th rowspan="2"><?=$customLabel["tm"]?>명</th>
					<th colspan="3">계정정보</th>
					<th rowspan="2">엑셀다운로드사용여부</th>
					<th rowspan="2">사용여부</th>
					<th rowspan="2">등록일시</th>
				</tr>
				<tr>
					<th>코드</th>
					<th>아이디</th>
					<th>이름</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT team_name FROM mt_member_team WHERE MT.tm_code = idx ) AS team_name
						, ( SELECT m_idx FROM mt_member_team WHERE MT.tm_code = idx ) AS mg_idx
					FROM mt_member MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove" onclick="www('/sub/group/teamMemberV?idx=<?=$row['idx']?>');">
					<td class="lp05"><?=listNo()?></td>
					<td><?=dhtml($row['team_name'])?></td>
					<td class="lp05">FC<?=$row["idx"]?></td>
					<td class="lp05">
						<?=($row['mg_idx'] == $row['idx']) ? "<i class='fas fa-crown' style='color: #DC3333; margin-right: 5px;'></i>" : "<i class='fas fa-crown' style='color: #DDD; margin-right: 5px;'></i>"?>
						<?=dhtml($row['m_id'])?>
					</td>
					<td><?=dhtml($row['m_name']);?></td>
					<td class="stopProgram">
						<input type="checkbox" class="toggle2 changeExcelYn" value="<?=$row['idx']?>" id="excel_yn<?=$row['idx']?>" <?=($row['excel_yn'] == "Y") ? "checked" : "";?>>
						<label class="toggle2" for="excel_yn<?=$row['idx']?>"><div></div></label>
					</td>
					<td><?=($row['use_yn'] == "Y") ? "사용중" : "<span style='color: #CCC;'>사용안함</span>"?></td>
					<td class="lp05"><?=date("Y-m-d", strtotime($row['reg_date']))?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="10" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/group/teamMemberW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i><?=$customLabel["fc"]?> 등록</a>
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<script type="text/javascript">
		$(function(){
			
			var teamCode = "<?=$_GET['teamCode']?>";
			if(teamCode){
				$("#teamCode").val(teamCode);
			}
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>