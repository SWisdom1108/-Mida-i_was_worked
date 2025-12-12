<?php

	# 메뉴설정
	$secMenu = "pm";
	$trdMenu = "pm";
	
	# 콘텐츠설정
	$contentsTitle = "생산업체 정보";
	$contentsInfo = "DB를 공급할 생산업체를 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "생산업체관리");
	array_push($contentsRoots, "정보");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 데이터 추출
	$value = array(':idx'=>$_GET['idx']);
	$query = "
		SELECT MT.*
			, ( SELECT bank_name FROM mc_bank WHERE MT.bank_code = bank_code ) AS bank_name
			, ( SELECT category_name FROM mc_member_cmpy_category WHERE MT.depth1 = category_code ) AS depth1_name
			, ( SELECT category_name FROM mc_member_cmpy_category WHERE MT.depth2 = category_code ) AS depth2_name
			, ( SELECT category_name FROM mc_member_cmpy_category WHERE MT.depth3 = category_code ) AS depth3_name
			, ( SELECT category_name FROM mc_member_cmpy_category WHERE MT.depth4 = category_code ) AS depth4_name
			, ( SELECT category_name FROM mc_member_cmpy_category WHERE MT.depth5 = category_code ) AS depth5_name
		FROM mt_member_cmpy MT
		WHERE use_yn = 'Y'
		AND auth_code = '003'
		AND idx = :idx
	";
	$view = view_pdo($query, $value);

	if(!$view){
		include_once "{$_SERVER['DOCUMENT_ROOT']}/sub/error/index.php";
		return false;
	}

	$value = array(':idx'=>$view['m_idx']);
	$query = "
		SELECT MT.*
		FROM mt_member MT
		WHERE use_yn = 'Y'
		AND auth_code = '003'
		AND idx = :idx
	";
	$viewUser = view_pdo($query, $value);

	# API정보
	$value = array(':code_idx'=>$view['idx']);
	$query = "SELECT * FROM mt_api WHERE use_yn = 'Y' AND auth_code = 'pm' AND code_idx = :code_idx";
	$api = view_pdo($query, $value);

	# 컬럼 정리
	$columnDataCon = '';
	$value = array(''=>'');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = 'Y'
		ORDER BY idx ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnDataCon .= ($columnDataCon) ? "," : "";
		$columnDataCon .= ' "'.$row["column_name"].'" : "'.$row["column_ex"].'" ';
	}

