<?php

# 메뉴 접근 권한설정
# 001(최고관리자) 002(관리자) 003(생산마스터)
# 004(팀마스터) 005(영업자)
$menuAuth = ["001", "002"];

# 공용 헤더 가져오기
include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";
?>
<style>
	.tit { margin-bottom: 5px; font-size:30px !important;}
	.tit::before{
		content: "";
		position: absolute;
		left: -2px;
		top: -5px;
		width: 30px;
		height: 4px;
		border-radius: 1px;
		background-color: #ffe618;
	}
	ul > li:first-child::before{
		content: "";
		position: absolute;
		left: 0;
		bottom: 7px;
		width: 63px;
		height: 5px;
		border-radius: 1px;
		background-color: #ffe618;
	}
	.txtBox{box-shadow: 0 15px 25px rgb(17 20 39 / 10%) !important; border:none !important;}
	ul {margin-bottom: 20px; width:100%; float:left;}
	ul > li {width:100%; float:left;}
	ul > li:first-child{z-index: 2; padding-bottom: 5px; font-weight: 600; font-size:17px; }
	ul > li:last-child{padding:15px 0px 5px 15px;}
	ul > li:last-child .txtBox{height:45px;}
	ul > li:last-child textarea{height:100px !important;}
	#popupWrap{height:100%;}
</style>
<div class="writeWrap">
	<div class="tit">미리보기<span style="width:100%;float:left; color:#cc3333; font-size:3px;">*) 아래 디자인은 실제 입력폼과 다를 수 있습니다.</span></div>
			<ul>
				<li><span>단문입력<span></li>
				<li  colspan="3"><input type="text" class="txtBox" name="status_name" placeholder="단문 입력란입니다."></li>
			</ul>
			<ul>
				<li><span>숫자입력<span></li>
				<li  colspan="3"><input type="text" class="txtBox" name="status_name" placeholder="숫자만 입력이가능합니다." numonly></li>
			</ul>
			<ul>
				<li><span>선택박스<span></li>
				<li  colspan="3">
					<select class="txtBox">
						<option value="">항목선택</option>
						<option value="선택항목1">선택항목1</option>
						<option value="선택항목2">선택항목2</option>
						<option value="선택항목3">선택항목3</option>
					</select>
				</li>
			</ul>
			<ul>
				<li><span>날짜선택<span></li>
				<li  colspan="3"><label for="date_input" class="date_icon" style="top:58%; left: 25px;"><i class="fas fa-calendar-alt"></i></label><input placeholder="날짜를 선택가능합니다." type="text" style="width: 220px !important;" class="txtBox date_input" id="date_input" dateonly></li>
			</ul>
			<ul>
				<li><span>단일선택<span></li>
				<li  colspan="3">
					<input type="radio" name='radio5' value="항목1" class="ainfo_1" id="radio5_1">
					<label class="radioBox" for="radio5_1">
						<i class="fas fa-check-circle on"></i>
						<i class="far fa-circle off"></i>
					</label>
					<label for="radio5_1">항목1</label>
					<input type="radio" name='radio5' value="항목2" class="ainfo_2" id="radio5_2">
					<label class="radioBox" for="radio5_2">
						<i class="fas fa-check-circle on"></i>
						<i class="far fa-circle off"></i>
					</label>
					<label for="radio5_2">항목2</label>
					<input type="radio" name='radio5' value="항목2" class="ainfo_2" id="radio5_3">
					<label class="radioBox" for="radio5_3">
						<i class="fas fa-check-circle on"></i>
						<i class="far fa-circle off"></i>
					</label>
					<label for="radio5_3">항목3</label>
					<input type="radio" name='radio5' value="항목2" class="ainfo_2" id="radio5_4">
					<label class="radioBox" for="radio5_4">
						<i class="fas fa-check-circle on"></i>
						<i class="far fa-circle off"></i>
					</label>
					<label for="radio5_4">항목4</label>
					<input type="radio" name='radio5' value="항목2" class="ainfo_2" id="radio5_5">
					<label class="radioBox" for="radio5_5">
						<i class="fas fa-check-circle on"></i>
						<i class="far fa-circle off"></i>
					</label>
					<label for="radio5_5">항목5</label>
				</li>
			</ul>
			<ul>
				<li><span>다중선택<span></li>
				<li  colspan="3">
					<input type="checkbox" name="cs_etc06[]" value="123" class="item_box" id="check6_1">
					<label class="checkBox" for="check6_1">
						<i class="fas fa-check-square on"></i>
						<i class="far fa-square off"></i>
					</label>
					<label for="check6_1">항목1</label>

					<input type="checkbox" name="cs_etc06[]" value="123123" class="item_box" id="check6_2">
					<label class="checkBox" for="check6_2">
						<i class="fas fa-check-square on"></i>
						<i class="far fa-square off"></i>
					</label>
					<label for="check6_2">항목2</label>

					<input type="checkbox" name="cs_etc06[]" value="1231232" class="item_box" id="check6_3">
					<label class="checkBox" for="check6_3">
						<i class="fas fa-check-square on"></i>
						<i class="far fa-square off"></i>
					</label>
					<label for="check6_3">항목3</label>
					
					<input type="checkbox" name="cs_etc06[]" value="1231232" class="item_box" id="check6_4">
					<label class="checkBox" for="check6_4">
						<i class="fas fa-check-square on"></i>
						<i class="far fa-square off"></i>
					</label>
					<label for="check6_4">항목4</label>
					<input type="checkbox" name="cs_etc06[]" value="1231232" class="item_box" id="check6_5">
					<label class="checkBox" for="check6_5">
						<i class="fas fa-check-square on"></i>
						<i class="far fa-square off"></i>
					</label>
					<label for="check6_5">항목5</label>
				</li>
			</ul>
			<ul>
				<li><span>장문입력<span></li>
				<li colspan="3"><textarea class="txtBox" placeholder="장문입력란입니다."></textarea></li>
			</ul>
</div>
<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>