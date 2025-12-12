<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["004"];

	# 메뉴설정
	$secMenu = "myTeamMember";

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";
	
	# 콘텐츠설정
	$contentsTitle = "{$customLabel["fc"]} 관리";
	$contentsInfo = "{$customLabel["fc"]}(를)을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "{$customLabel["fc"]}관리");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND auth_code IN ( 005 ) AND tm_code = '{$user['tm_code']}'";

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
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="m_name">이름</option>
						<option value="m_id">아이디</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<input type="hidden" name="setDate" value="order_by">
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
			<a href="/sub/group/myTeamMemberW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i><?=$customLabel["fc"]?> 등록</a>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="16%">
				<col width="20%">
				<col width="16%">
				<col width="20%">
				<col width="10%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th colspan="4">계정정보</th>
					<th rowspan="2">사용여부</th>
					<th rowspan="2">등록일시</th>
				</tr>
				<tr>
					<th>아이디</th>
					<th>이름</th>
					<th>연락처</th>
					<th style="border-right: 1px solid #FFF;">이메일</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mt_member MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove" onclick="www('/sub/group/myTeamMemberV?idx=<?=$row['idx']?>');">
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=$row['m_id']?></td>
					<td><?=dhtml($row['m_name'])?></td>
					<td><?=($row['m_tel']) ? $row['m_tel'] : "-"?></td>
					<td><?=(dhtml($row['m_mail'])) ? dhtml($row['m_mail']) : "-"?></td>
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
			<a href="/sub/group/myTeamMemberW" class="typeBtn btnMain big"><i class="fas fa-plus-circle"></i><?=$customLabel["fc"]?> 등록</a>
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