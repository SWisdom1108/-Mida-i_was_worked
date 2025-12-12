// loading
function loading(fnc){
	$("#loadingWrap").fadeIn(350, fnc);
}

function loadingClose(){
	$("#loadingWrap").fadeOut(350);
}

// www
function www(url){
	window.location.href = url;
}

// setCookie
function setCookie(name, data){
	var date = new Date();
	
	if(data){
		date.setDate(date.getDate() + 1);
	} else {
		date.setDate(date.getDate() - 1);
	}

	var willCookie = "";
	willCookie += name + "=" + data + ";";
	willCookie += "path=/;";
	willCookie += "expires=" + date.toUTCString();

	document.cookie = willCookie;
}

function byteCheck(el){
    var codeByte = 0;
    for (var idx = 0; idx < el.val().length; idx++) {
        var oneChar = escape(el.val().charAt(idx));
        if ( oneChar.length == 1 ) {
            codeByte ++;
        } else if (oneChar.indexOf("%u") != -1) {
            codeByte += 2;
        } else if (oneChar.indexOf("%") != -1) {
            codeByte ++;
        }
    }
    return codeByte;
}

// http://blog.outsider.ne.kr/556
$.fn.selectRange = function(start, end) {
    return this.each(function() {
         if(this.setSelectionRange) {
             this.focus();
             this.setSelectionRange(start, end);
         } else if(this.createTextRange) {
             var range = this.createTextRange();
             range.collapse(true);
             range.moveEnd('character', end);
             range.moveStart('character', start);
             range.select();
         }
     });
 };

// getClean
function getClean(target, val){
	var url = String(window.location);
	var listCnt = $("#listCnt").val();
	var oB = $("#orderBy").val();
	var overlap_yn = $("#overlap_yn").val();
	var result, get;
	get = url.split("?")[1];
	
	result = window.location.pathname ;
	result += "?page=1";
	
	if(get){
		var targetStatus = false;
		get = String(get).split("&");
		for(var i = 0; i < get.length; i++){
			var getData = String(get[i]).split("=");
			if(getData[0] == "page"){
				continue;
			}
			if(getData[0] == target){
				targetStatus = true;
				result += "&" + target + "=" + val;
			} else {
				result += "&" + get[i];
			}
		}
		
		if(!targetStatus){
			result += "&" + target + "=" + val;
		}
	} else {
		var item = $(".listSet");
		for(var i = 0; i < item.length; i++){
			result += "&" + $(item[i]).attr("id") + "=" + $(item[i]).val();
		}
		result += "&listCnt="+listCnt;
	}
	
	window.location.href = result;
}

// popupSubmitClose
function popupSubmitClose(){
	window.location.reload();
}

// popupControl
function popupControl(type, target, url, name, closeReload, guide,data1,data2,idx){
	switch(type){
		case "open" :
			closeReload = (closeReload == undefined) ? false : closeReload;
			var code = `
			<div class="popupWrap" id="popupBox_` + target + `">
				<div class="prev_btn popup_btns" style="${(( data1 )? "" : "display: none;")} " data-idx="${data1}">
					<i class="fas fa-chevron-left"></i>
				</div>
				<div class="next_btn popup_btns" style="${(( data2 )? "" : "display: none;")} " data-idx="${data2}">
					<i class="fas fa-chevron-right"></i>
				</div>
				<div class="popupBox"><div class="titWrap"><div class="left">` + name;
			if(guide){
				code += `<span class="guide">` + guide + `</span>`;
			}
			code += `</div><div class="right"><i class="far fa-times-circle popupCloseBtn" data-reload="` + closeReload + `"></i></div></div><div class="frameWrap"><iframe src="` + url + `" data-idx="${idx}"></iframe></div></div></div>`;
			$("body").append(code);
			target = $("#popupBox_" + target);

			$(target).fadeIn(350);
			break;

		case "openRadius" :
			closeReload = (closeReload == undefined) ? false : closeReload;
			var code = `
			<div class="popupWrap popupRadius" id="popupBox_` + target + `">
				<div class="prev_btn popup_btns" style="${(( data1 )? "" : "display: none;")} " data-idx="${data1}">
					<i class="fas fa-chevron-left"></i>
				</div>
				<div class="next_btn popup_btns" style="${(( data2 )? "" : "display: none;")} " data-idx="${data2}">
					<i class="fas fa-chevron-right"></i>
				</div>
				<div class="popupBox"><div class="titWrap"><div class="left">` + name;
			if(guide){
				code += `<span class="guide">` + guide + `</span>`;
			}
			code += `</div></div><div class="frameWrap"><iframe src="` + url + `" data-idx="${idx}"></iframe></div></div></div>`;
			$("body").append(code);
			target = $("#popupBox_" + target);

			$(target).fadeIn(350);
			break;

		case "close" :
			target = $("#popupBox_" + target);

			$(target).fadeOut(350, function(){
				$(target).remove();
			});
			break;
	}
}




