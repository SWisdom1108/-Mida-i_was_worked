<?php

	# 메뉴설정
	$secMenu = "my";
	
	# 콘텐츠설정
	$contentsTitle = "나의 정보";
	$contentsInfo = "나의 기본정보를 수정하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "나의정보");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 보안카드정보
	$value = array(':m_idx'=>$user['idx']);
	$query = "SELECT * FROM mt_member_snum WHERE use_yn = 'Y' AND m_idx = :m_idx";
	$s_num = view_pdo($query, $value);

?>
	
	<div class="viewWrap">
		<div class="tit">기본정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="80%">
			</colgroup>
			<tbody>
				<tr>
					<th>아이디</th>
					<td class="lp05"><?=dhtml($user['m_id'])?></td>
				</tr>
					<tr>
						<th>비밀번호</th>
						<td><a href="/sub/my/myU" class="typeBtn btnGray01" title="비밀번호 재설정">비밀번호 재설정</a></td>
					</tr>
				<tr>
					<th>이름</th>
					<td><?=dhtml($user['m_name'])?></td>
				</tr>
				<tr>
					<th>연락처</th>
					<td class="lp05"><?=$user['m_tel']?></td>
				</tr>
				<tr>
					<th>이메일</th>
					<td class="lp05"><?=dhtml($user['m_mail'])?></td>
				</tr>
				<tr>
					<th>주소</th>
					<td class="lp05"><?=dhtml($user['m_addr'])?></td>
				</tr>
				<tr>
					<th>보안카드 사용여부</th>
					<td colspan="3" style="font-weight: bold; color: #<?=($user["snum_use_yn"] == "Y") ? "333" : "CCC"?>;">
						<?=($user["snum_use_yn"] == "Y") ? "사용중" : "미사용"?>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="tit">보안카드 정보</div>
		<table>
			<colgroup>
				<col width="20%">
				<col width="30%">
				<col width="20%">
				<col width="30%">
			</colgroup>
			<tbody>
				<?php if($s_num){ ?>
					<tr>
						<th>보안 카드<div class="miniGuideWrap" data-class="snumDonw"></div></th>
						<td colspan="3" class="lp05">
							<div id="mindScoreBody" style="float : left; padding : 10px;">
								<div onclick="downloadImage()" style="float: left; cursor: pointer; width : 800px; height: 400px; border-radius: 20px; overflow: hidden; background-color: #f7fbfe; margin: 5px;">
									<div style="float: left; width : 100%; text-align: center; height : 50px; line-height: 50px; font-size : 20px; font-weight: 900; color : #fff; background-color : #0783e3;">
										보안카드
									</div>
									<div style="float : left; width : 100%; padding : 25px 60px;">
										<?php 
											for ($i=1; $i <= 30; $i++) { 
												$ii = $i;
												if($i < 10){
													$ii = '0'.$ii;
												}
												$s_num_part1 = substr($s_num['s_num'.$ii], 0, 2); 
												$s_num_part2 = substr($s_num['s_num'.$ii], 2, 2); 

												$s_num_part1 = '**'; 
												$s_num_part2 = '**'; 
										?>
											<div style="float : left; width : 20%; border-left: 1px solid #ddd; <?= $i > 25 ? "border-bottom : 1px solid #ddd;" :  "" ?> <?= $i % 5 == 0 ? "border-right : 1px solid #ddd;" :  "" ?>">
												<div style="border-top : 1px solid #ddd; float : left; width : 35%; height : 47px; line-height: 47px; text-align : center; font-size: 15px; background-color : #cde6f9; color : #333; font-weight : 500;">
													<?=$i?>
												</div>
												<div style="border-top : 1px solid #ddd; float : left; width : 65%; height : 47px; line-height: 47px; text-align : center; font-size: 15px; background-color : #fff; color : #333; font-weight : 500;">
													<?=$s_num_part1?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$s_num_part2?>
												</div>
											</div>	
											
										<?php }?>
									</div>
									<div style="float : left; width : 100%; padding :0px 60px; font-size: 15px; margin-top: -17px; color : #e34141; font-weight : 500;">
										*타인에게 절대 공유하지 마세요.
									</div>
								</div>
							</div>
							<div id="container" style=" float: left; width: 0; height: 0; overflow: hidden;"></div>
							<div style="float : left; width : 600px; padding : 0px 20px;">
								<input type="password" name="password" id="password" placeholder="파일을 다운로드 받으시려면 본인 계정의 비밀번호를 입력해주세요." class="txtBox">
								<div style="float : left;">
									<button type="button" class="typeBtn btnOrange snumDonw" onclick="downloadImage()" style="margin-top: 10px;"><i class="fas fa-clipboard-check"></i>다운로드</button>
								</div>
							</div>
						</td>
					</tr>
				<?php } else { ?>
					<tr>
						<th>보안카드 발급받기</th>
						<td colspan="3">
							<button type="button" class="typeBtn btnOrange getSnumBtn"><i class="fas fa-clipboard-check"></i>보안카드 발급</button>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div class="dataBtnWrap">
		<div class="left">
		</div>
		<div class="right">
			<a href="/sub/my/myU" class="typeBtn btnMain" title="수정"><i class="fas fa-edit"></i>수정</a> 
		</div>
	</div>

	<?php if(!$s_num){ ?>
		<script type="text/javascript">
			$(function(){
				
				$(".getSnumBtn").click(function(){
					$("#loadingWrap").fadeIn(350, function(){
						$.ajax({
							url : "/ajax/api/snum",
							type : "POST",
							data : {
								idx : "<?=$user['idx']?>"
							},
							success : function(){
								window.location.reload();
							}
						})
					});
				});
				
			})
		</script>
	<?php } ?>

	<script src="/js/html2canvas.min.js"></script>
	<script>
		function downloadSecurityCard() {
	        var xhr = new XMLHttpRequest();
	        xhr.open('POST', '/ajax/api/snum_down.php', true);  // POST 방식으로 요청을 엽니다
	        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');  // 요청 헤더 설정
	        
	        // POST 요청의 본문에 전송할 데이터 설정
	        var postData = 'param1=value1&param2=value2';  // 필요한 데이터를 여기에 설정
	        
	        xhr.onreadystatechange = function() {
	            if (xhr.readyState == 4 && xhr.status == 200) {
	                // 응답받은 HTML을 'container'에 삽입
	                document.getElementById('container').innerHTML = xhr.responseText;
	                
	                // 삽입된 HTML에서 실제 캡처할 요소를 찾음
	                var divToCapture = document.querySelector('#container #securityCard');

	                // divToCapture가 null이 아닌지 확인
	                if (divToCapture) {
	                    // 삽입된 div 캡처하여 이미지로 변환
	                    html2canvas(divToCapture).then(function (canvas) {
	                        var imageData = canvas.toDataURL('image/jpeg');
	                        var link = document.createElement('a');
	                        link.href = imageData;
	                        link.download = 'SecurityCard.jpg';
	                        link.click();
	                        $("#securityCard").remove();
	                    });
	                } else {
	                    console.error('Error: #securityCard not found in the container.');
	                }
	            }
	        };
	        
	        // POST 요청의 본문에 데이터 전송
	        xhr.send(postData);
	    }
		function downloadImage() {
		  	var password = $("#password").val();
		  	if(!password){
		  		alert('비밀번호를 입력해주세요!');
		  		return false;
		  	}

		  	if(confirm("보안카드를 다운로드 받으시겠습니까?")){
		  	}else{
		  		return false;
		  	}

		  	$.ajax({
				url : "/ajax/api/pass_check",
				type : "POST",
				data : {
					password : password
				},
				success : function(result){
					if(result == "success"){
						downloadSecurityCard();
					}else{
						alert(result);
						return false;
					}
				}
			})		   
		}

		$("#password").keyup(function(key){
			if(key.keyCode == 13){
				downloadImage();
			}
		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>