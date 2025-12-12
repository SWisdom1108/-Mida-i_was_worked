<?php

	# 메뉴 접근 권한설정
	# 001(최고관리자) 002(관리자) 003(생산마스터)
	# 004(팀마스터) 005(영업자)
	$menuAuth = ["001", "002", "004", "005"];

	# 메뉴설정
	$secMenu = "log";
	
	# 콘텐츠설정
	$contentsTitle = "전송내역";
	$contentsInfo = "SMS 전송내역을 확인하실 수 있습니다.";

	# 콘텐츠 경로설정
	$contentsRoots = array();
	array_push($contentsRoots, "전송내역");

	# 공용 헤더 가져오기
	include_once "{$_SERVER['DOCUMENT_ROOT']}/include/header.php";

	# 추가 쿼리문
	$andQuery .= " AND use_yn = 'Y'";
	if($user["auth_code"] > 002){
		$andQuery .= " AND reg_idx = '{$user["idx"]}'";
	}

	# 검색값 정리
	search();

	# 페이징 정리
	paging("mt_sms_log");

?>
	
	<?php if($user["auth_code"] <= 002){ ?>
		<!-- 데이터 간단정리표 -->
		<div class="dataInfoSimpleWrap">
			<div>
				<div class="iconWrap">
					<i class="fas fa-chart-pie"></i>
				</div>
				<div class="conWrap">
					<ul class="dataCntList">
						<li>
							<span class="label">총 SMS 수량</span>
							<span class="value" id="smsTotalCnt">0</span>
						</li>
						<li>
							<span class="label">사용 수량</span>
							<span class="value" id="smsUseCnt">0</span>
						</li>
						<li>
							<span class="label">잔여 수량</span>
							<span class="value" id="smsFinishCnt">0</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>

	<!-- sms가이드 내용 설정 -->
	<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/smsGuide.php"; ?>

	<!-- 데이터 검색영역 -->
	<div class="searchWrap">
		<form method="get">
			<ul class="formWrap">
				<li>
					<span class="label">상세검색</span>
					<select class="txtBox" name="label">
						<option value="receive_name">수신자명</option>
						<option value="receive_tel">수신자연락처</option>
						<option value="send_name">발신자명</option>
						<option value="send_tel">발신자연락처</option>
						<option value="contents">내용</option>
						<option value="result_code">결과코드</option>
						<option value="result_msg">결과내용</option>
					</select>
					<input type="text" class="txtBox value" name="value" value="<?=$_GET['value']?>">
				</li>
				<li class="drag">
					<span class="label">조회기간</span>
					<input type="hidden" name="setDate" value="reg">
					<input type="text" class="txtBox s_date" name="s_date" value="<?=$_GET['s_date']?>" dateonly>
					<span class="hypen">~</span>
					<input type="text" class="txtBox e_date" name="e_date" value="<?=$_GET['e_date']?>" dateonly>
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
	
	<!-- 데이터 목록영역 -->
	<div class="listEtcWrap">
		<div class="left">
			<span class="cnt">TOTAL <?=number_format($totalCnt)?></span>
		</div>
		<div class="right">
			<select class="listSet" id="orderBy">
				<option value="order_by_date DESC">기본정렬</option>
				<option value="reg_date ASC">등록일시 오름차순</option>
				<option value="reg_date DESC">등록일시 내림차순</option>
			</select>
			<select class="listSet" id="listCnt">
				<option value="15">15개씩 보기</option>
				<option value="30">30개씩 보기</option>
				<option value="50">50개씩 보기</option>
				<option value="100">100개씩 보기</option>
				<option value="9999999">전체 보기</option>
			</select>
		</div>
	</div>
	
	<div class="listWrap">
		<table>
			<colgroup>
				<col width="4%">
				<col width="8%">
				<col width="9%">
				<col width="8%">
				<col width="9%">
				<col width="12%">
				<col width="22%">
				<col width="6%">
				<col width="10%">
				<col width="12%">
			</colgroup>
			<thead>
				<tr>
					<th rowspan="2">NO</th>
					<th colspan="2">수신정보</th>
					<th colspan="3">발신정보</th>
					<th rowspan="2">내용</th>
					<th colspan="2">전송결과</th>
					<th rowspan="2">등록일시</th>
				</tr>
				<tr>
					<th>이름</th>
					<th>연락처</th>
					<th>이름</th>
					<th>연락처</th>
					<th>발신일시</th>
					<th>결과코드</th>
					<th style="border-right: 1px solid #FFF;">결과내용</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$value = array(''=>'');
				$query = "
					SELECT MT.*
					FROM mt_sms_log MT
					{$andQuery}
					{$orderQuery}
					{$limitQuery}
				";
				$sql = list_pdo($query, $value);
				while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			?>
				<tr class="<?=($row["result_code"] == "-") ? "resultNo result_{$row["result_id"]}" : ""?>" data-idx="<?=$row["idx"]?>">
					<td class="lp05"><?=listNo()?></td>
					<td><?=dhtml($row['receive_name'])?></td>
					<td class="lp05"><?=$row['receive_tel']?></td>
					<td><?=dhtml($row['send_name'])?></td>
					<td class="lp05"><?=$row['send_tel']?></td>
					<td class="lp05 sendDate"><?=($row["send_date"]) ? $row["send_date"] : "-"?></td>
					<td class="lp05 tl"><?=dhtml($row["contents"])?></td>
					<td class="lp05 resultCode"><?=$row["result_code"]?></td>
					<td class="lp05 resultMsg"><?=dhtml($row["result_msg"])?></td>
					<td class="lp05"><?=$row["reg_date"]?></td>
				</tr>
			<?php } ?>

			<?php if(!$totalCnt){ ?>
				<tr>
					<td colspan="10">조회된 데이터가 존재하지 않습니다.</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	
	<!-- 페이징 -->
	<?=paging()?>
	
	<script type="text/javascript">
		$(function(){

			/* 결과 정리 */
			var idxs = [];
			var Item = $("tr.resultNo");
			for(var i = 0; i < $(Item).length; i++){
				idxs.push($(Item[i]).data("idx"));
			}

			if(idxs){
				$.ajax({
					url : "/ajax/sms/setLogResult",
					type : "POST",
					data : {
						idx : idxs
					},
					success : function(data){
						$.each(data, function(msg_id, val){
							var target = $(".result_" + msg_id);

							$(target).find(".sendDate").text(val["date"]);
							$(target).find(".resultCode").text(val["code"]);
							$(target).find(".resultMsg").text(val["msg"]);
						});
					}
				})
			}
			
			<?php if($user["auth_code"] <= 002){ ?>
				/* 잔여수량 체크 */
				$.ajax({
					url : "/ajax/sms/getSmsCnt",
					success : function(result){
						$("#smsTotalCnt").text(result.totalCnt);
						$("#smsUseCnt").text(result.useCnt);
						$("#smsFinishCnt").text(result.finishCnt);
					}
				});
			<?php } ?>

		});
	</script>

<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/footer.php"; ?>