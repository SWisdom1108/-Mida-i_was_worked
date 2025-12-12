$(function(){
	
	var o = [];
	if($("#bbsContents").length > 0){
		setTimeout(function(){
			nhn.husky.EZCreator.createInIFrame({
				 oAppRef: o,
				 elPlaceHolder: "bbsContents",
				 sSkinURI: "/plugin/se2/SmartEditor2Skin.html",
				 fCreator: "createSEditor2"
			});
		}, 300);
	}
	
	// 파일첨부
	$("#bbsFile").change(function(){
		var file = this.files[0];
		var filename = file.name;
		var filesize = Math.round(file.size/1024/1024) + "MB";
		var clone = $(this).clone();
		var maxCnt = $(this).data("cnt");
		
		var itemLength = $(".bbsFileUploadListWrap > li").length;
		if((itemLength - 2) >= maxCnt){
			alert("최대 업로드 가능한 파일 개수를 초과하였습니다.");
			return false;
		}
		
		var html = '<li class="item">';
		html += '<div>' + filename + '</div>';
		html += '<div>' + filesize + '</div>';
		html += '<div><i class="far fa-times-circle fileDeleteBtn"></i></div>';
		html += '</li>';
		
		$(".bbsFileUploadListWrap .noData").hide();
		$(".bbsFileUploadListWrap").append(html);
		var item = $(".bbsFileUploadListWrap li:last-of-type");
		$(item).find("div:nth-of-type(1)").append(clone);
		$(item).find("div:nth-of-type(1)").find("input").removeAttr("id");
		$(item).find("div:nth-of-type(1)").find("input").attr("name", "files[]");
	});
	
	$(document).on("click", ".fileDeleteBtn", function(){
		$(this).closest("li").remove();
		
		var item = $(".bbsFileUploadListWrap > li").length;
		if(item == 2){
			$(".bbsFileUploadListWrap .noData").show();
		}
	});
	
	$("#bbsSubmitBtn").click(function(){
		var target = $(this).data("target");
		var el = $("form")[0];
		var type = $(el).data("type");
		var callback = $(el).data("callback");
		var typeName = "";
		
		switch(type){
			case "W" :
				typeName = "등록";
				break;
			case "U" :
				typeName = "수정";
				break;
		}
		
		o.getById["bbsContents"].exec("UPDATE_CONTENTS_FIELD", []);
		var data = new FormData(el);
		$.ajax({
			url : "/mida/module/bbs/proc/bbs/" + type + "P.php",
			type : "POST",
			processData : false,
			contentType : false,
			data : data,
			success : function(result){
				switch(result){
					case "success" :
						window.location.href = callback;
						break;
					case "fail" :
						alert("알 수 없는 이유로 " + typeName + "(를)을 실패하였습니다.");
						break;
					default :
						alert(result);
						break;
				}
			},
			error : function(){
				alert("알 수 없는 이유로 " + typeName + "(를)을 실패하였습니다.");
			}
		});
	});
	
	$("#commentFrm").submit(function(){
		var data = $(this).serialize();
		var type = "댓글 등록";
		
		$.ajax({
			url : "/mida/module/bbs/proc/comment/WP.php",
			type : "POST",
			data : data,
			success : function(result){
				switch(result){
					case "success" :
						window.location.reload();
						break;
					case "fail" :
						alert("알 수 없는 이유로 " + type + "(를)을 실패하였습니다.");
						break;
					default :
						alert(result);
						break;
				}
			},
			error : function(){
				alert("알 수 없는 이유로 " + type + "(를)을 실패하였습니다.");
			}
		});
		
		return false;
	});
	
	$(".commentDeleteBtn").click(function(e){
		e.preventDefault();
		
		if(confirm("해당 댓글을 삭제하시겠습니까?")){
			$.ajax({
				url : "/mida/module/bbs/proc/comment/DP.php",
				type : "POST",
				data : { idx : $(this).data("idx") },
				success : function(result){
					window.location.reload();
				}
			});
		}
	});
	
	$(".bbsDeleteBtn").click(function(e){
		e.preventDefault();
		var bbs = $(this).data("bbs");
		var idx = $(this).data("idx");
		
		if(confirm("해당 게시글을 삭제하시겠습니까?")){
			$.ajax({
				url : "/mida/module/bbs/proc/bbs/DP.php",
				type : "POST",
				data : { idx : idx, bbs : bbs },
				success : function(result){
					if(result == "success"){
						window.location.href = "/sub/bbs/bbs?bbs=" + bbs + "&inc=L";
					}
				}
			});
		}
	});
	
})