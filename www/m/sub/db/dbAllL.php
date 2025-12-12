<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/mida/db/config.php";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 콘텐츠설정
	$pageTitle = "DB통합관리";

	# 탭메뉴 설정
	$tabMenu = "db";
	$tabMenuList = [];
	array_push($tabMenuList, "DB통합관리@db@/db/dbAllL");
	array_push($tabMenuList, "DB분배관리@dbDist@/db/dbTeamL");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y' AND dist_code = '001'";

	# 초기 정렬
	$_GET['orderBy'] = ($_GET['orderBy']) ? $_GET['orderBy'] : "made_date DESC";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_db");

?>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="order_by_date DESC">기본정렬</option>
				<option value="made_date ASC">생산일시 오름차순</option>
				<option value="made_date DESC">생산일시 내림차순</option>
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
	
		<!-- 버튼 영역 -->
		<div class="dataBtnWrap">
			<button type="button" class="typeBtn btnMain dbChangeBtn" data-url="/m/sub/db/dbDistTM" data-name="DB분배"><i class="fas fa-plus-circle"></i>DB분배</button>
		</div>
		
		<!-- 목록영역 -->
		<div class="dataListWrap">
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT company_name FROM mt_member_cmpy WHERE idx = MT.pm_code ) AS pm_name
					FROM mt_db MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<ul onclick="www('/m/sub/db/dbAllV?idx=<?=$row['idx']?>');">
					<li class="stopProgram">
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</li>
					<li>
						<p>
							<span class="lp05"><?=($row['overlap_yn']=="Y") ? "<span style='color:#CD3333; font-weight: 500;'>중복</span>" : "미중복"?> <span style="margin: 0 5px;">|</span> <b>생산</b> [<?=date("Y-m-d", strtotime($row['made_date']))?>]</span>
						</p>
						<p style="font-size: 13px;">
							<span class="bold lp05">D-<?=$row['idx']?></span>
							<span class="line">|</span>
							<span class="bold label">생산업체</span>
							<span class="lp05"><?=($row['pm_name']) ? "PM{$row['pm_code']} ({$row['pm_name']})" : "-"?></span>
						</p>
						<p class="main">
							<span><?=$row['cs_name']?></span>
							<span class="lp05"><?=$row['cs_tel']?></span>
						</p>
					</li>
					<li><i class="fas fa-angle-right"></i></li>
				</ul>
			<?php } ?>
			
			<?php if(!$totalCnt){ ?>
				<div class="noData">조회된 데이터가 존재하지 않습니다.</div>
			<?php } ?>
		</div>
		
		<!-- 버튼 영역 -->
		<div class="dataBtnWrap">
			<button type="button" class="typeBtn btnMain dbChangeBtn" data-url="/m/sub/db/dbDistTM" data-name="DB분배"><i class="fas fa-plus-circle"></i>DB분배</button>
		</div>

		<!-- 페이징 -->
		<?=paging()?>
		
	</div>
	
	<script type="text/javascript">
		$(function(){

			$(".dbChangeBtn").click(function(){
				var item = $(".listDataCheck:checked");
				var url = $(this).data("url");
				var name = $(this).data("name");

				if(!item.length){
					alert("분배할 DB를 선택해주시길 바랍니다.");
					return false;
				}

				popupControl("open", "dist", url, name);
			});

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>