?>
	
	<div class="viewWrap">
		<div class="tit"><i class="fas fa-user-circle"></i>계정정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>분배 후 DB 미노출 여부</th>
					<td><?=($view['hidden_yn'] == "Y") ? "미노출" : "<span style='color: #CCC;'>노출</span>"?></td>
				</tr>
				<tr>
					<th>아이디</th>
					<td class="lp05"><?=$viewUser['m_id']?></td>
				</tr>
				<tr>
				<tr>
					<th>이름</th>
					<td><?=dhtml($viewUser['m_name'])?></td>
				</tr>
				<tr>
					<th>연락처</th>
					<td class="lp05"><?=dhtml($viewUser['m_tel'])?></td>
				</tr>
				<tr>
					<th>이메일</th>
					<td class="lp05"><?=dhtml($viewUser['m_mail'])?></td>
				</tr>
				<tr>
					<th>주소</th>
					<td><?=dhtml($viewUser['m_addr'])?></td>
				</tr>
			</tbody>
		</table>

		<div class="tit">카테고리정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>카테고리 1</th>
					<td><?=$view['depth1_name']?></td>
				</tr>
				<tr>
					<th>카테고리 2</th>
					<td><?=$view['depth2_name']?></td>
				</tr>
				<tr>
					<th>카테고리 3</th>
					<td><?=$view['depth3_name']?></td>
				</tr>
				<tr>
					<th>카테고리 4</th>
					<td><?=$view['depth4_name']?></td>
				</tr>
				<tr>
					<th>카테고리 5</th>
					<td><?=$view['depth5_name']?></td>
				</tr>
			</tbody>
		</table>
	
		<div class="tit">회사 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<tr>
					<th>사업자명</th>
					<td><?=dhtml($view['company_name'])?></td>
					<th>사업자번호</th>
					<td class="lp05"><?=dhtml($view['company_num'])?></td>
				</tr>
				<tr>
					<th>사업장 연락처</th>
					<td class="lp05"><?=dhtml($view['company_tel'])?></td>
					<th>사업장 팩스</th>
					<td class="lp05"><?=dhtml($view['company_fax'])?></td>
				</tr>
				<tr>
					<th>사업장 이메일</th>
					<td colspan="3" class="lp05"><?=dhtml($view['company_mail'])?></td>
				</tr>
				<tr>
					<th>업태</th>
					<td><?=dhtml($view['company_type'])?></td>
					<th>업종</th>
					<td><?=dhtml($view['company_sec'])?></td>
				</tr>
				<tr>
					<th>사업장 주소</th>
					<td colspan="3"><?=dhtml($view['company_addr'])?></td>
				</tr>
				<tr>
					<th>세금계산서처리용 이메일</th>
					<td colspan="3" class="lp05"><?=dhtml($view['tax_mail'])?></td>
				</tr>
			</tbody>
		</table>

		<div class="tit">대표자 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<tr>
					<th>대표자명</th>
					<td colspan="3"><?=dhtml($view['ceo_name'])?></td>
				</tr>
				<tr>
					<th>대표자 연락처</th>
					<td class="lp05"><?=dhtml($view['ceo_tel'])?></td>
					<th>대표자 이메일</th>
					<td class="lp05"><?=dhtml($view['ceo_mail'])?></td>
				</tr>
			</tbody>
		</table>

		<div class="tit">계좌 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<tr>
					<th>은행명</th>
					<td><?=dhtml($view['bank_name'])?></td>
					<th>예금주명</th>
					<td><?=dhtml($view['bank_holder'])?></td>
				</tr>
				<tr>
					<th>계좌번호</th>
					<td colspan="3" class="lp05"><?=dhtml($view['bank_num'])?></td>
				</tr>
			</tbody>
		</table>
		
		<div class="tit">API 정보<div class="miniGuideWrap" data-class="groupPM_API"></div></div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<?php if($api){ ?>
					<tr>
						<th>API KEY값</th>
						<td colspan="3" class="lp05"><?=$api["api_key"]?></td>
					</tr>
					<tr>
						<th>API 코드</th>
						<td colspan="3" class="lp05">
							<pre>
$.ajax({
	url : "<?=($_SERVER["HTTPS"] == "on") ? "https" : "http"?>://<?=$_SERVER["HTTP_HOST"]?>/api/requestPMDB",
	type : "POST",
	data : {
		apiKey : "<?=$api["api_key"]?>", /* API KEY */
		csName : "홍길동", /* 고객명 (필수) */
		csTel : "010-1234-5678", /* 고객연락처 (필수) */
		etc : {<?=$columnDataCon?>}, /* 기타항목 (배열) */
	}
});</pre>
						</td>
					</tr>
				<?php } else { ?>
					<tr>
						<th>API 발급받기</th>
						<td colspan="3">
							<button type="button" class="typeBtn btnOrange getAPIBtn"><i class="fas fa-clipboard-check"></i>API 발급</button>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

		<div class="tit">상세 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<tr>
					<th>비고</th>
					<td colspan="3"><?=dhtml($view['memo'])?></td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<a href="<?=$_SESSION['prevURL']?>" class="typeBtn btnGray02" title="이전"><i class="fas fa-arrow-left"></i>이전</a> 
		</div>
		<div class="right">
			<a href="/sub/group/pmU?idx=<?=$view['idx']?>" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		</div>
	</div>
	
	<?php if(!$api){ ?>
		<script type="text/javascript">
			$(function(){
				
				$(".getAPIBtn").click(function(){
					$("#loadingWrap").fadeIn(350, function(){
						$.ajax({
							url : "/ajax/api/pm",
							type : "POST",
							data : {
								code : "<?=$view['idx']?>"
							},
							success : function(){
								window.location.reload();
							}
						})
					});
				});
				
			})
		</script>
	<?php } ?>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>