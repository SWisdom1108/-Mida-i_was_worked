<?php

	# 메뉴설정
	$secMenu = "block";
	$trdMenu = "ipblock";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001"];
	
	# 콘텐츠설정
	$contentsTitle = "접근차단IP";
	$contentsInfo = "접근 차단 IP를 설정 하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "접근차단IP");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 검색값 정리
	search();

	$andQuery .="AND block_yn = 'Y'";


	# 페이징 정리
	paging("mt_login_block_ip");
?>
	<div class="ipblock">
		<ul>
			<a href = "/sub/my/ipblockL"><li style="min-width:180px; float:left; border-top: 1px solid #ccc; border-left: 1px solid #ccc;text-align: center; padding: 10px 0px; color:#999; font-size:14px;">등록 IP 차단</li></a>
			<a href = "/sub/my/ipblockL2"><li class="active">로그인 실패 IP 차단</li></a>
			<div style="position : absolute;left : 180px;top: 1px; border: 1px solid #000;width: 180px;height: 40.5px;z-index: 2; border-bottom: 0;opacity: 0.2;pointer-events: none; top:0px"></div>
		</ul>
	</div>
<div class="ipbox">
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
				<col width="25%">
				<col width="20%">
				<col width="15%">
				<col width="20%">
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
					<th>차단된 IP</th>
					<th>차단 사유</th>
					<th>등록자</th>
					<th>등록자IP</th>
					<th>등록일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
						,(select m_name from mt_member where idx = MT.reg_idx) as m_name
					FROM mt_login_block_ip MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="rowMove" data-type="open" data-target="mod"  data-name="접근 차단 IP정보">
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=dhtml($row['login_ip'])?></td>
					<td class="lp05">연속적인 로그인 시도</td>
					<td class="lp05"><?=$row['m_name']?></td>
					<td class="lp05"><?=$row['reg_ip']?></td>
					<td class="lp05"><?=$row['reg_date']?></td>
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
			<button type="button" class="typeBtn btnGray02 big blackAllDeleteBtn">차단해제</button>
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="permit_ip">IP</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>
</div>

	<script type="text/javascript">
		$(function(){

			$(document).on("click", ".blackAllDeleteBtn", function(){

				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("차단을 해제할 IP를 선택해주시길 바랍니다.");
					return false;
				}
				
				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}
		
				idx = idx.join(",");

				if(confirm("해당 IP의 차단을 해제하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){

						$.ajax({
                        url : "/ajax/my/block2DP",
                        data : {
                            idx : idx
                        },
                        type : "POST",
                        success : function(result){
	                        	switch(result){
	                        		case "success" :
										alert("차단헤제하였습니다.");
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