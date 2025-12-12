<?php

	# 메뉴설정
	$secMenu = "block";
	$trdMenu = "permit";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001"];
	
	# 콘텐츠설정
	$contentsTitle = "접근허용IP";
	$contentsInfo = "접근 허용 IP를 설정 하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "접근허용IP");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_permit_ip");

?>
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">접근 허용 IP 등록</span>
			<?php 
				$all_permit = "SELECT all_permit_yn FROM mt_permit WHERE idx = 1";
				$value = array(''=>'');
				$query = $all_permit;
				$allPermitYN = view_pdo($query, $value)['all_permit_yn'];
				$value = array(''=>'');
				$query = "SELECT count(*) as cnt FROM mt_permit_ip";
				$permitIPCnt = view_pdo($query, $value)['cnt'];
			?>
			<input type='checkbox' class='toggle use_yn' name='all_permit' id='all_permit' data-idx='1' <?=($allPermitYN == 'Y')? "checked" : ""?> <?=($permitIPCnt == 0)? "disabled" : ""?>>
			<label class='toggle' for='all_permit' style="margin-left: 10px;"><div></div></label>
			<br>
			<span class="cnt"><i class='fa fa-exclamation-circle'></i> 1개 이상의 IP를 추가하신 후, 접근 허용 IP 등록 옆 버튼(토글)을 눌러주세요.</span>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="writeWrap" style="margin-bottom: 50px;">
		<form action="/ajax/my/permitWP.php" id="permitIpFrm" method="post">
			<table style="width: 90%;">
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th class="label">허용 IP</th>
						<td class="value">
							<input class="txtBox" type="text" id="blockTel" name="blockTel" placeholder="접근을 허용할 IP 입력 ex)110.11.11.111" maxlength="45">
							<ul id="blockTelListWrap"></ul>
						</td>
					</tr>
					<tr>
						<th class="label">IP 이름</th>
						<td class="value">
							<input class="txtBox" type="text" id="ipName" name="ipName" placeholder="접근을 허용할 IP 이름입력 ex)홍길동" maxlength="20">
						</td>
					</tr>
						<button type="button" class="typeBtn permitIpBtn" style="width: 156px; height: 114px; float: right;">추가</button>
				</tbody>
			</table>
		</form>
	</div>

<!-- 	<div class="dataBtnWrap" style="margin-bottom: 50px;">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnGray01 big permitIpBtn">등록</button>
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
				<col width="10%">
				<col width="20%">
				<col width="10%">
				<col width="10%">
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
					<th>IP</th>
					<th>IP이름</th>
					<th>등록자</th>
					<th>등록자IP</th>
					<th>등록일시</th>
					<th>최종수정일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						, ( SELECT m_name FROM mt_member WHERE MT.reg_id = idx ) AS m_name
					FROM mt_permit_ip MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove popupBtn2" data-type="open" data-target="mod" data-url="/sub/my/permitU?idx=<?=$row['idx']?>" data-name="접근 허용 IP정보">
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=dhtml($row['permit_ip'])?></td>
					<td class="lp05"><?=dhtml($row['ip_name'])?></td>
					<td class="lp05"><?=$row['m_name']?></td>
					<td class="lp05"><?=$row['reg_ip']?></td>
					<td class="lp05"><?=$row['reg_date']?></td>
					<td class="lp05"><?=($row['edit_date'])? $row['edit_date']: "-"?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="8">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnGray02 big blackAllDeleteBtn">허용해제</button>
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="permit_ip">IP</option>
				<option value="ip_name">IP이름</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

	<script type="text/javascript">
		$(function(){

			$('input#all_permit').click(function(){
				var idxs =  $(this).data("idx");

				if(confirm("전체 허용여부를 변경하시겠습니까?")){
					$.ajax({
						url : "/ajax/my/allpermitU.php", 
						type : "POST",
						data : { idxs : idxs }, 
						success : function(result){
							switch(result){
								case "success" :
									alert("변경이 완료되었습니다.");
									window.location.reload();
									break; 
								case "fail" :
									break; 
								default :
									alert(result);
									break; 
							}
						},
						error : function(){
						}
					})
				}
			})

			$(document).on("click", ".blockTelDeleteBtn", function(){
				$(this).closest("li").remove();
			});

			$(document).on("click", ".permitIpBtn", function(){

				var data = $("#blockTel").val();
				var ipName = $("#ipName").val();
				var allpermitYN = $("#all_permit").is(":checked");

				if(confirm("해당 IP를 추가하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){

						$.ajax({
                        url : "/ajax/my/permitWP",
                        data : {
                            data : data,
							ipName : ipName
                        },
                        type : "POST",
                        success : function(result){
	                        	switch(result){
	                        		case "success" :
										alert("허용 IP를 추가하였습니다.");
										loadingClose();
										window.location.reload();
										break;
									case "fail" :
										alert("알수없는 오류로 허용 IP 추가를 실패하였습니다.");
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
					alert("허용을 해제할 IP를 선택해주시길 바랍니다.");
					return false;
				}
				
				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}
		
				idx = idx.join(",");

				if(confirm("해당 IP의 허용을 해제하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){

						$.ajax({
                        url : "/ajax/my/permitDP",
                        data : {
                            idx : idx
                        },
                        type : "POST",
                        success : function(result){
	                        	switch(result){
	                        		case "success" :
										alert("허용헤제하였습니다.");
										loadingClose();
										window.location.reload();
										break;
									case "fail" :
										alert("알수없는 오류로 허용해제를 실패하였습니다.");
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