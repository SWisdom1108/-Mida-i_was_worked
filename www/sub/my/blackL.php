<?php

	# 메뉴설정
	$secMenu = "block";
	$trdMenu = "black";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001"];
	
	# 콘텐츠설정
	$contentsTitle = "블랙리스트";
	$contentsInfo = "블랙리스트를 설정 하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "블랙리스트");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 210529 전화번호차단목록
	$value = array(''=>'');
	$query = "SELECT * FROM mt_block_tel WHERE use_yn = 'Y' ORDER BY idx ASC";
	$telBlockList = list_pdo($query, $value);

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_block_tel");

?>

<style type="text/css">
	#blockTelAddWrap { width: 100%; float: left; border: 1px solid #CCC; padding: 10px; }
	#blockTelAddWrap > #blockTel { width: calc(100% - 140px) !important; float: left; letter-spacing: -0.5px; }
	#blockTelAddWrap > #blockTelDate { width: 150px !important; float: left; margin-left: 10px; letter-spacing: -0.5px; }
	#blockTelAddWrap > button { width: 120px !important; float: left; margin-left: 10px; font-weight: bold; color: #FFF; background-color: #333; font-size: 13px; }
	#blockTelListWrap { width: 100%; float: left; }
	#blockTelListWrap > li { width: 100%; float: left; margin-top: 5px; border: 1px solid #EEE; padding: 10px 15px; }
	#blockTelListWrap > li > span { letter-spacing: 0; font-size: 13px; }
	#blockTelListWrap > li > .label { float: left; }
	#blockTelListWrap > li > .value { float: right; color: #AAA; }
	#blockTelListWrap > li > .value > i { color: #DC3333; cursor: pointer; margin-left: 10px; }
</style>
		
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">블랙리스트 등록</span>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="writeWrap" style="margin-bottom: 50px;">
		<form action="/ajax/my/blackUP.php" id="blackTelFrm" method="post">
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="label">블랙리스트 연락처</th>
						<td class="value">
							<div id="blockTelAddWrap">
								<input class="txtBox" type="text" id="blockTel" name="blockTel" numberonly placeholder="차단할 전화번호 입력 ex)01012345678">
								<!-- <button type="button" class="typeBtn" id="blockTelAddBtn">추가</button> -->
								<button type="button" class="typeBtn blackTelBtn">추가</button>
							</div>
							<ul id="blockTelListWrap"></ul>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>

<!-- 	<div class="dataBtnWrap" style="margin-bottom: 50px;">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnGray01 big blackTelBtn">등록</button>
		</div>
	</div> -->

	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
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

	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="6%">
				<col width="20%">
				<col width="20%">
				<col width="25%">
				<col width="25%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">
						<input type="checkbox" id="listDataAllCheck">
						<label class="ch" for="listDataAllCheck">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</th>
					<th>NO</th>
					<th>연락처</th>
					<th>등록자</th>
					<th>등록아이피</th>
					<th>등록일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT m_name FROM mt_member WHERE MT.reg_id = idx ) AS m_name
					FROM mt_block_tel MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr>
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=$row['block_tel']?></td>
					<td class="lp05"><?=$row['m_name']?></td>
					<td class="lp05"><?=$row['reg_ip']?></td>
					<td class="lp05"><?=$row['reg_date']?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="6">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnGray02 big blackAllDeleteBtn">차단해제</button>
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="block_tel">연락처</option>
				<option value="reg_ip">등록아이피</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

	<script type="text/javascript">
		$(function(){
			/* 전화번호차단 */
			$("#blockTelAddBtn").click(function(){
				var val = $("#blockTel").val();
				var date = $("#blockTelDate").val();
				
				if(!val){
					alert("전화번호를 입력해주시길 바랍니다.");
					return false;
				}
				
				var code = '<li>';
				code += '<input type="hidden" name="blockTel[]" value="' + val + '">';
				code += '<span class="label">- ' + val + '</span>';
				code += '<span class="value"><i class="fas fa-minus-circle blockTelDeleteBtn"></i></span>';
				code += '</li>';
				
				$("#blockTel").val("");
				$("#blockTelListWrap").append(code);
			});

			$(document).on("click", ".blockTelDeleteBtn", function(){
				$(this).closest("li").remove();
			});

			$(document).on("click", ".blackTelBtn", function(){

				var data = $("#blockTel").val();

				if(confirm("해당 연락처를 블랙리스트에 추가하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){

						$.ajax({
                        url : "/ajax/my/blackUP",
                        data : {
                            data : data
                        },
                        type : "POST",
                        success : function(result){
	                        	switch(result){
	                        		case "success" :
										alert("블랙리스트에 추가하였습니다.");
										loadingClose();
										window.location.reload();
										break;
									case "fail" :
										alert("알수없는 오류로 블랙리스트에 추가를 실패하였습니다.");
										loadingClose();
										break;
									default :
										alert(result);
										loadingClose();
										break;
                        		}
                            
                        	}
                   		})
						
					});
				}
			})

			$(document).on("click", ".blackAllDeleteBtn", function(){

				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("차단해제 할 연락처를 선택해주시길 바랍니다.");
					return false;
				}
				
				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}
		
				idx = idx.join(",");

				if(confirm("해당 연락처를 차단 해제하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){

						$.ajax({
                        url : "/ajax/my/blackDP",
                        data : {
                            idx : idx
                        },
                        type : "POST",
                        success : function(result){
	                        	switch(result){
	                        		case "success" :
										alert("차단해제하였습니다.");
										loadingClose();
										window.location.reload();
										break;
									case "fail" :
										alert("알수없는 오류로 차단해제를 실패하였습니다.");
										loadingClose();
										break;
									default :
										alert(result);
										loadingClose();
										break;
                        		}
                            
                        	}
                   		})
						
					});
				}
			})

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>