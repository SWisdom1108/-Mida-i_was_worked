<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php";

	# 보안카드정보
	$value = array(':m_idx'=>$user['idx']);
	$query = "SELECT * FROM mt_member_snum WHERE use_yn = 'Y' AND m_idx = :m_idx";
	$s_num = view_pdo($query, $value);
	if(!$user['idx']){
		die("불량 접속");
	}

 ?>


<div id="securityCard" style="float: left; cursor: pointer; width : 800px; height: 400px; border-radius: 20px; overflow: hidden; background-color: #f7fbfe; margin: 5px;">
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