<?php
# 메뉴 접근 권한설정
# 001(최고관리자) 002(관리자) 003(생산마스터)
# 004(팀마스터) 005(영업자)
$menuAuth = ["001", "002", "004", "005", "006", "007"];

# 공용 헤더 가져오기
include_once "{$_SERVER['DOCUMENT_ROOT']}/include/headerPopup.php";

# 권한에 따른 추가 쿼리문
switch ($user['auth_code']) {
	case "004":
		$andQuery .= "";
		break;
	case "005":
		$andQuery .= "";
		break;
	case "006":
		$andQuerys .= " AND md_idx = '{$user['idx']}'";
		break;
	case "007":
		$andQuerys .= " AND dr_idx = '{$user['idx']}'";
		break;
}

# 데이터 정보추출
$value = array(':use_yn' => 'Y', ':chart_num' => $_GET['chart_num']);
$query = "		
		SELECT MT.*
		FROM mt_db_dent MT
		WHERE use_yn = :use_yn
		AND chart_num = :chart_num
		{$andQuery}
	";
$view = view_pdo($query, $value);

if (!$view) {
	www("/sub/error/popup");
     exit;
}

$value = array('' => '');
$query = "SELECT idx, m_name FROM mt_member WHERE use_yn = 'Y' AND auth_code = '006' ORDER BY idx ASC";
$mdList = list_pdo($query, $value);

$value = array('' => '');
$query = "SELECT idx, m_name FROM mt_member WHERE use_yn = 'Y' AND auth_code = '007' ORDER BY idx ASC";
$drList = list_pdo($query, $value);

$value = array('' => '');
$query = "SELECT treat_code, treat_name FROM mc_treatment_code WHERE use_yn = 'Y' ORDER BY treat_code ASC";
$treat_code = list_pdo($query, $value);

$value = array(':chart_num'=>$_GET['chart_num']);
$query = "
	SELECT SUM(pay) AS total_pay from mt_pay_log
	WHERE chart_num = :chart_num
	AND use_yn = 'Y'
	{$andQuerys}
";
$total_pay = view_pdo($query, $value)['total_pay'];
$total_pay = $total_pay ? number_format($total_pay) : 0;

if($_GET["md_idx2"]) {
	$andQuerys .= " AND md_idx = '{$_GET["md_idx2"]}'";
}


if($_GET['dr_idx2']) {
	$andQuerys .= " AND dr_idx = {$_GET['dr_idx2']}";
}
  
if($_GET['treat_code']) {
	$andQuerys .= " AND treat_code = {$_GET['treat_code']}";
}

if($_GET['s_date']){
	$startDate = ($_GET['s_date']) ? $_GET['s_date'] : date("Y-m-d");
	$andQuerys .= " AND date_format({$_GET['setDate']}_date, '%Y-%m-%d') >= date_format('{$startDate}', '%Y-%m-%d')";
}

if($_GET['e_date']){
	$endDate = ($_GET['e_date']) ? $_GET['e_date'] : date("Y-m-d");
	$andQuerys .= " AND date_format({$_GET['setDate']}_date, '%Y-%m-%d') <= date_format('{$endDate}', '%Y-%m-%d')";
}
# 상담기록

$value = array(':use_yn' => 'Y', ':chart_num' => "{$view['chart_num']}");
$query = "
		SELECT COUNT(*) as totalCnt
		FROM mt_pay_log MT
		WHERE use_yn = :use_yn
		AND chart_num = :chart_num
        {$andQuerys}
		ORDER BY idx DESC
	";
$totalCnt = view_pdo($query, $value)['totalCnt'];


$query = "
		SELECT MT.*
		FROM mt_pay_log MT
		WHERE use_yn = :use_yn
		AND chart_num = :chart_num
		{$andQuerys}
		ORDER BY idx DESC
	";
$cs = list_pdo($query, $value);


# 오늘일자
$year = date("Y");
$month = date("m");
$day = date("d");

# 수정삭제 허용 권한
$access_auth = ['001','002','006'];
?>