function popupControl2(type, target, url, name, closeReload, guide){
	switch(type){
		case "open" :
			closeReload = (closeReload == undefined) ? false : closeReload;
			var code = `<div class="popupWrap" id="popupBox_` + target + `"><div class="popupBox"><div class="titWrap"><div class="left">` + name;
			if(guide){
				code += `<span class="guide">` + guide + `</span>`;
			}
			code += `</div><div class="right"><i class="far fa-times-circle popupCloseBtns" data-reload="` + closeReload + `"></i></div></div><div class="frameWrap"><iframe src="` + url + `"></iframe></div></div></div>`;
			$("body").append(code);
			target = $("#popupBox_" + target);

			$(target).fadeIn(350);
			break;
		case "close" :
			target = $("#popupBox_" + target);

			$(target).fadeOut(350, function(){
				$(target).remove();
			});
			break;
	}
}


// 허용IP목록용 
function popupControls(type, target, url, name, closeReload, guide){
	switch(type){
		case "open" :
			closeReload = (closeReload == undefined) ? false : closeReload;
			var code = '<div class="popupWraps" id="popupBox_' + target + '"><div class="popupBox" style="height: 490px; top: 60%; left: 51%;"><div class="titWrap"><div class="left">' + name;
			if(guide){
				code += '<span class="guide">' + guide + '</span>';
			}
			code += '</div><div class="right"><i class="far fa-times-circle popupCloseBtn" data-reload="' + closeReload + '"></i></div></div><div class="frameWrap"><iframe src="' + url + '"></iframe></div></div></div>';
			$("body").append(code);
			target = $("#popupBox_" + target);

			$(target).fadeIn(350);
			break;
		case "close" :
			target = $("#popupBox_" + target);

			$(target).fadeOut(350, function(){
				$(target).remove();
			});
			break;
	}
}

function popupControls2(type, target, url, name, closeReload, guide){
	switch(type){
		case "open" :
			closeReload = (closeReload == undefined) ? false : closeReload;
			var code = "";
			code += '<iframe id="noti_popup" src="' + url + '"></iframe>';
			$("body").append(code);
			target = $("#popupBox_" + target);

			$(target).fadeIn(350);
			break;
		case "close" :
			target = $("#popupBox_" + target);

			$(target).fadeOut(350, function(){
				$(target).remove();
			});
			break;
	}
}

