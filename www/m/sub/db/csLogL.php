<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/m/include/headerPopup.php";

	# 데이터 정보추출
	$value = array(':use_yn'=>'Y',':dist_code'=>'002',':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
		FROM mt_db MT
		WHERE use_yn = :use_yn
		AND dist_code = :dist_code
		AND idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		www("/sub/error/popup");
	}

	# 상담기록
	$sql = "
		SELECT MT.*
			, ( SELECT status_name FROM mc_db_cs_status WHERE MT.status_code = status_code ) AS status_name
			, ( SELECT m_name FROM mt_member WHERE MT.reg_idx = idx ) AS m_name
			, ( SELECT m_id FROM mt_member WHERE MT.reg_idx = idx ) AS m_id
		FROM mt_db_cs_log MT
		WHERE use_yn = 'Y'
		AND db_idx = '{$view['idx']}'
		ORDER BY idx DESC
	";
	$cs = list_sql($sql);
	$totalCnt = cnt_sql($sql);

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

<style>
	#statusCode{min-width:0px; width:50%;}
	#gradeCode{min-width:0px; width:45%; float: right;}
</style>

	<div class="dbCheckDataInfoWrap">
		<div class="titWrap">
			<span class="point"><i class="fas fa-arrow-circle-right"></i>D-<?=$view['idx']?></span>
			<?=($view['product_name']) ? " / {$view['product_name']}" : ""?>
		</div>
		
		<div class="infoWrap">
			<ul>
				<li>
					<span class="label"><?=$customLabel["cs_name"]?></span>
					<span class="value"><?=$view['cs_name']?></span>
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
						}
						?></span>
					</li>
				<?php } ?>
				<li>
					<span class="label"><?=$customLabel["cs_tel"]?></span>
					<span class="value lp05"><?=$view['cs_tel']?></span>
					<a href="tel: <?=$view['cs_tel']?>"><i class="fas fa-phone"></i></a>
				</li>
			</ul>
		</div>
	</div>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<input type="hidden" name="db_idx" value="<?=$view['idx']?>">
			
			<ul class="listMiniWriteWrap">
				<li style="width: 100%;">
					<select class="txtBox" name="status_code" id="statusCode">
					<?php
						$sql = list_sql("
							SELECT MT.*
							FROM mc_db_cs_status MT
							WHERE use_yn = 'Y'
							AND sms_yn = 'N'
							ORDER BY sort ASC
						");
						foreach ( $sql as $row ){
							$lastCsStatus = view_sql("SELECT status_code FROM mt_db_cs_log WHERE use_yn = 'Y' AND db_idx = '{$view['idx']}' ORDER BY idx DESC")['status_code'];
					?>
						<option value="<?=$row['status_code']?>" <?=($row['status_code']==$lastCsStatus) ? "selected" : ""?>><?=$row['status_name']?></option>
					<?php } ?>
					</select>
					<select name="grade_code" class="txtBox" id="gradeCode">
						<option value="">선택안함</option>
						<?php 
						$grades = list_sql("SELECT * FROM mc_db_grade_info WHERE use_yn = 'Y' AND del_yn = 'N' ORDER BY grade_code DESC");
						foreach ( $grades as $row ){?>
							<option value="<?=$row["grade_code"]?>" <?=($row["grade_code"] == $view['grade_code'] ) ? "selected" : ""?>><?=$row["grade_name"]?></option>
						<?php } ?>
					</select>
				</li>
				<li style="width: 100%;">
					<input type="file" class="getFileName" id="csFile" data-target="cs" name="file">
					<label for="csFile" class="typeBtn btnGray01" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%; padding: 0 5px; font-size: 13px; font-weight: 500;">
						<i class="fas fa-save"></i>
						<span id="csFileName" class="lp05">첨부파일업로드</span>
					</label>
				</li>
				<li style="width: 75%;">
					<input type="text" class="txtBox" name="memo" id="csMemo" placeholder="상담내용">
				</li>
				<li style="width: 25%;">
					<button type="button" class="typeBtn" style="width: 95%; margin-left: 5%;" id="csSubmitBtn">등록</button>
				</li>
			</ul>
						
			<div class="miniListWrap">
				<?php foreach ( $cs as $row ){ ?>
					<ul>
						<li class="top">
							<div class="left">
								<?=date("Y-m-d H:i", strtotime($row['reg_date']))?>
							</div>
							<div class="right">
								<?=$row['m_name']?>(<?=$row['m_id']?>)
							</div>
						</li>
						<li class="con">
							<b><?=$row['status_name']?></b>
							<?=dhtml($row['memo'])?>
							<?php if($row['filename']){ ?>
								<a href="/sub/down/csFile?idx=<?=$row['idx']?>" title="<?=$row['filename_r']?>" target="_blank"><i class="fas fa-download click"></i><?=$row['filename_r']?></a>
							<?php } ?>
						</li>
					</ul>
				<?php } ?>
				
				<?php if(!$totalCnt){ ?>
					<div class="noData">등록된 상담기록이 존재하지 않습니다.</div>
				<?php } ?>
			</div>
		</form>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
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