<div class="dbCheckDataInfoWrap">
	<div class="titWrap">
		<div class="left">
		</div>
		<div class="right">
		</div>
	</div>

	<div class="infoWrap">
		<ul>
			<li>
				<span class="label">이름</span>
				<span class="value"><?= $view['cs_name'] ?></span>
			</li>
			<li>
				<span class="label">연락처</span>
				<span class="value lp05"><?= $view['cs_tel'] ?></span>
			</li>
            <li>
				<span class="label">차트번호</span>
				<span class="value lp05"><?= $view['chart_num'] ?></span>
			</li>
			<li>
				<span class="label">총 금액</span>
				<span class="value lp05"><?= $total_pay ?></span>
			</li>
		</ul>
	</div>
</div>

<div class="writeWrap">
	<div class="searchWrap" style="margin: 50px 0px;">
		<form method="get">
			<input type="hidden" name="chart_num" value="<?=$_GET['chart_num']?>">
			<ul class="formWrap">
				<li class="drag">
					<span class="label">수납일</span>
					<input type="hidden" name="setDate" value="pay">
					<input type="text" class="txtBox s_date" name="s_date" value="<?=$startDate?>" dateonly>
					<span class="hypen">~</span>
					<input type="text" class="txtBox e_date" name="e_date" value="<?=$endDate?>" dateonly>
					<span class="dateBtn" data-s="<?=date("Y-m-d")?>" data-e="<?=date("Y-m-d")?>">오늘</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 7 days"))?>" data-e="<?=date("Y-m-d")?>">7일</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 1 month"))?>" data-e="<?=date("Y-m-d")?>">1개월</span>
					<span class="dateBtn" data-s="<?=date("Y-m-d", strtotime("- 3 month"))?>" data-e="<?=date("Y-m-d")?>">3개월</span>
				</li>
			</ul>
			<div class="btnWrap">
				<button type="submit" class="typeBtn">조회</button>
			</div>
		</form>
	</div>

	<div class="listWrap">
        <div class="listEtcWrap">
	    	<div class="left">
	    		<select class="listSet" id="md_idx2" style="width: 130px;">
	    			<option value="">실장명</option>
	    			<?php while ($row = $mdList->fetch(PDO::FETCH_ASSOC)) { ?>
	    				<option value="<?= $row["idx"] ?>" <?= ($_GET["md_idx2"] == $row["idx"]) ? "selected" : "" ?>><?= dhtml($row["m_name"]) ?></option>
	    			<?php } ?>
	    		</select>
                <select class="listSet" id="dr_idx2" style="width: 130px; margin-left:5px;">
	    			<option value="">닥터명</option>
	    			<?php while ($row = $drList->fetch(PDO::FETCH_ASSOC)) { ?>
	    				<option value="<?= $row["idx"] ?>" <?= ($_GET["dr_idx2"] == $row["idx"]) ? "selected" : "" ?>><?= dhtml($row["m_name"]) ?></option>
	    			<?php } ?>
	    		</select>
				<select class="listSet" id="treat_code" style="width: 130px; margin-left:5px;">
	    			<option value="">진료항목</option>
	    			<?php while ($row = $treat_code->fetch(PDO::FETCH_ASSOC)) { ?>
	    				<option value="<?= $row["treat_code"] ?>" <?= ($_GET["treat_code"] == $row["treat_code"]) ? "selected" : "" ?>><?= dhtml($row["treat_name"]) ?></option>
	    			<?php } ?>
	    		</select>
	    	</div>
	    </div>
		<div></div>
		<table>
			<colgroup>
				<col width="5%">
				<col width="12%">
				<col width="20%">
				<col width="10%">
				<col width="10%">
				<col width="10%">
				<?php if(in_array($user['auth_code'], $access_auth)){?>
				<col width="5%">
				<col width="5%">
				<?php }?>
			</colgroup>
			<thead>
				<tr>
					<th>NO</th>
					<th>진료항목</th>
					<th>금액</th>
					<th>실장명</th>
					<th>닥터명</th>
					<th>수납일</th>
					<?php if(in_array($user['auth_code'], $access_auth)){?>
					<th>수정</th>
					<th>삭제</th>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php if (!$totalCnt) { ?>
					<tr>
						<td <?php if (in_array($user['auth_code'], $access_auth)) { ?> colspan="8" <?php } else { ?> colspan="6" <?php } ?>>등록된 금액기록이 존재하지 않습니다.</td>
					</tr>
				<?php } ?>
				<?php while ($row = $cs->fetch(PDO::FETCH_ASSOC)) { ?>
					<tr>
						<td class="lp05"><?= $totalCnt--; ?></td>
						<td class="lp05">
							<?php
								$value = array(':treat_code' => $row['treat_code']);
								$query = "
									SELECT treat_name FROM mc_treatment_code
									WHERE treat_code = :treat_code 
								";
								$treat_name = view_pdo($query,$value)['treat_name'];
							?>
							<span class="treat">
								<?= $treat_name?>
							</span>
							<select class="txtBox treat_edit" style="display: none;">
								<?php 
								$value = array('' => '');
								$query = "SELECT treat_code, treat_name FROM mc_treatment_code WHERE use_yn = 'Y' ORDER BY treat_code ASC";
								$treatList = list_pdo($query, $value);
								while ($t = $treatList->fetch(PDO::FETCH_ASSOC)) { ?>
									<option value="<?= $t['treat_code'] ?>" <?= ($row['treat_code'] == $t['treat_code']) ? 'selected' : '' ?>>
										<?= $t['treat_name'] ?>
									</option>
								<?php } ?>
							</select >
						</td>
						<td class="lp05" style="text-align: left;">
							<div class="pay">
							<?=number_format($row['pay'])?>
							</div>
							<input type="text" class="txtBox pay_edit" placeholder="금액 입력" value="<?=number_format($row['pay'])?>" style='width : 93%; display: none;' numberonly>
							<div style="float: right; cursor : pointer; display: none; line-height: 35px; margin-right: 2px;" class="update_pay" data-idx="<?=$row['idx']?>">
								<i class="fas fa-save"></i>
							</div>
						</td>
						<td class="lp05">
							<?php
							if($row['md_idx']){
								$value = array(':md_idx' => $row['md_idx']);
								$query = "
									SELECT m_name FROM mt_member
									WHERE idx = :md_idx 
								";
								$md_name = view_pdo($query,$value)['m_name'];
							?>
							<span class="md_name">
								<?=$md_name?>
							</span>
							<?php }else{?>
							<span class="md_name">
								-
							</span>
							<?php }?>
							<select class="txtBox md_edit" style="display: none; min-width:120px;">
								<option value="" >실장명</option>
								<?php
								$value = array(''=>'');
								$query = "
									SELECT idx, m_name FROM mt_member
									WHERE use_yn = 'Y'
									AND auth_code = '006'
								";
								$md_info = list_pdo($query,$value);

								while($md = $md_info->fetch(PDO::FETCH_ASSOC)){
								?>
								<option value="<?=$md['idx']?>" <?=($md['idx'] == $row['md_idx']) ? 'selected' : ''?>><?=$md['m_name']?></option>
								<?php }?>
							</select>
						</td>
						<td class="lp05">
							<?php
							if($row['dr_idx']){
								$value = array(':dr_idx' => $row['dr_idx']);
								$query = "
									SELECT m_name FROM mt_member
									WHERE idx = :dr_idx 
								";
								$dr_name = view_pdo($query,$value)['m_name'];
							?>
							<span class="dr_name">
								<?=$dr_name?>
							</span>
							<?php }else{?>
							<span class="dr_name">
								-
							</span>
							<?php }?>
							<select class="txtBox dr_edit" style="display: none; min-width:120px;">
								<option value="">닥터명</option>
								<?php
								$value = array(''=>'');
								$query = "
									SELECT idx, m_name FROM mt_member
									WHERE use_yn = 'Y'
									AND auth_code = '007'
								";
								$md_info = list_pdo($query,$value);

								while($md = $md_info->fetch(PDO::FETCH_ASSOC)){
								?>
								<option value="<?=$md['idx']?>" <?=($md['idx'] == $row['dr_idx']) ? 'selected' : ''?>><?=$md['m_name']?></option>
								<?php }?>
							</select>
						</td>
						<td class="lp05"><?= $row['pay_date'] ?></td>
						<?php if(in_array($user['auth_code'], $access_auth)){?>
						<td class="lp05">
							<div style="cursor : pointer;" class="edit_btn">
								<i class="fas fa-edit"></i>
							</div>
							<div style="cursor: pointer; display: none;" class="save_btn" data-idx="<?= $row['idx'] ?>">
								<i class="fas fa-save"></i>
							</div>
						</td>
						<td class="lp05"><i style="cursor: pointer;" data-idx="<?= $row['idx'] ?>" class="far fa-times-circle trash_go"></i></td>
						<?php }?>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>

<div id="popupBtnWrap">
	<button type="button" class="typeBtn btnGray02 popupCloseBtn" data-target="payLogL">닫기</button>
</div>

<script type="text/javascript">
	$(function() {

		$(".trash_go").click(function() {
			var idx = $(this).data("idx");
			if (confirm("금액내역을 삭제하시겠습니까?")) {
				$("#loadingWrap").fadeIn(350, function() {
					$.ajax({
						url: "/ajax/db/payLogDP",
						type: "POST",
						data: {
							idx: idx
						},
						success: function(result) {
							switch (result) {
								case "success":
									alert("금액내역 삭제를 완료하였습니다.");
									window.location.reload();
									break;
								case "fail":
									alert("알 수 없는 이유로 삭제를 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default:
									alert(result);
									$("#loadingWrap").fadeOut(350);
									break;
							}
						}
					});
				});
			}
		});

		$(".edit_btn").click(function(){
			var row = $(this).closest("tr");
			row.find(".pay, .treat, .md_name, .dr_name").hide();
			row.find(".pay_edit, .treat_edit, .md_edit, .dr_edit").show();

			$(this).hide();
			row.find(".save_btn").show();
		});

		$(".save_btn").click(function() {
			var row = $(this).closest("tr");
			var idx = $(this).data("idx");
			
			var treat_code = row.find(".treat_edit").val();
			var pay = row.find(".pay_edit").val().replace(/[^0-9]/g, '');
			var md_idx = row.find(".md_edit").val();
			var dr_idx = row.find(".dr_edit").val();


			if(!pay){
				alert("금액을 입력해주세요.");
				return false;
			}
			
			if(confirm("수정하시겠습니까?")) {
				$("#loadingWrap").fadeIn(350, function() {
					$.ajax({
						url: "/ajax/db/payLogUP",
						type: "POST",
						data: {
							idx: idx,
							treat_code: treat_code,
							pay: pay,
							md_idx: md_idx,
							dr_idx: dr_idx
						},
						success: function(result) {
							switch(result) {
								case "success":
									alert("수정이 완료되었습니다.");
									window.location.reload();
									break;
								case "fail":
									alert("알 수 없는 이유로 수정에 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default:
									alert(result);
									$("#loadingWrap").fadeOut(350);
									break;
							}
						},
						error: function() {
							alert("수정 중 오류가 발생했습니다.");
							$("#loadingWrap").fadeOut(350);
						}
					});
				});
			}
		});

		$(".update_pay2").click(function(){
			var idx = $(this).data("idx");
			var memo = $(this).parent().find("input").val();
			var status_code = $(this).parent().siblings().find('.statusChange_a').val();
			if(confirm("내용을 수정하시겠습니까?")){
				$("#loadingWrap").fadeIn(350, function(){
					$.ajax({
						url : "/ajax/db/csLogUP",
						type : "POST",
						data : {
							idx : idx,
							memo : memo,
							status_code : status_code
						},
						success : function(result){
							switch(result){
								case "success" :
									alert("내용을 수정하였습니다.");
									window.location.reload();
									break;
								case "fail" :
									alert("알 수 없는 이유로 수정에 실패하였습니다.");
									$("#loadingWrap").fadeOut(350);
									break;
								default :
									alert(result);
									console.log(result);
									$("#loadingWrap").fadeOut(350);
									break;
							}
						}
					});
				});
			}else{
				$(this).parent().find("span").css("display","inline-block");
				$(this).css("display","none");
				$(this).parent().find(".edit_memo").css("display","block");
				$(this).parent().find("input").css("display","none");
			}
		});

	});
</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footerPopup.php"; ?>