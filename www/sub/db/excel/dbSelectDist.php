<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	# 컬럼 정리
	$columnCnt = 0;
	$columnArr = [];
	$value = array(':use_yn'=>'Y');
	$query = "
		SELECT *
		FROM mt_db_cs_info
		WHERE use_yn = :use_yn
		ORDER BY sort ASC
	";
	$columnData = list_pdo($query, $value);
	while($row = $columnData->fetch(PDO::FETCH_ASSOC)){
		$columnCnt++;
		
		$thisdatas = [];
		$thisdatas['name'] = $row['column_name'];
		$thisdatas['code'] = $row['idx'];
		$thisdatas['use_yn'] = $row['use_yn'];
		
		$columnArr[$columnCnt] = $thisdatas;
	}
	$columnWidth = 70 / ($columnCnt + 2);

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<div class="tit">엑셀항목</div>			
			<table>
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
					<tr>
						<th>전체선택</th>
						<td colspan="3">
							<input class="toggle2" type="checkbox" id="excelDownYnAllCheck" checked="">
							<label class="toggle2" for="excelDownYnAllCheck"><div></div></label>
						</td>
					</tr>
				</tbody>
			</table>


			<table style="margin-top :30px; border-top: 2px solid #efefef;">
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="20%">
					<col width="30%">
				</colgroup>
				<tbody>
				<?php foreach($columnArr as $index => $val){ ?>
					<tr>
						<th><?=$val['name']?></th>
						<td colspan="3">
							<input type="checkbox" class="toggle2 excelDownYn" value="<?=$val['code']?>" data-column="<?=$val['code']?>" id="<?=$val['code']?>" <?=($val['use_yn'] == "Y") ? "checked" : "";?>>
							<label class="toggle2" for="<?=$val['code']?>"><div></div></label>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</form>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" onclick="location.href='/excel/db/dbDistL?code=<?=$code?>'" class="typeBtn btnGreen01"><i class="fas fa-file-excel"></i>엑셀다운로드</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="excel">취소</button>
	</div>
	
	<script type="text/javascript">
		$(function(){

			// excelDownYnAllCheck
			$("#excelDownYnAllCheck").change(function(){
				$(".excelDownYn").prop("checked", $(this).prop("checked"));
				
				var item = $(".excelDownYn:checked");
				var column = [];
				for(var i = 0; i < item.length; i++){
					column.push($(item[i]).data("column"));
				}		
		
				column.join(",");
		
				setCookie("excelYnColumn", column);
			});

			$(".excelDownYn").change(function(e){
				e.stopPropagation();
				
				var itemLen = $(".excelDownYn").length;
				var checkLen = $(".excelDownYn:checked").length;
				
				var item = $(".excelDownYn:checked");
				var column = [];
				for(var i = 0; i < item.length; i++){
					column.push($(item[i]).data("column"));
				}
		
				column.join(",");
		
				setCookie("excelYnColumn", column);

				if(itemLen == checkLen){
					$("#excelDownYnAllCheck").prop("checked", true);
				} else {
					$("#excelDownYnAllCheck").prop("checked", false);
				}
		
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>