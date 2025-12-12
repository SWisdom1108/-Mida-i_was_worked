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
	}
	
	window.location.href = result;
}

// popupSubmitClose
function popupSubmitClose(){
	window.location.reload();
}

// popupControl
function popupControl(type, target, url, name, closeReload){
	switch(type){
		case "open" :
			loading();
			closeReload = (closeReload == undefined) ? false : closeReload;
			var code = '<div class="popupWrap" id="popupBox_' + target + '"><div class="popupBox"><div class="titWrap"><div class="left">' + name + '</div><div class="right"><i class="far fa-times-circle popupCloseBtn" data-reload="' + closeReload + '"></i></div></div><div class="frameWrap"><iframe src="' + url + '"></iframe></div></div></div>';
			$("body").append(code);
			target = $("#popupBox_" + target);

			$(".popupWrap iframe").load(function(){
				$(target).show();
				$("html, body").css("overflow", "hidden");
				
				var h = this.contentWindow.document.body.offsetHeight;
				$(this).height(h);
				
				loadingClose();
			});
			break;
		case "close" :
			target = $("#popupBox_" + target);

			$(target).remove();
			$("html, body").css("overflow", "");
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
		yearSuffix: '년'
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
		var type = $(this).data("type");
		var target = $(this).data("target");
		var url = $(this).data("url");
		var name = $(this).data("name");
		
		popupControl(type, target, url, name);
	});
	
	$(document).on("click", ".popupWrap .popupCloseBtn", function(){
		var reload = $(this).data("reload");
		
		$(this).closest(".popupWrap").remove();
		$("html, body").css("overflow", "");
	});
	
	$("#popupWrap .popupCloseBtn").click(function(){
		var target = $("#popupBox_" + $(this).data("target"), parent.document);
		
		$(target).remove();
		$("html, body").css("overflow", "");
	});
	
	// cntControler
	$(".cntControler").attr("numberonly", "numberonly");
	$(".cntControler").wrap("<div class='cntControlerWrap'>");
	$(".cntControler").closest("div").append('<i class="fas fa-plus" data-type="plus"></i>');
	$(".cntControler").closest("div").prepend('<i class="fas fa-minus" data-type="minus"></i>');
	$(document).on("focusin focusout change focus keyup keydown", ".cntControler", function(){
		if(!$(this).val() || $(this).val() == 0){
			$(this).val("");
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
				if(num < 1){
					num = 1;
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
		var callback = $(el).data("callback");
		var type = $(el).data("type");
		
		var important = $(el).find(".important");
		for(var i = 0; i < important.length; i++){
			var val = $(important[i]).next("td").find("input");
			if(!val.length){
				val = $(important[i]).next("td").find("select");
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
		
		if(confirm("해당 데이터를 삭제하시겠습니까?")){
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : url,
					type : "POST",
					data : { idx : idx },
					success : function(result){
						switch(result){
							case "success" :
								alert("삭제가 완료되었습니다.");
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
		
		if(confirm("해당 데이터를 복원하시겠습니까?")){
			$("#loadingWrap").fadeIn(350, function(){
				$.ajax({
					url : url,
					type : "POST",
					data : { idx : idx },
					success : function(result){
						switch(result){
							case "success" :
								alert("복원이 완료되었습니다.");
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
	
	// leftMenu
	$(".leftMenuOpenBtn").click(function(){
		$("#wrap").css("left", "250px");
		$("#leftMenuWrap").css("left", "0");
	});
	
	$(".leftMenuCloseBtn").click(function(){
		$("#wrap").css("left", "");
		$("#leftMenuWrap").css("left", "");
	});
	
	// console remove
	console.clear();
	
})