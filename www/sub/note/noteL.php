<?php

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

?>

	<style>
		
		#popupWrap { height: 100% !important; }
		
	</style>
	
	<div id="myNoteWrap">
		<div class="frmWrap">
			<form id="sendFrm">
				<input type="text" class="txtBox" name="receive_id" id="receive_id" placeholder="받는 사람 아이디 (쉼표로 여러명 전달가능)">
				<textarea name="content" id="content" class="txtBox" placeholder="보낼 내용 입력..."></textarea>
				<button type="button" class="typeBtn" id="noteSendBtn">전송</button>
				<button type="reset" class="typeBtn btnGray02">초기화</button>
			</form>
			<div id="noteViewWrap">
				<input type="hidden" id="noteViewFromID">
				<span class="userInfo"></span>
				<div class="contentsWrap"></div>
				<button type="button" class="typeBtn" id="noteReturnBtn">답장</button>
				<button type="button" class="typeBtn btnGray02" id="noteViewCloseBtn">닫기</button>
			</div>
		</div>
		<div class="listWrap">
			<div class="tabWrap">
				<ul>
					<li data-type="send" class="active">받은 쪽지</li>
					<li data-type="my">보낸 쪽지</li>
					<li data-type="book">즐겨찾기</li>
				</ul>
			</div>
			<div class="viewWrap">
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
		function loadListView(type){
			loading(function(){
				$(".listWrap > .viewWrap").html("");
				$.ajax({
					url : "/ajax/note/list/" + type,
					type : "GET",
					success : function(result){
						$(".listWrap > .viewWrap").html(result);
						loadingClose();
					}
				})
			});
		}
		
		$(function(){
			
			// 초기목록
			loadListView("send");
			
			// 전송
			$("#noteSendBtn").click(function(){
				var datas = $("#sendFrm").serialize();
				
				if(!$("#sendFrm > #receive_id").val()){
					alert("받으실 분의 아이디를 입력해주시길 바랍니다.");
					$("#sendFrm > #receive_id").focus();
					return false;
				}
				
				if(!$("#sendFrm > #content").val()){
					alert("보내실 내용을 입력해주시길 바랍니다.");
					$("#sendFrm > #content").focus();
					return false;
				}
				
				loading(function(){
					$.ajax({
						url : "/ajax/note/noteWP",
						type : "POST",
						data : datas,
						success : function(result){
							alert(result);
							loadingClose();
							location.reload();
						}
					})
				})
			});
			
			// 탭메뉴
			$(".tabWrap > ul > li").click(function(){
				var type = $(this).data("type");
				
				$(".tabWrap > ul > li").removeClass("active");
				$(this).addClass("active");
				
				loadListView(type);
			});
			
			// 쪽지 닫기
			$("#noteViewCloseBtn").click(function(){
				$("#sendFrm").show();
				$("#noteViewWrap").hide();
				$("#myNoteWrap > .listWrap > .viewWrap > ul > li").removeClass("active");
			});
			
			// 쪽지 불러오기
			$(document).on("click", ".sendListWrap > li", function(){
				var idx = $(this).data("idx");
				var type = $(this).data("type");
				var fromID = $(this).data("from-id");
				
				$("#myNoteWrap > .listWrap > .viewWrap > ul > li").removeClass("active");
				$(this).addClass("active");
				loading(function(){
					$.ajax({
						url : "/ajax/note/noteV",
						type : "POST",
						data : { idx : idx },
						success : function(result){
							if(result.msg == "fail"){
								$("#sendFrm").show();
								$("#noteViewWrap").hide();
								$("#myNoteWrap > .listWrap > .viewWrap > ul > li").removeClass("active");
							} else {
								$("#myNoteWrap #noteViewWrap > .userInfo").text(result.user);
								$("#myNoteWrap #noteViewWrap > .contentsWrap").html(result.memo);
								
								if(result.date){
									$("#myNoteItem_" + idx).find(".viewDate").removeClass("before");
									$("#myNoteItem_" + idx).find(".viewDate").html('<i class="fas fa-eye"></i>' + result.date);
								}
								
								$("#sendFrm").hide();
								$("#noteViewWrap").show();
								
								if(type == "my"){
									$("#noteReturnBtn").show();
								} else {
									$("#noteReturnBtn").hide();
								}
								
								if(fromID){
									$("#noteViewFromID").val(fromID);
								} else {
									$("#noteViewFromID").val("");
								}
							}
							loadingClose();
						}
					})
				});
			});
			
			// 주소록 추가
			$(document).on("click", "#bookAddBtn", function(){
				var datas = $("#addBookFrm > form").serialize();
				
				if(!$("#addBookFrm #userID").val()){
					alert("추가할 회원의 아이디를 입력해주시길 바랍니다.");
					$("#addBookFrm #userID").focus();
					return false;
				}
				
				loading(function(){
					$.ajax({
						url : "/ajax/note/bookWP",
						type : "POST",
						data : datas,
						success : function(result){
							$("#addBookFrm #userID").val("");
							$("#addBookFrm #userMemo").val("");
							
							if(result.msg != "success"){
								alert(result.msg);
							} else {
								var code = '<li data-idx="' + result.idx + '">';
								code += '<input type="text" class="txtBox" id="bookMemo_' + result.idx + '" value="' + result.memo2 + '">';
								code += '<span class="memo">' + result.memo + '</span>';
								code += '<span class="user">' + result.user + '</span>';
								code += '<div class="buttonList mod" style="display: none;">';
								code += '<i class="fas fa-check bookModOkBtn" data-idx="' + result.idx + '"></i>';
								code += '</div>';
								code += '<div class="buttonList basic">';
								code += '<i class="fas fa-envelope bookSendBtn" title="보내기" data-id="' + result.id + '"></i>';
								code += '<i class="fas fa-pencil-alt bookModBtn" title="수정"></i>';
								code += '<i class="fas fa-trash-alt bookDeleteBtn" data-idx="' + result.idx + '" title="삭제"></i>';
								code += '</div>';
								code += '</li>';
								
								$(".bookListWrap > #addBookFrm").after(code);
							}
							loadingClose();
						}
					})
				});
			});
			
			// 주소록 삭제
			$(document).on("click", ".bookDeleteBtn", function(){
				var idx = $(this).data("idx");
				
				$.ajax({
					url : "/ajax/note/bookDP",
					type : "POST",
					data : { idx : idx },
					success : function(result){
						if(result == "success"){
							$(".bookItem_" + idx).remove();
						}
					}
				})
			});
			
			// 주소록에 추가
			$(document).on("click", ".bookSendBtn", function(){
				var id = $(this).data("id");
				var idList = $("#receive_id").val();
				idList = (idList) ? idList + ", " + id : id;
				
				$("#receive_id").val(idList);
				$("#sendFrm").show();
				$("#noteViewWrap").hide();
			});
			
			// 주소록 수정
			$(document).on("click", ".bookModBtn", function(){
				$(this).closest("li").find("input").show();
				$(this).closest("li").find(".buttonList.mod").show();
				$(this).closest("li").find(".buttonList.basic").hide();
				$(this).closest("li").find(".memo").hide();
			});
			
			$(document).on("click", ".bookModOkBtn", function(){
				var idx = $(this).data("idx");
				var memo = $("#bookMemo_" + idx).val();
				var el = $(this);
				
				$.ajax({
					url : "/ajax/note/bookUP",
					type : "POST",
					data : { idx : idx, memo : memo },
					success : function(result){
						if(result != "fail"){
							$(el).closest("li").find(".memo").text(result);
						}
						
						$(el).closest("li").find("input").hide();
						$(el).closest("li").find(".buttonList.mod").hide();
						$(el).closest("li").find(".buttonList.basic").show();
						$(el).closest("li").find(".memo").show();
					}
				})
			});
			
			// 답장
			$("#noteReturnBtn").click(function(){
				var id = $("#noteViewFromID").val();
				var con = $("#noteViewWrap .contentsWrap").text();
				con = "\n\n>>\n" + con;
				
				$("#receive_id").val(id);
				$("#content").val(con);
				
				$("#sendFrm").show();
				$("#noteViewWrap").hide();
				$("#myNoteWrap > .listWrap > .viewWrap > ul > li").removeClass("active");
				$("#content").selectRange(0, 0);
			});
			
			// 즐겨찾기 자동추가
			$(document).on("click", ".addGroupBtn", function(){
				var type = $(this).attr("data-type");
				
				loading(function(){
					$.ajax({
						url : "/ajax/note/book" + type + "WP",
						success : function(result){
							$.each(result, function(key, value){
								var code = '<li data-idx="' + value.idx + '">';
								code += '<input type="text" class="txtBox" id="bookMemo_' + value.idx + '" value="' + value.memo2 + '">';
								code += '<span class="memo">' + value.memo + '</span>';
								code += '<span class="user">' + value.user + '</span>';
								code += '<div class="buttonList mod" style="display: none;">';
								code += '<i class="fas fa-check bookModOkBtn" data-idx="' + value.idx + '"></i>';
								code += '</div>';
								code += '<div class="buttonList basic">';
								code += '<i class="fas fa-envelope bookSendBtn" title="보내기" data-id="' + value.id + '"></i>';
								code += '<i class="fas fa-pencil-alt bookModBtn" title="수정"></i>';
								code += '<i class="fas fa-trash-alt bookDeleteBtn" data-idx="' + value.idx + '" title="삭제"></i>';
								code += '</div>';
								code += '</li>';

								$(".bookListWrap > #addBookFrm").after(code);
							});
							loadingClose();
						}
					})
				});
			});
			
		})
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>	