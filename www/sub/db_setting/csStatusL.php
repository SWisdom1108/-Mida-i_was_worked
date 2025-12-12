<?php

	# 메뉴설정
	$secMenu = "csStatus";
	
	# 콘텐츠설정
	$contentsTitle = "DB상담구분값 설정";
	$contentsInfo = "DB상담 시 상세기록을 위한 구분값을 설정하여 관리가 가능합니다.<br>상태별 구분값을 추가하여 사용하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "상담구분값설정");
	array_push($contentsRoots, "목록");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	// $orderBy = "sort asc";

	# 초기 정렬
	$_GET['orderBy'] = ($_GET['orderBy']) ? $_GET['orderBy'] : "sort ASC";

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mc_db_cs_status");

?>
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="sort ASC">순서 오름차순</option>
				<option value="sort DESC">순서 내림차순</option>
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
			<button type="button" class="typeBtn btnMain big" onclick='popupControl("open", "write", "/sub/db_setting/csStatusW", "DB상담구분값 등록하기")'><i class="fas fa-plus-circle"></i>상담구분값 등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="36%">
				<col width="10%">
				<col width="10%">
				<col width="10%">
				<col width="13%">
				<col width="13%">
				<col width="4%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>상태값명 (상담구분코드)</th>
					<th>정산사용여부</th>
					<th>상담완료여부</th>
					<th>사용여부</th>
					<th>최초등록일시</th>
					<th>최종수정일시</th>
					<th>순서</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mc_db_cs_status MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
					# 사용여부 색상지정
					$useColor = ($row['use_yn'] == "Y") ? "666" : "CCC";
					
					# 상담완료여부 색상지정
					$finishColor = ($row['finish_yn'] == "Y") ? "666" : "CCC";
					
					# 숫자전용여부 색상지정
					$numberColor = ($row['number_yn'] == "Y") ? "666" : "CCC";			
				?>
				<tr class="rowMove" onclick='popupControl("open", "mod", "/sub/db_setting/csStatusU?code=<?=$row['status_code']?>", "DB상담구분값 수정하기")'>
					<td class="lp05"><?=listNo()?></td>
					<td class="lp05" style="font-weight: bold; color: <?=$row["color"]?>;"><?=dhtml($row['status_name'])?> (<?=dhtml($row['status_code'])?>)</td>
					<td style="color: #<?=$numberColor?>;"><?=($row['number_yn'] == "Y") ? "사용중" : "미사용"?></td>
					<td style="color: #<?=$finishColor?>;"><?=($row['finish_yn'] == "Y") ? "완료값" : "기본값"?></td>
					<td style="color: #<?=$useColor?>;"><?=($row['use_yn'] == "Y") ? "사용중" : "미사용"?></td>
					<td class="lp05"><?=$row['reg_date']?></td>
					<td class="lp05"><?=($row['edit_date']) ? $row['edit_date'] : "-"?></td>
					<td class="e stopProgram">
						<span style="padding-right: 10px; font-size: 14px;"><?=$row["sort"]?></span>
						<input type="hidden" class="sort" value="<?=$row["sort"]?>" data-idx="<?=$row['status_code']?>">
						<i class="fas fa-list-ol"></i>
					</td>
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
			<button type="button" class="typeBtn btnMain big" onclick='popupControl("open", "write", "/sub/db_setting/csStatusW", "DB상담구분값 등록하기")'><i class="fas fa-plus-circle"></i>상담구분값 등록</button>
		</div>
		<div class="right">
		</div>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<!-- 데이터 검색 -->
	<div class="simpleSearchWrap">
		<form method="get">
			<select class="txtBox" name="label">
				<option value="status_name">구분값명</option>
			</select>
			<input type="text" class="txtBox" name="value" value="<?=$_GET['value']?>">
			<button type="submit" class="typeBtn">검색</button>
		</form>
	</div>

	<script type="text/javascript">
	$(function(){
		var fixHelper = function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};
		$(".listWrap tbody").sortable({
			handle : ".stopProgram"
			, start: function(){
			}
			, update : function(e, ui){
				var datas = [];
				$(".sort").each(function(index, el) {
					var sort = index+1;
					$(el).val(sort);
					$(el).siblings('span').text(sort);
				});

				$(".sort").each(function(index, el) {
					datas.push($(el).attr("data-idx")+"||"+$(el).val());
				});
				
				$.post('/ajax/my/productSort', { sorts : datas }, function(result) {
					alert(result);
					window.location.reload();
				});

			}
			, helper: fixHelper
		}).disableSelection();
	})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>