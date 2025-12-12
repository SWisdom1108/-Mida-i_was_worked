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
	
	<div class="viewWrap">
		<div class="tit">기본정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>메인 발신번호</th>
					<td class="lp05"><?=($mainTel) ? "{$mainTel["sent_name"]}({$mainTel["sent_tel"]})" : "-"?></td>
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
					<td style="font-weight: bold; color: #<?=($template01Info["use_yn"] == "Y") ? "333" : "CCC"?>;">
						<?=($template01Info["use_yn"] == "Y") ? "사용중" : "미사용"?>
					</td>
					<th>담당자 DB분배시<br>담당자 전송</th>
					<td style="font-weight: bold; color: #<?=($template02Info["use_yn"] == "Y") ? "333" : "CCC"?>;">
						<?=($template02Info["use_yn"] == "Y") ? "사용중" : "미사용"?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<a href="/sub/my/smsSettingU" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		</div>
	</div>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>