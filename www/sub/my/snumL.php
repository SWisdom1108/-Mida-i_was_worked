<?php

	# 메뉴설정
	$secMenu = "block";
	$trdMenu = "snum";

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001"];
	
	# 콘텐츠설정
	$contentsTitle = "보안카드 사용목록";
	$contentsInfo = "보안카드를 발급 및 사용중인 회원 목록을 확인 가능합니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "보안카드 목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	$andQuery .= " AND (snum_use_yn = 'Y' OR (SELECT count(*) FROM mt_member_snum WHERE m_idx = MT.idx) > 0)";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_member MT");

?>
	


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
				<col width="10%">
				<col width="10%">
				<col width="10%">
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
					<th>이름(ID)</th>
					<th>권한</th>
					<th>사용여부</th>
					<th>등록일시</th>
					<th>최종수정일시</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT *, (SELECT auth_name FROM mc_member_auth WHERE auth_code = MT.auth_code) as auth_name FROM mt_member MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="">
					<td>
						<input type="checkbox" class="listDataCheck" id="listDataCheck_<?=$row['idx']?>" data-idx="<?=$row['idx']?>">
						<label class="ch" for="listDataCheck_<?=$row['idx']?>">
							<i class="fas fa-check-square on"></i>
							<i class="far fa-square off"></i>
						</label>
					</td>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05"><?=dhtml($row['m_name'])?>(<?=$row['m_id']?>)</td>
					<td class="lp05"><?=dhtml($row['auth_name'])?></td>
					<td class="lp05"><?=($row['snum_use_yn'] == 'Y')? '<span style="color : #000; font-weight : 900" >사용중</span>': '<span style="color : #cc3333; font-weight : 900" >미사용(발급만 진행)</span>'?></td>
					<td class="lp05"><?=$row['reg_date']?></td>
					<td class="lp05"><?=($row['edit_date'])? $row['edit_date']: "-"?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="7">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnGray02 big snumDel">카드 초기화</button>
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="m_name">이름</option>
				<option value="m_id">아이디</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

	<script type="text/javascript">
		$(function(){

			$(".snumDel").click(function(){
				var item = $(".listDataCheck:checked");

				if(!item.length){
					alert("카드 초기화 할 계정을 선택해주시길 바랍니다.");
					return false;
				}
				
				var idx = [];
				for(var i = 0; i < item.length; i++){
					idx.push($(item[i]).data("idx"));
				}

				idx = idx.join(",");

				if(confirm("선택된 계정의 보안카드를 초기화하시겠습니까?")){
					$("#loadingWrap").fadeIn(350, function(){
						$.ajax({
							url : "/ajax/group/snumDP",
							data : {
								idx : idx
							},
							type : "POST",
							success : function(result){
								alert("보안카드 초기화가 완료되었습니다.");
								window.location.reload();
							}
						})
					});
				}
			});

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>