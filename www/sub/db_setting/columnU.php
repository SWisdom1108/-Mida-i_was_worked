<?php

	# 메뉴설정
	$secMenu = "column";
	
	# 콘텐츠설정
	$contentsTitle = "DB관리항목 설정";
	$contentsInfo = "DB관리를 위한 상세항목을 설정하실 수 있습니다.<br>설정된 항목은 설정값에 따라 엑셀양식이 자동으로 생성되며, DB관리 목록에서 원하는 항목을 설정하여 보실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "DB관리항목");
	array_push($contentsRoots, "설정");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 데이터 설정
	$value = array(''=>'');
	$query = "SELECT COUNT(*) AS cnt FROM mt_db_cs_info";
	$csCnt = view_pdo($query, $value)['cnt'];
	if(!$csCnt){
		for($i = 1; $i < 11; $i++){
			$etcNum = ($i < 10) ? "0{$i}" : $i;
			excute("
				INSERT INTO mt_db_cs_info
					( column_code, reg_idx, reg_ip, use_yn )
				VALUES
					( 'cs_etc{$etcNum}', '{$proc_id}', '{$proc_ip}', 'N' )
			");
		}
	}

	$value = array(''=>'');
	$query = "SELECT * FROM mt_db_cs_info ORDER BY sort asc";
	$sql = list_pdo($query, $value);
	$totalCnt = 0;
?>
	
	<form enctype="multipart/form-data" id="modFrm" data-ajax="/ajax/my/columnUP" data-callback="/sub/db_setting/columnU" data-type="저장" class="prFormListWrap">
		<div class="writeWrap" style="margin-bottom: 50px;">
			<div class="tit">중복검사 설정<div class="miniGuideWrap" data-class="columnOverlap"></div></div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				
				<tbody>
					<tr>
						<th>사용여부</th>
						<td>
							<input type="checkbox" class="toggle use_yn" name="overlay" id="overlay" <?=($site['overlap_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="overlay"><div></div></label>
						</td>
					</tr>
					<tr>
						<th>기간설정</th>
						<td><input type="text" class="txtBox cntControler" name="overlap_days" value="<?=$site['overlap_days']?>"></td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="listWrap">
			<div class="tit">항목 설정 (고정)</div>
			<table>
				<colgroup>
					<col width="4%">
					<col width="36%">
					<col width="36%">
					<col width="8%">
					<col width="8%">
					<col width="8%">
				</colgroup>
				<thead>
					<tr>
						<th>NO</th>
						<th>항목명</th>
						<th>가이드설정<div class="miniGuideWrap" data-class="columnEx"></div></th>
						<th>사용여부</th>
						<th><i class="fas fa-check" style="color: #CC3333; margin-right: 5px;"></i> 목록노출여부<div class="miniGuideWrap typeRight" data-class="columnList"></div></th>
						<th>순서</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td class="tl"><?=$customLabel["cs_name"]?></td>
						<td class="tl">홍길동</td>
						<td style="color: #666;">기본값</td>
						<td style="color: #666;"><i class="fas fa-check" style="color: #CC3333; margin-right: 5px;"></i>기본값</td>
						<td>고정</td>
					</tr>
					<tr>
						<td>2</td>
						<td class="tl"><?=$customLabel["cs_tel"]?></td>
						<td class="tl lp05">010-1234-5678</td>
						<td style="color: #666;">기본값</td>
						<td style="color: #666;"><i class="fas fa-check" style="color: #CC3333; margin-right: 5px;"></i>기본값</td>
						<td>고정</td>
					</tr>
				</tbody>
			</table>

			<div class="listWrap" style="margin-top: 50px;">
			<div class="tit">항목 설정 (가변)</div>
			<table style="border-top: 1px solid #e3e3e3;">
				<colgroup>
					<col width="4%">
					<col width="18%">
					<col width="18%">

					<col width="16%">
					<col width="16%">

					<col width="10%">
					<col width="10%">
					<col width="8%">
				</colgroup>
				<thead>
					<tr>
						<th>NO</th>
						<th>항목명</th>
						<th>가이드설정<div class="miniGuideWrap" data-class="columnEx"></div></th>
						<th>미리보기<div class="windoGuide"><i class="fas fa-exclamation-circle guideBtn"></i></div></th>
						<th>상세값설정</th>

						<th>사용여부</th>
						<th><i class="fas fa-check" style="color: #CC3333; margin-right: 5px;"></i> 목록노출여부<div class="miniGuideWrap typeRight" data-class="columnList"></div></th>
						<th>순서</th>
					</tr>
				</thead>
				<tbody>
				<?php while($row = $sql->fetch(PDO::FETCH_ASSOC)){ $totalCnt++; ?>
					<tr data-idx= "<?=$row['idx']?>">
						<td class="lp05"><?=$totalCnt?><input type="hidden" name="idx[]" value="<?=$row['idx']?>"></td>
						<td><input type="text" class="txtBox name_<?=$row['idx']?>"  name="name_<?=$row['idx']?>" value="<?=dhtml($row['column_name'])?>" placeholder="항목명"></td>
						<td><input type="text" class="txtBox" name="ex_<?=$row['idx']?>" value="<?=dhtml($row['column_ex'])?>" placeholder="항목예시"></td>
						<td>
							<select class="txtBox column_type" style="width:100%;" data-target="mod" data-idx="<?=$row['idx']?>" name="column_type_<?=$row['idx']?>" id="column_type_<?=$row['idx']?>">
								<option value="text" <?=($row['column_type'] == "text")? "selected" : ""?>>단문입력</option>
								<option value="textarea" <?=($row['column_type'] == "textarea")? "selected" : ""?>>장문입력</option>
								<option value="select" <?=($row['column_type'] == "select")? "selected" : ""?>>선택박스</option>
								<option value="radio" <?=($row['column_type'] == "radio")? "selected" : ""?>>단일선택</option>
								<option value="checkbox" <?=($row['column_type'] == "checkbox")? "selected" : ""?>>다중선택</option>
								<option value="datepicker" <?=($row['column_type'] == "datepicker")? "selected" : ""?>>날짜선택</option>
								<option value="number" <?=($row['column_type'] == "number")? "selected" : ""?>>숫자입력</option>
								<option value="file" <?=($row['column_type'] == "file")? "selected" : ""?>>첨부파일</option>
							</select>
						</td>
						<td class="Detail_<?=$row['idx']?>">
							<?php if( $row['column_type'] == "select" || $row['column_type'] == "radio" || $row['column_type'] == "checkbox"  ){ ?>
								<i class="fas fa-edit fa-lg click" onclick='popupControl2("open", "update", "/sub/db_setting/columnDetailU?idx=<?=$row['idx']?>", "<?=$row['column_name']?> 상세항목 수정")'></i>
							<?php }else{ ?>
								<span>상세값을 설정할수없는 항목입니다.</span>
							<?php } ?>
						</td>
						<td>
							<input type="checkbox" class="toggle use_yn" name="use_yn_<?=$row['idx']?>" id="use_yn_<?=$row['idx']?>" <?=($row['use_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="use_yn_<?=$row['idx']?>" style="display: inline-block; float: none; top: 1.5px;"><div></div></label>
						</td>
						<td>
							<input type="checkbox" class="toggle list_yn" name="list_yn_<?=$row['idx']?>" id="list_yn_<?=$row['idx']?>" <?=($row['list_yn'] == "Y") ? "checked" : ""?>>
							<label class="toggle" for="list_yn_<?=$row['idx']?>" style="display: inline-block; float: none; top: 1.5px;"><div></div></label>
						</td>
						<td class="e stopProgram">
							<span style="padding-right: 10px; font-size: 14px;"><?=$row["sort"]?></span>
							<input type="hidden" class="sort" value="<?=$row["sort"]?>" data-idx="<?=$row['idx']?>">
							<i class="fas fa-list-ol"></i>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>


		</div>
	</form>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<button type="button" class="typeBtn btnBlack big submitBtn" data-target="mod"><i class="far fa-check-circle"></i>저장하기</button>
		</div>
	</div>
	
	<script type="text/javascript">
		$(function(){
			$(".column_type").change(function(){
				var data = $(this).val();
				var idx = $(this).data("idx");
				var name = $(".name_"+idx).val();
				// alert(data);
				if(data == "select" || data == "radio" || data == "checkbox"){
					var html = '<i class="fas fa-edit fa-lg click column_click" data-idx="'+idx+'" data-name="'+name+'"></i>';				
					$(".Detail_"+idx).html(html);
				}else{
					var html = '<span>상세값을 설정할수없는 항목입니다.</span>';
					$(".Detail_"+idx).html(html);
				}

			var target = $(this).data("target");
			var el = $("#" + target + "Frm")[0];
			var url = $(el).data("ajax");
			var callback = $(el).attr("data-callback");
			var type = $(el).data("type");
			
			var important = $(el).find(".important");
			for(var i = 0; i < important.length; i++){
				var val = $(important[i]).next("td").find("input");
				if(!val.length){
					val = $(important[i]).next("td").find("select");
				}

				if(!val.length){
					val = $(important[i]).next("td").find("textarea");
				}
				
				if(!val.val()){
					alert("필수 값을 입력해주시길 바랍니다.");
					val.focus();
					return false;
				}
			}
			
			if($("#edit01").length > 0){
				o.getById["edit01"].exec("UPDATE_CONTENTS_FIELD", []);
			}
			var data = new FormData(el);
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : url,
					type : "POST",
					processData : false,
					contentType : false,
					data : data,
					success : function(result){
						switch(result){
							case "success" :
								$("#loadingWrap").fadeOut(350);
								break;
							case "fail" :
								$("#loadingWrap").fadeOut(350);
								break;
							default :
								alert(result);
								$("#loadingWrap").fadeOut(350);
								break;
						}
					},
					error : function(){
						alert("알 수 없는 이유로 " + type + "(를)을 실패하였습니다.");
						$("#loadingWrap").fadeOut(350);
					}
				})
			});



			})

			$(document).on("click",".column_click",function(){
				var idx = $(this).data("idx");
				var name = $(this).data("name");
				var link = '/sub/db_setting/columnDetailU?idx='+idx;
				popupControl2("open", "update", link ,name+"상세항목 수정");
			})

			//드래그
		
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
					
					$.post('/ajax/my/columnSort.php', { sorts : datas }, function(result) {
						alert(result);
						window.location.reload();
					});

				}
				, helper: fixHelper
			})
			
			$(document).on("click", ".trashBtn", function(){
				var type = $(this).attr("data-type");
		
				switch(type){
					case "w":
						$(this).closest('tr').remove();
						break;
					case "u":
						$(this).hide();
						$(this).siblings("i").show();
						$(this).siblings("input").val($(this).attr("data-val"));
						break;
				}
				sortSet();
			})

			
			/* 컨트롤 */
			$(".list_yn").change(function(){
				var target = $(this).closest("tr").find(".use_yn");
				
				if(!$(target).prop("checked")){
					$(this).prop("checked", false);
				}
			});
			
			$(".use_yn").change(function(){
				var target = $(this).closest("tr").find(".list_yn");
				
				if(!$(this).prop("checked")){
					$(target).prop("checked", false);
				}
			});
			
			$(".windoGuide").click(function(){
				// popupControl2("open", "update", '/sub/db_setting/windoGuide' ,"미리보기팝업");
				window.open('/sub/db_setting/windoGuide', '미리보기팝업', 'width=1200px,height=950px,scrollbars=yes');
			})

		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>