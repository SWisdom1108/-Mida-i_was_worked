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

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/header.php";

	# 데이터 정보추출
	$value = array(':use_yn'=>'Y',':dist_code'=>'002',':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
			, ( SELECT team_name FROM mt_member_team WHERE idx = MT.tm_code ) AS tm_name
			, ( SELECT m_name FROM mt_member WHERE idx = MT.m_idx ) AS fc_name
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND dist_code = :dist_code
		AND idx = :idx
	";
	$view = view_pdo($query, $value);


	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		ORDER BY idx ASC
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

?>
	
	<div class="dataSectionWrap">
	
		<div class="dataViewWrap">
			<div class="titWrap"><i class="fas fa-arrow-circle-down"></i>DB 상세보기</div>
			
			<div class="codeInfoWrap">
				D-<?=$view['idx']?>
			</div>
			
			<div class="infoWrap">
				<ul>
					<li>
						<span class="label"><?=$customLabel["cs_name"]?></span>
						<span class="value"><?=$view['cs_name']?></span>
					</li>
					<li>
						<span class="label"><?=$customLabel["cs_tel"]?></span>
						<span class="value lp05"><?=($view['cs_tel']) ? $view['cs_tel'] : "-"?></span>
					</li>
				<?php foreach($columnArr as $index => $val){ ?>
					<li>
						<span class="label"><?=$val['name']?></span>
						<span class="value lp05"><?php
						if($val['type'] == "file"){
							if($view["{$val['code']}"]){
								$value = explode( '@#@#', $view["{$val['code']}"] );
								echo "<a href='/upload/db_etc/{$value[0]}' class='db_csdwon' download='{$value[1]}'>{$value[1]}<i class=\"fas fa-download\"></i></a>";	
							}else{
								echo "-";
							}
						} else{
							echo ($view["{$val['code']}"]) ? $view["{$val['code']}"] : "-";
						}?></span>
					</li>
				<?php } ?>
					<li class="line"></li>
					<li>
						<span class="label">담당팀</span>
						<span class="value lp05"><?=($view['tm_name']) ? "TM{$view['tm_code']}({$view['tm_name']})" : "-"?></span>
					</li>
					<li>
						<span class="label">담당자</span>
						<span class="value lp05"><?=($view['fc_name']) ? "FC{$view['m_idx']}({$view['fc_name']})" : "-"?></span>
					</li>
				</ul>
			</div>
		</div>
		
		<form id="setFrm">
			<input type="hidden" name="idx[]" value="<?=$view['idx']?>">
		</form>
		
		<div class="dataViewBtnWrap">
			<a href="<?=$_SESSION['prevURL']?>" class="typeBtn btnGray02">이전</a>
		</div>
		
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/footer.php"; ?>