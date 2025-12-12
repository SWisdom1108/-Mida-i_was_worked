<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002", "004", "005"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 데이터 정보추출

	$value = array(':idx'=>$_GET['idx']);
	$query ="
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = 'Y'
		AND idx = :idx
	";
	$view = view_pdo($query, $value);


	if(!$view){
		www("/sub/error/popup");
	}

	$checkTel = preg_replace("/[^0-9]*/s", "", $view['cs_tel']);

	$andQuery .= "WHERE replace(cs_tel, '-', '') = '{$checkTel}'";
	# 권한에 따른 추가 쿼리문
	switch($user['auth_code']){
		case "004" :
			$andQuery .= " AND tm_code = '{$user['tm_code']}'";
			break;
		case "005" :
			$andQuery .= " AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}'";
			break;
	}

	$orderBy = "idx desc";

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		AND list_yn = 'Y'
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);

	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['column_code'];
		
		$columnArr[$columnCnt] = $thisdatas;
	}
	$columnWidth = 59 / ($columnCnt + 2);

	# 메인번호 가져오기

	# 오늘일자
	$year = date("Y");
	$month = date("m");
	$day = date("d");

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_db");


?>
	
	<div class="dbCheckDataInfoWrap">
		<div class="titWrap">
			<div class="left">
				<i class="fas fa-arrow-circle-right"></i>
				D-<?=$view['idx']?>
			</div>
		</div>
		
		<div class="infoWrap">
			<ul>
				<li>
					<span class="label">이름</span>
					<span class="value"><?=$view['cs_name']?></span>
				</li>
				<li>
					<span class="label">연락처</span>
					<span class="value lp05"><?=$view['cs_tel']?></span>
				</li>
				<?php foreach($columnArr as $val){ ?>
					<li>
						<span class="label"><?=$val['name']?></span>
						<span class="value lp05"><?=($view["{$val['code']}"]) ? $view["{$val['code']}"] : "-"?></span>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<input type="hidden" name="db_idx" value="<?=$view['idx']?>">
			
							
			<div class="listWrap">
				<table>
					<colgroup>
						<col width="5%">
						<col width="7%">
						<col width="8%">
						<col width="8%">
						<col width="8%">
						<?php for($i = 0; $i < ($columnCnt + 2); $i++){ ?>
							<col width="<?=$columnWidth?>%">
						<?php } ?>
						<col width="8%">
						<col width="5%">
					</colgroup>
					<thead>
						<tr>
							<th rowspan="2">NO</th>
							<th rowspan="2">중복여부</th>
							<th rowspan="2">DB고유번호</th>
							<th rowspan="2">유입경로</th>
							<th rowspan="2">유입일자</th>
							<th colspan="<?=($columnCnt + 3)?>">DB정보</th>
							<th rowspan="2">상담</th>
						</tr>
						<tr>
							<th><?=$customLabel["cs_name"]?></th>
							<th><?=$customLabel["cs_tel"]?></th>
						<?php foreach($columnArr as $val){ ?>
							<th><?=$val['name']?></th>
						<?php } ?>
              <th>중복위치</th>
						</tr>
					</thead>
					<tbody>
						<?php 
							# 중복내역
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

							foreach ( $location_sql as $row ){
								$locationArr[$location['location_code']] = $location['location_name'];
							}

							while($row = $sql->fetch(PDO::FETCH_ASSOC)){

						?>
						<tr >
							<td class="lp05"><?=listNo()?></td>
							<td><?=($row['overlap_yn']=="Y") ? "<span style='color:#CD3333; font-weight: 500;'>중복</span>" : "미중복"?></td>
							<td class="lp05">D-<?=$row['idx']?></td>
							<td class="lp05" style="line-height: 15px;">
								<?php if($row['pm_name']){ ?>
									<span><?=$row["pm_name"]?></span>
									<br><span style="font-size: 12px; color: #AAA;">PM<?=$row["pm_code"]?></span>
								<?php } else { ?>
									<span>-</span>
								<?php } ?>
							</td>
							<td class="lp05">
								<?=date("Y-m-d", strtotime($row['made_date']))?>
								<br><span style="font-size: 12px; color: #AAA;"><?=date("H:i:s", strtotime($row['made_date']))?></span>
							</td>
							<td><?=($row['cs_name']) ? $row['cs_name'] : "-"?></td>
							<td class="lp05"><?=($row['cs_tel']) ? $row['cs_tel'] : "-"?></td>
							<?php foreach($columnArr as $val){ ?>
								<?php if($val['name'] == "지역"){ ?>
									<td class="lp05"><?=($row["{$val['code']}"]) ? $row["{$val['code']}"] : "-"?></td>
								<?php } else { ?>
									<td class="lp05"><?=($row["{$val['code']}"]) ? $row["{$val['code']}"] : "-"?></td>
								<?php } ?>
							<?php } ?>

							<!-- 251208 차현우 중복위치 작업 -->
              <?php if($row['overlap_yn']=="Y"){ ?>
                <?php if ($row['use_yn'] == "Y") { 

                  if ($row['m_idx'] == "") { ?>
                  <td class="lp05">분배전</td>
                  <?php } else { 
                  $value = array(':idx'=>$row['m_idx']);
                  $query ="SELECT * FROM mt_member WHERE use_yn = 'Y' AND idx = :idx";
                  $view = view_pdo($query, $value); ?>
                  <td class="lp05"><?=$view['m_name']?></td>
                  <?php }?>

                <?php } else { ?>
                <td class="lp05">휴지통</td>
                <?php }?>

              <?php } else { 
                if ($row['use_yn'] == "Y") { 
                  if ($row['m_idx'] == "") { ?>
                  <td class="lp05">분배전</td>
                  <?php } else { 
                  $value = array(':idx'=>$row['m_idx']);
                  $query ="SELECT * FROM mt_member WHERE use_yn = 'Y' AND idx = :idx";
                  $view = view_pdo($query, $value); ?>
                  <td class="lp05"><?=$view['m_name']?></td>
                  <?php }?>

                <?php } else { ?>
                  <td class="lp05">휴지통</td>
                <?php }?>
              <?php } ?>
              <!-- 251208 차현우 중복위치 작업 끝. -->
							
							<td>
								<i class="fas fa-headphones csBtn click dbCsBtn<?=$lastCsStatus?>" style="font-size: 16px;" onclick="window.open('/sub/db/overCslogL?idx=<?=$row['idx']?>', 'window_name_<?=$row['idx']?>', 'width=1200, height=900,location=no, top=100, left=100, status=no, scrollbars=yes')"></i>
							</td>
						</tr>
						<?php } ?>
						<?php if(!$totalCnt){ ?>
							<tr>
								<td colspan="<?=($columnCnt + 7)?>" class="no">조회된 데이터가 존재하지 않습니다.</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>

	<!-- 페이징 -->
	<?=paging()?>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="overL">닫기</button>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			var calculateCodeList = <?=json_encode($calculateList)?>;
			function csMemoPlaceholderSetting(){
				var memo = "상담내용입력";
				if(jQuery.inArray($("#statusCode").val(), calculateCodeList) >= 0){
					memo = "금액 또는 수량기재";
				}
				
				$("#csMemo").attr("placeholder", memo);
			}
			
			csMemoPlaceholderSetting();
			
			$("#statusCode").change(function(){
				csMemoPlaceholderSetting();
			});
			
			$("#csSubmitBtn").click(function(){
				var datas = new FormData($("form")[0]);
				
				if(!$("#statusCode").val() || !$("#csMemo").val()){
					alert("등록할 내용을 입력해주시길 바랍니다.");
					return false;
				}
				
				$("#loadingWrap").fadeIn(350, function(){
					$.ajax({
						url : "/ajax/db/csLogWP",
						type : "POST",
						data : datas,
						processData : false,
						contentType : false,
						success : function(result){
							switch(result){
								case "success" :
									window.location.reload();
									break;
								case "fail" :
									alert("알 수 없는 이유로 등록을 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default :
									alert(result);
									$("#loadingWrap").fadeOut(350);
									break;
							}
						}
					});
				});
			});
			
		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>