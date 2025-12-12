<?php

	# 그래프 시작일 종료일
	$startDate = date("Y-m-d", strtotime("- 7 days"));
	$endDate = date("Y-m-d");

?>
<style>

	#mainContentsWrap { background-color: #F8F8F8; }
	#mainContentsWrap > .contentsWrap { padding: 30px; padding-bottom: 160px; }
	
</style>

<div class="dashboardWrap">
	<div class="dashboard_left">
		<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/dashboard/{$user['auth_code']}/left.php"; ?>
	</div>
	
	<div class="dashboard_right">
		<div class="dash_section my_info">
			<div class="top">
				<i class="fas fa-user-circle userIcon"></i>
				<span class="my_id"><?=$user['m_id']?> <a href="/sub/my/myV.php"><i class="far fa-edit"></i></a></span>
			</div>
			<div class="bottom">
				<table>
					<tbody>
						<tr>
							<th>- 이름</th>
							<td><?=$user['m_name']?>(<?=$user['auth_name']?>)</td>
						</tr>
						<tr>
							<th>- 가입일시</th>
							<td><?=date("Y년 m월 d일 H시 i분", strtotime($user['reg_date']))?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<div class="dash_section notice">
			<span class="section_tit">공지사항<a href="/sub/bbs/bbs?bbs=001&inc=L"><i class="far fa-plus-square"></i></a></span>
			<div class="section_con">
				<table>
					<colspan>
						<col width="72%">
						<col width="18%">
					</colspan>
					<tbody>
					<?php
						$value = array(':use_yn'=>'Y',':bbs_code'=>'001');
						$query = "
							SELECT MT.*
							FROM mt_bbs MT
							WHERE use_yn = :use_yn
							AND bbs_code = :bbs_code
							ORDER BY idx DESC
							LIMIT 0, 5
						";
						$result = list_pdo($query, $value);
						while($row = $result->fetch(PDO::FETCH_ASSOC)){
					?>
						<tr>
							<td class="tit"><a href="/sub/bbs/bbs?bbs=001&inc=V&idx=<?=$row['idx']?>"><?=($row['noti_yn'] == "Y") ? '<b class="notis">공지</b>' : ""?><?=$row['title']?></a></td>
							<td class="date"><?=date("Y-m-d", strtotime($row['reg_date']))?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
		
		<?php include_once "{$_SERVER['DOCUMENT_ROOT']}/include/dashboard/{$user['auth_code']}/right.php"; ?>
	</div>
</div>