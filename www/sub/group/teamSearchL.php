<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

	$value = array(''=>'');
	$query = "SELECT MT.*, ( SELECT COUNT(*) FROM mt_member WHERE tm_code = MT.idx ) AS m_cnt FROM mt_member_team MT WHERE use_yn = 'Y' ORDER BY idx DESC";
	$sql = list_pdo($query, $value);
	$teamCnt = 0;

?>
	
	<style>
		#popupWrap { height: 100% !important; }
		.simpleSearchWrap { margin-top: 0; margin-bottom: 20px; }
		.simpleSearchWrap > div { width: 100%; float: left; border: 1px solid #EAEAEA; text-align: center; padding: 35px 0; }
		.simpleSearchWrap > div > * { display: inline-block; float: none; margin: 0 3px; }
		.simpleSearchWrap > div > input { width: 300px; }
	</style>
	
	<div class="simpleSearchWrap">
		<div>
			<select class="txtBox" id="label">
				<option value="team_name"><?=$customLabel["tm"]?>명</option>
			</select>
			<input type="text" class="txtBox" id="value">
			<button type="button" class="typeBtn" style="top: 2px;" onclick="getTeamList();">검색</button>
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="80%">
				<col width="16%">
			</colgroup>
			<thead>
				<th>NO</th>
				<th><?=$customLabel["tm"]?>명</th>
				<th>인원 수</th>
			</thead>
			<tbody>
				<?php while($row = $sql->fetch(PDO::FETCH_ASSOC)){ $teamCnt++; ?>
					<tr class="rowMove" data-idx="<?=$row['idx']?>" data-name="<?=dhtml($row['team_name'])?>">
						<td><?=$teamCnt?></td>
						<td><?=dhtml($row['team_name'])?></td>
						<td><?=number_format($row['m_cnt'])?>명</td>
					</tr>
				<?php } ?>
				
				<?php if(!$teamCnt){ ?>
					<tr>
						<td colspan="3"><?=$customLabel["tm"]?>(를)을 검색해주시길 바랍니다.</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	
	<script type="text/javascript">
		function getTeamList(){
			var label = $("#label").val();
			var value = $("#value").val();
			
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : "/ajax/group/getTeamList",
					type : "POST",
					data : {
						label : label,
						value : value
					},
					success : function(result){
						var totalCnt = 0;
						var code = "";
						
						$.each(result, function(index, value){
							totalCnt++;
							
							code += '<tr class="rowMove" data-idx="' + value.idx + '" data-name="' + value.team_name + '">';
							code += '<td>' + totalCnt + '</td>';
							code += '<td>' + value.team_name + '</td>';
							code += '<td class="lp05">' + value.m_cnt + '</td>';
							code += '</tr>';
						});
						
						$(".listWrap tbody > tr").remove();
						$(".listWrap tbody").append(code);
						
						if(!totalCnt){
							$(".listWrap tbody").append('<tr><td colspan="3">조회된 데이터가 존재하지 않습니다.</td></tr>');
						}
						$("#loadingWrap").fadeOut(350);
					}
				})
			});
		}
		$(function(){
			
			$("#value").keydown(function(keyCode){
				if(keyCode.keyCode == 13){
					getTeamList();
				}
			});
			
			$(document).on("click", ".rowMove", function(){
				var idx = $(this).attr("data-idx");
				var name = $(this).attr("data-name");
				
				$("#team_code", parent.document).val(idx);
				$("#selectTeamName", parent.document).text(name);
				$("#selectTeamName", parent.document).css("color", "");
				
				$("#popupBox_search", parent.document).fadeOut(350, function(){
					$("#popupBox_search", parent.document).remove();
				});
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>