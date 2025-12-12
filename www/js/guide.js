var label = [];
function guideLabelSetting(tm, fc){
	label["tm"] = tm;
	label["fc"] = fc;
}

var guideInfo = [];
function guideSetting(classs){
	guideInfo['title'] = "";
	guideInfo['info'] = "";

	switch(classs){
		case "groupTeamUse" :
			guideInfo['title'] = "" + label["tm"] + " 사용여부";
			guideInfo['info'] += "- 설정 시 해당 " + label["fc"] + "들의 사용여부도 모두 반영됩니다.";
			break;
		case "snumDonw" :
			guideInfo['title'] = " 보안카드 다운";
			guideInfo['info'] += "- 이미지 클릭 시 해당 보안카드를 다운로드 하실 수 있습니다.";
			break;
		case "groupTeamMG" :
			guideInfo['title'] = "" + label["tm"] + " 담당관리자여부";
			guideInfo['info'] += "- " + label["tm"] + "의 관리자를 설정하실 수 있습니다.";
			guideInfo['info'] += "<br>- 설정 시 기존 " + label["tm"] + "의 담당자와 상관없이 변경됩니다.";
			break;
		case "columnEx" :
			guideInfo['title'] = "DB항목 가이드설정";
			guideInfo['info'] += "- 엑셀양식 기재 시 가이드예시를 추가하여 관리하실 수 있습니다.";
			break;
		case "columnList" :
			guideInfo['title'] = "DB항목 목록노출여부";
			guideInfo['info'] += "- 설정 시 DB관리 목록에서 설정된 항목을 바로 확인하실 수 있습니다.";
			break;
		case "columnOverlap" :
			guideInfo['title'] = "DB항목 중복검사설정";
			guideInfo['info'] += "- 중복체크를 설정 하신 후 날짜를 0으로 설정하시면 기간에 상관없이 모든 DB의 중복체크를 하실수 있습니다.";
			break;		
		case "dbDistAutoFC" :
			guideInfo['title'] = "DB자동분배 담당자 정보";
			guideInfo['info'] += "- 담당자 등록 시 설정한 우선순위와 기본수량으로 기본설정이 이루어집니다.";
			guideInfo['info'] += "<br>- 드래그 앤 드랍으로 우선순위 변경이 가능하며, 기본수량 역시 변경이 가능합니다.";
			guideInfo['info'] += "<br>- 자동분배 시 입력한 설정값은 기본설정에 반영되지 않습니다.";
			break;
		case "dbDistType" :
			guideInfo['title'] = "DB자동분배방식 선택";
			guideInfo['info'] += "- 순차분배 : 생성된 DB가 분배시 <b>하나씩 수량에 맞게 순차적으로 분배</b>되는 형태를 말합니다.";
			guideInfo['info'] += "<br>- 그룹별 분배 : <b>그룹별로 우선순위설정</b>(" + label["tm"] + "목록우선순위)에 맞게 순차적으로 분배됩니다.";
			break;
		case "dbDistTarget" :
			guideInfo['title'] = "DB자동분배대상 선택";
			guideInfo['info'] += "- " + label["tm"] + "별분배를 하신 경우 " + label["tm"] + "담당자에게 자동분배되며, " + label["tm"] + " 담당자는 " + label["fc"] + "에게 분배하여 관리하실 수 있습니다.";
			break;
		case "groupPM_API":
			guideInfo['title'] = "생산업체 API정보";
			guideInfo['info'] += "- 생산업체별 API를 발급하실 수 있습니다. 발급된 API는 외부시스템과 연결하여, DB를 자동으로 업로드할 수 있게 사용이 가능합니다.";
			break;
		case "full_sharing" :
			guideInfo['title'] = "전체공유여부";
			guideInfo['info'] += "- 전체공유여부 선택 시 일정이 모든 사용자에게 캘린더로 공유됩니다. 알림은 본인에게만 전송됩니다.";
			break;
		case "team_sharing" :
			guideInfo['title'] = "팀내공유여부";
			guideInfo['info'] += "- 팀내공유여부 선택 시 일정이 팀에게 캘린더로 공유됩니다. 알림은 본인에게만 전송됩니다.";
			break;
	}
}

$(function(){
	
	// guideBtn
	$(".miniGuideWrap").html('<i class="fas fa-exclamation-circle guideBtn"></i>');
	$(".miniGuideWrap").parent("*").addClass("drag");
	
	$(document).on("click", ".miniGuideWrap .guideBtn", function(){
		var classs = $(this).closest(".miniGuideWrap").data("class");
		guideSetting(classs);
		
		var len = $(this).closest(".miniGuideWrap").find("div").length;
		var code = "<div>";
		code += "<span class='title'>" + guideInfo['title'] + "</span>";
		code += "<p class='info'>" + guideInfo['info'] + "</p>";
		code+= "</div>";
		
		if(len){
			$(this).closest(".miniGuideWrap").find("div").remove();
		} else {
			$(this).closest(".miniGuideWrap").append(code);
		}
	});
	
})