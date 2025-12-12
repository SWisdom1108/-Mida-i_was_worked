function login(){
	loading(function(){
		$.ajax({
			url : "/ajax/account/login",
			type : "POST",
			data : {
				username : $("#username").val(),
				userpassword : $("#userpassword").val(),
				saveUserInfo : $("#saveUserInfo").prop("checked")
			},
			success : function(result){
				switch(result){
					case "return user" :
						alert("존재하지 않는 아이디입니다.");
						loadingClose();
						break;
					case "return auth" :
						alert("로그인이 불가능한 계정입니다.");
						loadingClose();
						break;
					case "return ip" :
						alert("접근이 허용되지 않은 IP입니다.");
						loadingClose();
						break;
					case "return password" :
						alert("비밀번호가 일치하지 않습니다.");
						loadingClose();
						break;
					case "success" :
						window.location.reload();
						break;
					case "s_card" :
						var form = document.createElement("form");
					    form.setAttribute("method", "POST");
					    form.setAttribute("action", "/account/s_card");

					    // 넘길 값을 input hidden 필드로 추가
					    var hiddenField = document.createElement("input");
					    hiddenField.setAttribute("type", "hidden");
					    hiddenField.setAttribute("name", "id");  // 전송할 데이터의 키
					    hiddenField.setAttribute("value", $("#username").val()); // 전송할 데이터의 값
					    form.appendChild(hiddenField);

					    var hiddenField2 = document.createElement("input");
					    hiddenField2.setAttribute("type", "hidden");
					    hiddenField2.setAttribute("name", "pw");  // 전송할 데이터의 키
					    hiddenField2.setAttribute("value", $("#userpassword").val()); // 전송할 데이터의 값
					    form.appendChild(hiddenField2);

					    // form을 body에 추가하고 제출
					    document.body.appendChild(form);
					    form.submit();
					    break;
					case "out" :
						alert("로그인 횟수를 초과하였습니다. 관리자("+mainTel+")에게 문의 부탁드립니다.");
						loadingClose();
						break;
						// location.href="/out.php";
						break;
					default :
						alert(result);
						loadingClose();
						break;
				}
			}
		})
	});
}


function login2(){
	loading(function(){
		$.ajax({
			url : "/ajax/account/snum",
			type : "POST",
			data : {
				username : $("#username").val(),
				first : $("#first").val(),
				second : $("#second").val(),
			},
			success : function(result){
				switch(result){
					case "return user" :
						alert("존재하지 않는 아이디입니다.");
						loadingClose();
						break;
					case "return auth" :
						alert("로그인이 불가능한 계정입니다.");
						loadingClose();
						break;
					case "return ip" :
						alert("접근이 허용되지 않은 IP입니다.");
						loadingClose();
						break;
					case "return password" :
						alert("비밀번호가 일치하지 않습니다.");
						loadingClose();
						break;
					case "success" :
						window.location.reload();
						break;
					case "s_card" :
						var form = document.createElement("form");
					    form.setAttribute("method", "POST");
					    form.setAttribute("action", "/account/s_card");

					    // 넘길 값을 input hidden 필드로 추가
					    var hiddenField = document.createElement("input");
					    hiddenField.setAttribute("type", "hidden");
					    hiddenField.setAttribute("name", "id");  // 전송할 데이터의 키
					    hiddenField.setAttribute("value", $("#username").val()); // 전송할 데이터의 값
					    form.appendChild(hiddenField);

					    var hiddenField2 = document.createElement("input");
					    hiddenField2.setAttribute("type", "hidden");
					    hiddenField2.setAttribute("name", "pw");  // 전송할 데이터의 키
					    hiddenField2.setAttribute("value", $("#userpassword").val()); // 전송할 데이터의 값
					    form.appendChild(hiddenField2);

					    // form을 body에 추가하고 제출
					    document.body.appendChild(form);
					    form.submit();
					    break;
					case "out" :
						alert("로그인 횟수를 초과하였습니다. 관리자("+mainTel+")에게 문의 부탁드립니다.");
						loadingClose();
						break;
						// location.href="/out.php";
						break;
					default :
						alert(result);
						loadingClose();
						break;
				}
			}
		})
	});
}

$(function(){
	
	$("#membersBox.login #loginBtn").click(function(){
		login();
	});
	
	$("#membersBox.login #userpassword").keyup(function(key){
		if(key.keyCode == 13){
			login();
		}
	});

	$("#membersBox.login #loginBtn2").click(function(){
		login2();
	});
	
	$("#membersBox.login #second").keyup(function(key){
		if(key.keyCode == 13){
			login2();
		}
	});
	
	// 회원정보확인
	var forgottelcheck = false;
	$("#forgottelCheckBtn").click(function(){
		var tel = $("#usertel").val();
		var id = $("#username").val();
		var name = $("#nickname").val();
		var el = $(this).closest("div");
		
		loading(function(){
			$.ajax({
				url : "/ajax/account/forgotUserTelSet",
				type : "POST",
				data : { tel : tel, id : id, name : name },
				success : function(result){
					switch(result){
						case "success" :
							alert(tel + "로 인증번호가 전송되었습니다.");
							$(el).hide();
							$(".joinTelWrap > span").text("인증번호 (" + tel + ")");
							$(".joinTelWrap").show();
							break;
						default :
							alert(result);
							break;
					}
					
					loadingClose();
				}
			})
		})
	});
	
	$("#forgottelSuccessBtn").click(function(){
		var val = $("#usertelNum").val();
		
		loading(function(){
			$.ajax({
				url : "/ajax/account/forgotUserTelCheck",
				type : "POST",
				data : { val : val },
				success : function(result){
					switch(result){
						case "success" :
							alert("인증이 완료되었습니다.");
							$("#forgottelSuccessBtn").hide();
							$("#usertelNum").removeClass("btninput");
							$("#usertelNum").prop("readonly", true);
							forgottelcheck = true;
							break;
						case "fail" :
							alert("인증번호가 올바르지 않습니다.");
							break;
					}
					
					loadingClose();
				}
			})
		})
	});
	
	$("#forgotPWBtn").click(function(){
		if(!forgottelcheck){
			alert("연락처 인증 후 회원가입이 가능합니다.");
			return false;
		}
		
		var datas = $("#membersWrap").serialize();
		loading(function(){
			$.ajax({
				url : "/ajax/account/forgotPW",
				type : "POST",
				data : datas,
				success : function(result){
					if(result == "success"){
						alert("재설정된 비밀번호를 입력하신 연락처로 전송하였습니다.\n로그인 후 비밀번호를 재설정해주시길 바랍니다.");
						www("/");
					} else {
						alert("알 수 없는 이유로 비밀번호 재설정에 실패하였습니다.");
					}
					
					loadingClose();
				}
			})
		})
	});
	
});