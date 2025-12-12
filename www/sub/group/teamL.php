<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 메뉴설정
	$secMenu = "team";
	$trdMenu = "team";
	
	# 콘텐츠설정
	$contentsTitle = "{$customLabel["tm"]} 관리";
	$contentsInfo = "{$customLabel["tm"]}(를)을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "사용자관리");
	array_push($contentsRoots, "{$customLabel["tm"]}관리");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND auth_code = '004'";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_member_team");

?>
	
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
			<button type="button" class="typeBtn btnMain big" onclick="popupControl('open', 'write', '/sub/group/teamW', '<?=$customLabel["tm"]?> 등록');"><i class="fas fa-plus-circle"></i><?=$customLabel["tm"]?> 등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="13%">
				<col width="33%">
				<col width="13%">
				<col width="13%">
				<col width="12%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th colspan="5"><?=$customLabel["tm"]?> 정보</th>
					<th rowspan="2">등록일시</th>
				</tr>
				<tr>
					<th><?=$customLabel["tm"]?>코드</th>
					<th><?=$customLabel["tm"]?>명</th>
					<th>담당자</th>
					<th>인원 수</th>
					<th style="border-right: 1px solid #FFF;">사용여부</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT m_id FROM mt_member WHERE idx = MT.m_idx AND use_yn = 'Y' ) AS m_id
						, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx AND use_yn = 'Y' ) AS m_name
						, ( SELECT COUNT(*) FROM mt_member WHERE tm_code = MT.idx ) AS m_cnt
					FROM mt_member_team MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove" onclick="popupControl('open', 'mod', '/sub/group/teamU?idx=<?=$row['idx']?>', '<?=$customLabel["tm"]?> 수정');">
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05">TM<?=$row['idx']?></td>
					<td><?=dhtml($row['team_name'])?></td>
					<td><?=($row['m_name']) ? "{$row['m_name']}({$row['m_id']})" : "-"?></td>
					<td class="lp05"><?=number_format($row['m_cnt'])?>명</td>
					<td><?=($row['use_yn'] == "Y") ? "사용중" : "<span style='color: #CCC;'>사용안함</span>"?></td>
					<td class="lp05"><?=date("Y-m-d", strtotime($row['reg_date']))?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="7" class="no">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big" onclick="popupControl('open', 'write', '/sub/group/teamW', '<?=$customLabel["tm"]?> 등록');"><i class="fas fa-plus-circle"></i><?=$customLabel["tm"]?> 등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="team_name"><?=$customLabel["tm"]?>명</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>