$(function(){
	
	$(".listSet").change(function(){
		var target = $(this).attr("id");
		var val = $(this).val();
		
		getClean(target, val);
	});
	
	// datepicker
	$.datepicker.setDefaults({
		dateFormat : 'yy-mm-dd',
		prevText: '이전 달',
		nextText: '다음 달',
		monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		dayNames: ['일','월','화','수','목','금','토'],
		dayNamesShort: ['일','월','화','수','목','금','토'],
		dayNamesMin: ['일','월','화','수','목','금','토'],
		showMonthAfterYear: true,
		changeMonth: true,
		changeYear: true,
		yearSuffix: '',
		showOtherMonths:true,
		selectOtherMonths: true,
		yearRange: '1900:c'
	});
	
	// datepicker set
	$("input:text[dateonly]").attr("readonly", "readonly");
	$("input:text[dateonly]").datepicker({
		onClose : function(selectedDate){
			if($(this).hasClass("s_date")){
				$(this).parent("*").find(".e_date").datepicker("option", "minDate", selectedDate);
			}
			if($(this).hasClass("e_date")){
				$(this).parent("*").find(".s_date").datepicker("option", "maxDate", selectedDate);
			}
		}
	});
	
	// if($("input:text[coloronly]").length > 0){	
	// 	$("input:text[coloronly]").minicolors({});
	// }

	if($("input:text[coloronly]").length > 0){	
		$('input:text[coloronly]').spectrum({
			type: "component"
		});
	}
	
	$(document).on("focusin focusout change focus keyup keydown", "input:text[numberonly]", function() {
		$(this).val($(this).val().replace(/[^0-9]/g,""));
	});
	
	$(document).on("focusin focusout change focus keyup keydown", "input:text[numonly]", function() {
		$(this).val($(this).val().replace(/[^0-9-]/g,""));
	});
	
	$(document).on("focusin focusout change focus keyup keydown", "input:text[usernameonly]", function() {
		$(this).val($(this).val().replace(/[^a-z0-9_]/g,""));
	});
	
	// dateBtn
	$(".searchWrap .dateBtn").click(function(){
		var s = $(this).data("s");
		var e = $(this).data("e");
		
		$(this).closest("li").find(".s_date").val(s);
		$(this).closest("li").find(".e_date").val(e);
	});
	
	// popup control
	$(".popupBtn").click(function(){
		var type = $(this).attr("data-type");
		var target = $(this).attr("data-target");
		var url = $(this).attr("data-url");
		var name = $(this).attr("data-name");
		
		popupControl(type, target, url, name);
	});

	// 허용IP목록용 
	$(".popupBtn2").click(function(){
		var type = $(this).attr("data-type");
		var target = $(this).attr("data-target");
		var url = $(this).attr("data-url");
		var name = $(this).attr("data-name");
		
		popupControls(type, target, url, name);
	});
	
	$(document).on("click", ".popupWrap .popupCloseBtn", function(){
		var reload = $(this).data("reload");
		
		$(this).closest(".popupWrap").fadeOut(350, function(){
			$(this).remove();
			
			// if(reload){
				window.location.reload();
			// }
		});
	});

	$(document).on("click", ".popupWrap .popupCloseBtn", function(){
		var reload = $(this).data("reload");
		
		$(this).closest(".popupWrap").fadeOut(350, function(){
			$(this).remove();
			
			// if(reload){
				window.location.reload();
			// }
		});
	});

	$(document).on("click", ".popupWrap .popupCloseBtns", function(){
		var reload = $(this).data("reload");
		
		$(this).closest(".popupWrap").fadeOut(350, function(){
			$(this).remove();
			
			// if(reload){
				// window.location.reload();
			// }
		});
	});
	
	$("#popupBtnWrap .popupCloseBtn").click(function(){
		var target = $("#popupBox_" + $(this).data("target"), parent.document);

		$(target).fadeOut(350, function(){
			// $(target).remove();
			parent.location.reload();
		});

	});
	
	// cntControler
	$(".cntControler").attr("numberonly", "numberonly");
	$(".cntControler").wrap("<div class='cntControlerWrap'>");
	$(".cntControler").closest("div").append('<i class="fas fa-plus" data-type="plus"></i>');
	$(".cntControler").closest("div").prepend('<i class="fas fa-minus" data-type="minus"></i>');
	$(document).on("focusin focusout change focus keyup keydown", ".cntControler", function(){
		if(!$(this).val() || $(this).val() <= 0){
			$(this).val(0);
		}
	});
	
	$(document).on("click", ".cntControlerWrap i", function(){
		var type = $(this).data('type');
		var el = $(this).closest("div").find("input");
		
		switch(type){
			case "plus" :
				$(el).val(Number($(el).val()) + 1);
				break;
			case "minus" :
				var num = Number($(el).val()) - 1;
				if(num <= 0){
					num = 0;
				}
				$(el).val(num);
				break;
		}
	});
	
	// filename
	$(".getFileName").change(function(){
		if($(this)[0].files[0]){
			var target = $(this).data("target");
			$("#" + target + "FileName").html($(this)[0].files[0].name);
		}
	});

	$(".excelFile_etc").change(function(){
		if($(this)[0].files[0]){
			if($(this)[0].files[0].name){
				$(this).closest("td").find("#excelFileName").html($(this)[0].files[0].name);
			}else{
				$(this).closest("td").find("#excelFileName").html("파일을 선택해주세요.");
			}
		}else{
			$(this).closest("td").find("#excelFileName").html("파일을 선택해주세요.");
		}
	});
	
	// excel
	$("#excelFile").change(function(){
		if($(this)[0].files[0]){
			$("#excelFileName").html($(this)[0].files[0].name);
		}
	});
	
	$(".excelCloseBtn").click(function(){
		parent.popupSubmitClose();
	});
	
	// imageUploader
	$(".imageUploader").change(function(){
		var target = $("#imagePreview_" + $(this).data("target"));
		
		if($(this)[0].files[0]){
			var reader = new FileReader();
			reader.onload = function(e){
				$(target).attr('src', e.target.result);
				$(target).css("opacity", 1);
				$(".bgWrap").remove();
				$(".bgDarkWrap").remove();
			}
			
			reader.readAsDataURL($(this)[0].files[0]);
		}
	});
	
	// submit
	var o = [];
	if($("#edit01").length > 0){
		setTimeout(function(){
			nhn.husky.EZCreator.createInIFrame({
				 oAppRef: o,
				 elPlaceHolder: "edit01",
				 sSkinURI: "/plugin/se2/SmartEditor2Skin.html",
				 fCreator: "createSEditor2"
			});
		}, 300);
	}
	
	$(".submitBtn").click(function(){
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
							alert(type + "(가)이 완료되었습니다.");
							if(callback){
								if(callback == "close"){
									parent.popupSubmitClose();
								} else {
									window.location.href = callback;
								}
							} else {
								window.location.reload();
							}
							break;
						case "fail" :
							alert("알 수 없는 이유로 " + type + "(를)을 실패하였습니다.");
							$("#loadingWrap").fadeOut(350);
							break;
						case "fail2" :
							alert("담당자가 없습니다. 조직에서 담당자를 추가하고 DB를 추가해주세요.");
							window.parent.location.href = "/sub/group/teamMemberL";
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
	});
	
	$(".deleteBtn").click(function(){
		var url = $(this).data("ajax");
		var callback = $(this).data("callback");
		var idx = $(this).data("idx");
		var msg = $(this).data("successmsg");
		msg = (msg) ? msg : "삭제가 완료되었습니다.";
		
		if(confirm("해당 데이터를 삭제하시겠습니까?")){
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : url,
					type : "POST",
					data : { idx : idx },
					success : function(result){
						switch(result){
							case "success" :
								alert(msg);
								if(callback){
									if(callback == "close"){
										parent.popupSubmitClose();
									} else {
										window.location.href = callback;
									}
								} else {
									window.location.reload();
								}
								break;
							case "fail" :
								alert("알 수 없는 이유로 삭제를 실패하였습니다.");
								$("#loadingWrap").fadeOut(350);
								break;
							default :
								alert(result);
								$("#loadingWrap").fadeOut(350);
								break;
						}
					},
					error : function(){
						alert("알 수 없는 이유로 삭제를 실패하였습니다.");
						$("#loadingWrap").fadeOut(350);
					}
				})
			});
		}
	});
	
	$(".dataReturnBtn").click(function(){
		var url = $(this).data("ajax");
		var callback = $(this).data("callback");
		var idx = $(this).data("idx");
		var msg = $(this).data("successmsg");
		msg = (msg) ? msg : "복원이 완료되었습니다.";
		
		if(confirm("해당 데이터를 복원하시겠습니까?")){
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : url,
					type : "POST",
					data : { idx : idx },
					success : function(result){
						switch(result){
							case "success" :
								alert(msg);
								if(callback){
									if(callback == "close"){
										parent.popupSubmitClose();
									} else {
										window.location.href = callback;
									}
								} else {
									window.location.reload();
								}
								break;
							case "fail" :
								alert("알 수 없는 이유로 복원을 실패하였습니다.");
								$("#loadingWrap").fadeOut(350);
								break;
							default :
								alert(result);
								$("#loadingWrap").fadeOut(350);
								break;
						}
					},
					error : function(){
						alert("알 수 없는 이유로 복원을 실패하였습니다.");
						$("#loadingWrap").fadeOut(350);
					}
				})
			});
		}
	});
	
	// listDataAllCheck
	$("#listDataAllCheck").change(function(){
		$(".listDataCheck").prop("checked", $(this).prop("checked"));
		
		var item = $(".listDataCheck:checked");
		var idx = [];
		for(var i = 0; i < item.length; i++){
			idx.push($(item[i]).data("idx"));
		}

		idx.join(",");

		setCookie("listCheckData", idx);
	});
	
	$(".listWrap tbody > tr > td:first-of-type").click(function(e){
		e.stopPropagation();
	});
	
	$(".listDataCheck").click(function(e){
		e.stopPropagation();
	});
	
	$(".listDataCheck").change(function(e){
		e.stopPropagation();
		
		var itemLen = $(".listDataCheck").length;
		var checkLen = $(".listDataCheck:checked").length;
		
		var item = $(".listDataCheck:checked");
		var idx = [];
		for(var i = 0; i < item.length; i++){
			idx.push($(item[i]).data("idx"));
		}

		idx.join(",");

		setCookie("listCheckData", idx);
		
		if(itemLen == checkLen){
			$("#listDataAllCheck").prop("checked", true);
		} else {
			$("#listDataAllCheck").prop("checked", false);
		}
	});

	$(".stopProgram").click(function(e){
		e.stopPropagation();
	});
	
	// myNoteBtn
	$("#myNoteBtn").click(function(e){
		e.preventDefault();
		
		popupControl("open", "myNote", "/sub/note/noteL", "나의 쪽지함");
	});
	
	// myNotificationBtn
	$("#myNotificationBtn").click(function(e){
		e.preventDefault();
		
		popupControl("open", "myNote", "/sub/notice/noticeL", "알림");
	});
	
	// db 삭제
	$(".dbAllDeleteBtn").click(function(){
		var item = $(".listDataCheck:checked");

		if(!item.length){
			alert("삭제할 DB를 선택해주시길 바랍니다.");
			return false;
		}
		
		var idx = [];
		for(var i = 0; i < item.length; i++){
			idx.push($(item[i]).data("idx"));
		}

		idx = idx.join(",");

		if(confirm("선택된 DB들을 삭제하시겠습니까?")){
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : "/ajax/db/dbAllDP",
					data : {
						idx : idx
					},
					type : "POST",
					success : function(result){
						alert("삭제가 완료되었습니다.");
						window.location.reload();
					}
				})
			});
		}
	});
	
	// 201118 좌측메뉴컨트롤
	$(".leftSideSizeControlBtn").click(function(e){
		e.preventDefault();
		
		$("#headerWrap").toggleClass("active");
		$("#leftSideWrap").toggleClass("active");
		$("#mainContentsWrap").toggleClass("active");
		
		var cookieData = ($("#headerWrap").hasClass("active")) ? 1 : 0;
		setCookie("leftSideSizeStatus", cookieData);
	});

	// 회원 리스트 사용유무
	// 조직관리->생산업체관리 사용여부
	$(".changeUseYn").change(function(event) {
		var checked = $(this).is(":checked");
		var idx = $(this).val();
		var m_idx = $(this).data("midx");
		var use_yn = (checked == true) ? "Y": "N";

		if ( confirm("사용상태를 변경하시겠습니까?") == true ){
			$.post('/ajax/group/changeUseYn.php', { idx : idx, use_yn : use_yn, m_idx : m_idx }, function(data, textStatus, xhr) {
				alert(data.msg);
				if ( data.status_code == "success" ){
					window.location.reload();
				}
			});
		}

	});

	// 회원 리스트 엑셀다운로드 사용유무
	$(".changeExcelYn").change(function(event) {
		var checked = $(this).is(":checked");
		var idx = $(this).val();
		var excel_yn = (checked == true) ? "Y": "N";
		
		if ( confirm("엑셀다운로드 상태를 변경하시겠습니까?") == true ){
			$.post('/ajax/group/changeExcelYn.php', { idx : idx, excel_yn : excel_yn }, function(data, textStatus, xhr) {
				alert(data.msg);
				if ( data.status_code == "success" ){
					window.location.reload();
				}
			});
		}

	});


	$(".blockAllDeleteBtn").click(function(){
		var item = $(".listDataCheck:checked");

		if(!item.length){
			alert("차단해제 할 계정을 선택해주시길 바랍니다.");
			return false;
		}
		
		var idx = [];
		for(var i = 0; i < item.length; i++){
			idx.push($(item[i]).data("idx"));
		}

		idx = idx.join(",");

		if(confirm("선택된 계정들을 차단해제하시겠습니까?")){
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : "/ajax/group/blockDP",
					data : {
						idx : idx
					},
					type : "POST",
					success : function(result){
						alert("차단해제가 완료되었습니다.");
						window.location.reload();
					}
				})
			});
		}
	});
	
	// console remove
	// console.clear();
	
	
})