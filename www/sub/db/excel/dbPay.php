<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002","004","006"];

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

?>

	<div class="writeWrap">
		<form enctype="multipart/form-data">
			<div class="tit">엑셀 업로드 양식</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>다운로드</th>
						<td class="tl">
							<a href="/excelFrm/db/dbPayL" class="typeBtn btnGreen02"><i class="fas fa-file-download"></i>양식 다운로드</a>
						</td>
					</tr>
					<tr>
						<th style="color: #DC3333;"><i class="fas fa-exclamation-circle" style="margin-right: 5px;"></i>주의</th>
						<td class="tl">
							<span>- 양식의 예시 데이터를 확인 후 내용에 맞게 기재해주셔야 업로드가 가능합니다.</span><br>
							<b>- 생산일자 : 엑셀기본양식에 생산일자는  셀서식에 텍스트로 설정되어 있습니다. 업로드 실패시 셀서식을 다시한번 확인해주세요!</b>
						</td>
					</tr>
				</tbody>
			</table>
			
			<div class="tit">대량 업로드</div>
			<table>
				<colgroup>
					<col width="20%">
					<col width="80%">
				</colgroup>
				<tbody>
					<tr>
						<th>엑셀 업로드</th>
						<td class="tl">
							<input type="file" name="file" id="excelFile">
							<label for="excelFile" class="typeBtn btnGreen01"><i class="fas fa-search"></i>파일선택</label>
							<span id="excelFileName">파일을 선택해주세요.</span>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
	
	<div class="listWrap" style="display: none;">
		<div id="excelResultInfoWrap">
			<ul>
				<li class="label"><div class="background"></div><span>전체 업로드DB</span></li>
				<li class="value"><span id="totalCnt">0</span></li>
				<li class="label"><div class="background"></div><span>업로드 성공DB</span></li>
				<li class="value"><span id="successCnt">0</span></li>
				<li class="label"><div class="background"></div><span>업로드 실패DB</span></li>
				<li class="value"><span id="failCnt">0</span></li>
			</ul>
		</div>
		<table>
			<colgroup>
				<col width="4%">
				<col width="8%">
				<col width="8%">
				<col width="8%">
				<col width="6%">
				<col width="8%">
				<col width="8%">
				<col width="6%">
				<col width="8%">
				<col width="8%">
				<col width="8%">
				<col width="6%">
				<col width="14%">
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>수납일</th>
					<th>진료일</th>
					<th>차트번호</th>
					<th>이름</th>
					<th>보험구분</th>
					<th>수납구분</th>
					<th>의사명</th>
					<th>금액</th>
					<th>최초내원</th>
					<th>내원경로</th>
					<th>실장명</th>
					<th>사유</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	
	<div id="popupBtnWrap">
		<button type="button" class="typeBtn btnBlack excelUploadBtn">완료</button>
		<button type="button" class="typeBtn btnBlack excelCloseBtn" style="display: none;">완료</button>
		<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="excel">취소</button>
	</div>
	
	<script type="text/javascript">
		$(function(){
			
			$(".excelUploadBtn").click(function(){
				var url = $("form").data("ajax");
				var data = new FormData($("form")[0]);

				$("#loadingWrap").fadeIn(350, function(){
					$.ajax({
						url : "/ajax/db/excel/dbPayWP.php",
						type : "POST",
						processData : false,
						contentType : false,
						data : data,
						success : function(result){
							switch(result){
								case "return upload" :
									alert("파일 읽기에 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default :
									$("#successCnt").text(result.success);
									$("#failCnt").text(result.fail);
									$("#totalCnt").text(result.total);

									$.each(result.data, function(key, value){
										var code = '<tr>';
										code += '<td class="lp05">' + (key + 1) + '</td>';
										code += (value.pay_date) ? '<td class="lp05">' + value.pay_date + '</td>' : "<td>-</td>";
										code += (value.treat_date) ? '<td class="lp05">' + value.treat_date + '</td>' : "<td>-</td>";
										code += (value.chart_num) ? '<td class="lp05">' + value.chart_num + '</td>' : "<td>-</td>";
										code += (value.cs_name) ? '<td>' + value.cs_name + '</td>' : "<td>-</td>";
										code += (value.insurance_type) ? '<td class="lp05">' + value.insurance_type + '</td>' : "<td>-</td>";
										code += (value.treat_name) ? '<td class="lp05">' + value.treat_name + '</td>' : "<td>-</td>";
										code += (value.dr_name) ? '<td class="lp05">' + value.dr_name + '</td>' : "<td>-</td>";
										code += (value.pay) ? '<td class="lp05">' + value.pay + '</td>' : "<td>-</td>";
										code += (value.first_date) ? '<td class="lp05">' + value.first_date + '</td>' : "<td>-</td>";
										code += (value.visit_path) ? '<td class="lp05">' + value.visit_path + '</td>' : "<td>-</td>";
										code += (value.md_name) ? '<td class="lp05">' + value.md_name + '</td>' : "<td>-</td>";
										code += '<td style="color: #DC3333;">' + value.reason + '</td>';
										code += '</tr>';

										$(".listWrap tbody").append(code);
									});

									if(!result.data.length){
										$(".listWrap tbody").append('<tr><td colspan="13">업로드 실패된 DB가 존재하지 않습니다.</td></tr>');
									}

									$(".guideWrap").remove();
									$(".writeWrap").remove();
									$(".excelUploadBtn").remove();
									$(".popupCloseBtn").remove();

									$(".listWrap").show();
									$(".excelCloseBtn").css("display", "");
									$("#loadingWrap").fadeOut(350);
									break;
							}
						},
						error : function(){
							alert("알 수 없는 이유로 업로드를 실패하였습니다.");
							$("#loadingWrap").fadeOut(350);
						}
					})
				});
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>