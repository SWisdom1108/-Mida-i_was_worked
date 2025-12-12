<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 콘텐츠설정
	$pageTitle = "DB분배관리";

	# 탭메뉴 설정
	$tabMenu = "dbDist";
	$tabMenuList = [];
	array_push($tabMenuList, "DB통합관리@db@/db/dbAllL");
	array_push($tabMenuList, "DB분배관리@dbDist@/db/dbTeamL");

	$code = ($_GET['code']) ? $_GET['code'] : view_sql("SELECT idx FROM mt_member_team WHERE use_yn = 'Y' AND auth_code = '004' ORDER BY idx DESC LIMIT 0, 1")['idx'];
	$codeInfo = view_sql("SELECT * FROM mt_member_team WHERE use_yn = 'Y' AND idx = '{$code}' AND auth_code = '004'");
	$codeInfo['name'] = $codeInfo['team_name'];
	
	$codeSql = list_sql("SELECT * FROM mt_member_team WHERE use_yn = 'Y' AND auth_code = '004' ORDER BY idx DESC");
	$codeNameType = 'team_name';

	if(!$codeInfo['idx']){
		www("/m/sub/db/dbAllL");
	}

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y' AND dist_code = '002' AND tm_code = '{$code}'";

	# 초기 정렬
	$_GET['orderBy'] = ($_GET['orderBy']) ? $_GET['orderBy'] : "order_by_date DESC";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_db");

?>
	
	<!-- 코드목록 -->
	<div class="distCodeListWrap">
		<ul>
		<?php foreach ( $codeSql as $row ){ ?>
			<?php $class = ($code == $row['idx']) ? "active" : ""; ?>
			<li class="<?=$class?>"><a href="/m/sub/db/dbTeamL?code=<?=$row['idx']?>"><?=$row[$codeNameType]?></a></li>
		<?php } ?>
		</ul>
	</div>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="order_by_date DESC">기본정렬</option>
				<option value="reg_date ASC">등록일시 오름차순</option>
				<option value="reg_date DESC">등록일시 내림차순</option>
			</select>
			<button type="button" class="searchControlBtn <?=($_GET['value']) ? "active" : ""?>" onclick="$('.searchWrap').toggle(); $('.searchControlBtn').toggleClass('active');"><i class="fas fa-search"></i></button>
		</div>
	</div>
	
	<!-- 검색영역 -->
	<div class="searchWrap" style="display: <?=($_GET['value']) ? "block" : "none"?>;">
		<form method="get">
			<input type="hidden" name="code" value="<?=$code?>">
			<select class="txtBox" name="label">
				<option value="cs_name"><?=$customLabel["cs_name"]?></option>
				<option value="cs_tel"><?=$customLabel["cs_tel"]?></option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit">검색</button>
		</form>
	</div>
	
	<div class="dataSectionWrap">
		
		<!-- 목록영역 -->
		<div class="dataListWrap">
			<?php
				$sql = list_sql("
					SELECT MT.*
						, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.tm_code ) AS tm_name
						, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS fc_name
					FROM mt_db MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				");
				foreach ( $sql as $row ){
					
					# 상담버튼 활성화
					$csClass = ($row['check_code'] == "002" || $row['check_code'] == "006") ? "click" : "none";
					
			?>
				<ul onclick="www('/m/sub/db/dbTeamV?idx=<?=$row['idx']?>');">
					<li class="stopProgram" style="width: 0; padding-left: 10px;"></li>
					<li>
						<p>
							<span class="lp05"><b>분배</b> [<?=date("Y-m-d", strtotime($row['dist_date']))?>]</span>
						</p>
						<p style="font-size: 13px;">
							<span class="bold lp05">D-<?=$row['idx']?></span>
							<span class="line">|</span>
							<span class="bold label">담당팀</span>
							<span class="lp05"><?=($row['tm_name']) ? "TM{$row['tm_code']}" : "-"?></span>
							<span class="line">|</span>
							<span class="bold label">담당자</span>
							<span class="lp05"><?=($row['fc_name']) ? "FC{$row['m_idx']}" : "-"?></span>
						</p>
						<p class="main">
							<span><?=$row['cs_name']?></span>
							<span class="lp05"><?=$row['cs_tel']?></span>
						</p>
						<a href="#" class="csLogBtn click" data-idx="<?=$row['idx']?>"><i class="fas fa-headphones"></i></a>
					</li>
					<li><i class="fas fa-angle-right"></i></li>
				</ul>
			<?php } ?>
			
			<?php if(!$totalCnt){ ?>
				<div class="noData">조회된 데이터가 존재하지 않습니다.</div>
			<?php } ?>
		</div>

		<!-- 페이징 -->
		<?=paging()?>
		
	</div>
	
	<script type="text/javascript">
		$(function(){

			$(".csLogBtn.click").click(function(e){
				e.preventDefault();
				e.stopPropagation();
				
				var idx = $(this).data("idx");

				popupControl("open", "csLog", "/m/sub/db/csLogL?idx=" + idx, "DB 상담기록");
			});

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>