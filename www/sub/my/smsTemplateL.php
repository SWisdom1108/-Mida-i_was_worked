<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 메뉴설정
	$secMenu = "sms";
	$trdMenu = "template";
	
	# 콘텐츠설정
	$contentsTitle = "템플릿관리";
	$contentsInfo = "SMS 전송 시 사용할 템플릿을 관리하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "SMS설정");
	array_push($contentsRoots, "템플릿관리");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND basic_yn = 'N'";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_sms_template");

	# 데이터 추출
	// $sql = list_sql("
	// 	SELECT MT.*
	// 	FROM mt_sms_template MT
	// 	{$andQuery}
	// 	{$orderQuery}
	// 	{$limitQuery}
	// ");

	$value = array(''=>'');
	$query = "
		SELECT MT.*
		FROM mt_sms_template MT
		{$andQuery}
		{$orderQuery}
		{$limitQuery}
	";

	$sql = list_pdo($query, $value);


?>

	<!-- 데이터 검색영역 -->
	<div class="searchWrap">
		<form method="get">
			<ul class="formWrap">
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="title">템플릿명</option>
						<option value="contents">템플릿내용</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<input type="hidden" name="setDate" value="reg">
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
				<option value="order_by_date DESC">기본정렬</option>
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
			<button type="button" class="typeBtn btnMain big popupBtn" data-type="open" data-target="write" data-url="/sub/my/smsTemplateW" data-name="템플릿등록"><i class="fas fa-plus-circle"></i>템플릿등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="smsTemplateWrap">
	<?php if(!$totalCnt){ ?>
		<ul class="no">
			<li>조회된 데이터가 존재하지 않습니다.</li>
		</ul>
	<?php } else { ?>
		<ul class="itemList">
		<?php while($row = $sql->fetch(PDO::FETCH_ASSOC)){?>
			<li>
				<input type="text" class="txtBox title" placeholder="템플릿명" value="<?=$row["title"]?>">
				<textarea class="txtBox contents" placeholder="템플릿내용"><?=$row["contents"]?></textarea>
				<p style="width: 100%; float: left; margin-bottom: 5px; text-align: right; font-size: 12px; letter-spacing: -0.5px;">0 Byte</p>
				<input type="checkbox" class="toggle use_yn" id="use_yn_<?=$row["idx"]?>" <?=($row["use_yn"] == "Y") ? "checked" : ""?>>
				<label class="toggle" for="use_yn_<?=$row["idx"]?>"><div></div></label>
				<button type="button" class="typeBtn btnBlack templateSubmitBtn" data-idx="<?=$row["idx"]?>">저장</button>
				<button type="button" class="typeBtn btnGray01 templateDeleteBtn" data-idx="<?=$row["idx"]?>">삭제</button>
			</li>
		<?php } ?>
		</ul>
	<?php } ?>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
			<button type="button" class="typeBtn btnMain big popupBtn" data-type="open" data-target="write" data-url="/sub/my/smsTemplateW" data-name="템플릿등록"><i class="fas fa-plus-circle"></i>템플릿등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<script type="text/javascript">
		$(function(){
			
			$(".templateSubmitBtn").click(function(){
				var idx = $(this).data("idx");
				var title = $(this).closest("li").find(".title").val();
				var contents = $(this).closest("li").find(".contents").val();
				var use_yn = ($("#use_yn_" + idx).prop("checked")) ? "Y" : "N";

				if ( !title ) {
					alert('필수 값을 입력해주시길 바랍니다.');
					return false;
				}

				loading(function(){
					$.ajax({
						url : "/ajax/my/smsTemplateUP",
						type : "POST",
						data : {
							idx : idx,
							title : title,
							contents : contents,
							use_yn : use_yn,
						},
						success : function(result){
							if(result == "success"){
								alert("저장이 완료되었습니다.");
							} else {
								alert("알 수 없는 이유로 저장을 실패하였습니다.");
							}
							
							loadingClose();
						}
					})
				});
			});
			
			$(".templateDeleteBtn").click(function(){
				var idx = $(this).data("idx");
				
				if(confirm("해당 템플릿을 삭제하시겠습니까?")){
					loading(function(){
						$.ajax({
							url : "/ajax/my/smsTemplateDP",
							type : "POST",
							data : {
								idx : idx
							},
							success : function(result){
								if(result == "success"){
									alert("삭제가 완료되었습니다.");
									window.location.reload();
								} else {
									alert("알 수 없는 이유로 삭제를 실패하였습니다.");
								}

								loadingClose();
							}
						})
					});
				}
			});
			
			var item = $(".contents");
			for(var i = 0; i < $(item).length; i++){
				$(item[i]).next().text(byteCheck($(item[i])) + " Byte");
			}
			
			$(document).on("focus focusin focusout change keyup keydown", ".contents", function(){
				$(this).next().text(byteCheck($(this)) + " Byte");
			});

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>