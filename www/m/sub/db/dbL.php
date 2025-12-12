<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 콘텐츠설정
	$pageTitle = "DB통합관리";

	# 탭메뉴 설정
	$tabMenu = "db";
	$tabMenuList = [];
	array_push($tabMenuList, "DB통합관리@db@/db/dbL");
	if($user['auth_code'] == "004"){
		array_push($tabMenuList, "DB분배관리@dbDist@/db/dbDistL");
	}

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y' AND dist_code = '002' AND tm_code = '{$user['tm_code']}'";
	if($user['auth_code'] == "005"){
		$andQuery .= " AND m_idx = '{$user['idx']}'";
	}

	# 권한에 따른 쿼리문
	switch($user['auth_code']){
		case "004" :
			# 데이터 간단정리표

			$value = array(''=>'');
			$query = "
				SELECT
						( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'Y' AND tm_code = '{$user['tm_code']}' AND dist_code = '002' ) AS totalCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'Y' AND tm_code = '{$user['tm_code']}' AND dist_code = '002' AND reg_date LIKE '".date("Y-m-d")."%' ) AS todayCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'Y' AND tm_code = '{$user['tm_code']}' AND dist_code = '002' AND dist_date LIKE '".date("Y-m-d")."%' ) AS todayDistCnt
				FROM mt_db
			";
			$dashboard = view_pdo($query, $value);
			break;
		case "005" :
			# 데이터 간단정리표
			$value = array(''=>'');
			$query = "
				SELECT
						( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'Y' AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}' AND dist_code = '002' ) AS totalCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'Y' AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}' AND dist_code = '002' AND reg_date LIKE '".date("Y-m-d")."%' ) AS todayCnt
					, ( SELECT COUNT(*) FROM mt_db WHERE use_yn = 'Y' AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}' AND dist_code = '002' AND dist_date LIKE '".date("Y-m-d")."%' ) AS todayDistCnt
				FROM dual
			";
			$dashboard = view_pdo($query, $value);

			break;
	}

	# 초기 정렬
	$_GET['orderBy'] = ($_GET['orderBy']) ? $_GET['orderBy'] : "order_by_date DESC";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_db");

?>

	<!-- 데이터 간단정리표 -->
	<div class="dataInfoSimpleWrap">
		<ul class="dataCntList">
			<li>
				<span class="cnt"><?=number_format($dashboard['totalCnt'])?></span>
				<span class="label">전체 DB</span>
			</li>
			<li>
				<span class="cnt"><?=number_format($dashboard['todayCnt'])?></span>
				<span class="label">오늘의 업로드 DB</span>
			</li>
			<li>
				<span class="cnt"><?=number_format($dashboard['todayDistCnt'])?></span>
				<span class="label">오늘의 분배 DB</span>
			</li>
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
			<select class="txtBox" name="label">
				<option value="cs_name"><?=$customLabel["cs_name"]?></option>
				<option value="cs_tel"><?=$customLabel["cs_tel"]?></option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit">검색</button>
		</form>
	</div>
	
	<?php if($user["auth_code"] == "004"){ ?>
		<!-- 버튼 영역 -->
		<div class="dataBtnWrap">
			<button type="button" class="typeBtn btnMain dbChangeBtn" data-url="/m/sub/db/dbMyTeamChange" data-name="DB분배"><i class="fas fa-plus-circle"></i>DB분배</button>
		</div>
	<?php } ?>
	
	<div class="dataSectionWrap">
		
		<!-- 목록영역 -->
		<div class="dataListWrap">
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
						, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS fc_name
					FROM mt_db MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<ul onclick="www('/m/sub/db/dbV?idx=<?=$row['idx']?>');">
				<?php if($user["auth_code"] == "004"){ ?>
					<li class="stopProgram">
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</li>
				<?php } else { ?>
					<li class="stopProgram" style="width: 0;">
					</li>
				<?php } ?>
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
		
		<?php if($user["auth_code"] == "004"){ ?>
			<!-- 버튼 영역 -->
			<div class="dataBtnWrap">
				<button type="button" class="typeBtn btnMain dbChangeBtn" data-url="/m/sub/db/dbMyTeamChange" data-name="DB분배"><i class="fas fa-plus-circle"></i>DB분배</button>
			</div>
		<?php } ?>

		<!-- 페이징 -->
		<?=paging()?>
		
	</div>
	
	<script type="text/javascript">
		$(function(){

			<?php if($user["auth_code"] == "004"){ ?>
				$(".dbChangeBtn").click(function(){
					var item = $(".listDataCheck:checked");
					var url = $(this).data("url");
					var name = $(this).data("name");

					if(!item.length){
						alert("변경할 DB를 선택해주시길 바랍니다.");
						return false;
					}

					popupControl("open", "dist", url, name);
				});
			<?php } ?>

			$(".csLogBtn.click").click(function(e){
				e.preventDefault();
				e.stopPropagation();
				
				var idx = $(this).data("idx");

				popupControl("open", "csLog", "/m/sub/db/csLogL?idx=" + idx, "DB 상담기록");
			});

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>