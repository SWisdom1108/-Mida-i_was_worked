<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	// paging("mt_db_cs_info_detail");
?>
<style>
	.writeWrap th { text-align:center !important; }
	.writeWrap td { text-align:center !important; }
</style>
<div class="writeWrap">
	<form enctype="multipart/form-data" id="updateFrm" data-ajax="/ajax/db_setting/columnDetailUP" data-callback="close" data-type="수정">
		<input type="hidden" name="info_idx" value="<?=$_GET['idx']?>">
		<!-- <div class="tit">고객등급구분값 정보</div> -->
		<table text-align="center">
			<colgroup>
				<col width="10%">
				<col width="70%">
				<col width="10%">
				<col width="10%">
			</colgroup>
			<thead>
				<tr>
					<th>No</th>
					<th>항목명</th>
					<th>순서</th>
					<th>기능</th>
				</tr>
			</thead>
			<tbody>
				<?php
					// $result = list_sql($sql);
					$maxSort = 0;
					$value = array(':info_idx'=>$_GET['idx'],':use_yn'=>'Y');
					$query = "
						SELECT *
						FROM mt_db_cs_info_detail
						WHERE info_idx = :info_idx
						AND use_yn = :use_yn
						ORDER BY sort ASC
					";
					$sql = list_pdo($query, $value);

					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$maxSort = $row['sort'];
				?>
				<tr>
					<!-- <td><?=listNo()?></td> -->
					<?php /*<td><?=listNo()?></td>*/ ?>
					<td><?=$row['sort']?></td>
					<td>
						<input type="hidden" name="u_idxs[]" value="<?=$row['idx']?>">
						<input type="text" class="txtBox" name="u_name<?=$row['idx']?>" value="<?=$row['info_val']?>">
					</td>
					<td class="sortTd">
						<span><?=$row['sort']?></span>
						<input type="hidden" class="sortInp" name="u_sort<?=$row['idx']?>" value="<?=$row['sort']?>">
						<i class="fas fa-sort fa-lg click"></i>
					</td>
					<td>
						<input type="hidden" value="N" name="delYn<?=$row['idx']?>">
						<i class="fas fa-trash-alt fa-lg trashBtn click" data-type='u' data-val='Y'></i>
						<i class="fas fa-undo fa-lg click trashBtn dpn" data-type='u' data-val='N'></i>
					</td>
				</tr>
				<?php } $maxSort++; ?>
				<?php if($maxSort == 1){ ?>
				<tr class="nodata">
					<td colspan="3">
						<span>입력된 항목이 없습니다</span>
					</td>
				</tr>
				<?php } ?>
				<tr class="Wtr" style="background: #F8F8F8;">
					<td style="border-top:solid 1px #333; border-bottom: none;">
						<span style="font-weight: bold; color:#333;">항목추가</span>
					</td>
					<td style="border-top:solid 1px #333; border-bottom: none;" colspan="2">
						<input type="hidden" name="w_idxs[]" value="1">
						<input type="text" class="txtBox w_name1" name="w_name1" />
					</td>
					<td style="border-top:solid 1px #333; border-bottom: none;">
						<i class="fas fa-plus-circle fa-lg click addFrmBtn" style="color:#333"></i>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<div id="popupBtnWrap">
	<button type="button" class="typeBtn btnBlack submitBtn" data-target="update">수정</button>
	<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="update">취소</button>
</div>

<script>

function sortSet(){
	$(".sortInp").each(function(index, el) {
		var sort = index+1;
		$(el).val(sort);
		$(el).siblings('span').text(sort);
	});
}
$(function(){

	$('input[type="text"]').keydown(function() {
		if (event.keyCode === 13) {
			event.preventDefault();
			$(".addFrmBtn").trigger("click");
		};
	});

	var writeNum = 1;

	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};

	$(document).on("click", ".trashBtn", function(){
		var type = $(this).attr("data-type");

		switch(type){
			case "w":
				$(this).closest('tr').remove();
				var sort = $(".writeWrap tbody tr").length;
				if(sort == 1 ){
					var html = `
					<tr class="nodata">
						<td colspan="3">
							<span>입력된 항목이 없습니다</span>
						</td>
					</tr>
					`;
					$(".writeWrap tbody .Wtr").before(html);
				}
				break;
			case "u":
				$(this).hide();
				$(this).siblings("i").show();
				$(this).siblings("input").val($(this).attr("data-val"));
				break;
		}
		sortSet();
	})

	

	$(".addFrmBtn").click(function(event) {
		$(".nodata").remove();
		writeNum++;
		var sort = $(".writeWrap tbody tr").length;
		var value = $(".w_name1").val();
		value = value.split('"').join('&#34;');
		value = value.split("'").join('&#39;');

		var html = `
			<tr>
				<td></td>
				<td>
					<input type="hidden" name="w_idxs[]" value="${writeNum}">
					<input type="text" class="txtBox" name="w_name${writeNum}" value="${value}">
				</td>
				<td class="sortTd">
					<span>${sort}</span>
					<input type="hidden" class="sortInp" name="w_sort${writeNum}" value="${sort}">
					<i class="fas fa-sort fa-lg click"></i>
				</td>
				<td>
					<i class="fas fa-trash-alt fa-lg trashBtn click" data-type='w'></i>
				</td>
			</tr>
		`;
		// $(".writeWrap tbody").append(html);
		$(".writeWrap tbody .Wtr").before(html);
		 $(".w_name1").val('');
	});

	
	
	$(".writeWrap tbody").sortable({
		handle : ".sortTd"
		, start: function(){
		}
		, update : function(e, ui){
			sortSet();

		}
		, helper: fixHelper
	})

})
</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>