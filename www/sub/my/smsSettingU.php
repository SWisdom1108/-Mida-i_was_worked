<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "sms";
	$trdMenu = "setting";
	
	# 콘텐츠설정
	$contentsTitle = "SMS설정";
	$contentsInfo = "SMS에 관한 기본적인 내용을 설정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "SMS설정");
	array_push($contentsRoots, "SMS설정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 메인발신번호	
	$value = array(''=>'');
	$query = "SELECT * FROM mt_sms_tel WHERE use_yn = 'Y' AND main_yn = 'Y'";
	$mainTel = view_pdo($query, $value);

	# 템플릿정보
	$value = array(''=>'');
	$query = "SELECT * FROM mt_sms_template WHERE idx = '1'";
	$template01Info = view_pdo($query, $value);

	$value = array(''=>'');
	$query = "SELECT * FROM mt_sms_template WHERE idx = '2'";
	$template02Info = view_pdo($query, $value);

?>
	
	<div class="writeWrap">
		<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/my/smsSettingUP" data-callback="/sub/my/smsSettingV" data-type="수정">
			<div class="tit">기본정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>메인 발신번호</th>
						<td class="lp05">
							<select class="txtBox" name="mainTel">
								<option value="">선택안함</option>
							<?php
								$value = array(''=>'');
								$query = "SELECT * FROM mt_sms_tel WHERE use_yn ='Y' ORDER BY idx ASC";
								$sql = list_pdo($query, $value);
								while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							?>
								<option value="<?=$row["idx"]?>" <?=($row["idx"] == $mainTel["idx"]) ? "selected" : ""?>>
									<?=$row["sent_name"]?>(<?=$row["sent_tel"]?>)
								</option>
							<?php } ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<div class="tit">기본템플릿정보</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th>생산업체 DB유입시<br>관리자 전송</th>
						<td style="text-align: left;">
							<input type="checkbox" class="toggle" name="use_yn_01" id="use_yn_01" <?=($template01Info["use_yn"] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="use_yn_01"><div></div></label>
						</td>
						<th>담당자 DB분배시<br>담당자 전송</th>
						<td style="text-align: left;">
							<input type="checkbox" class="toggle" name="use_yn_02" id="use_yn_02" <?=($template02Info["use_yn"] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="use_yn_02"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="/sub/my/smsSettingV" class="typeBtn btnGray02" title="취소"><i class="fas fa-arrow-left"></i>취소</a> 
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack submitBtn" data-target="mod"><i class="far fa-check-circle"></i>완료</button>
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>