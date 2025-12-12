<?php

	$secMenuList = array();
	$trdMenuList = array();

	$mainMenuIcon = '<i class="fas fa-tachometer-alt"></i>';
	$mainMenuName = '대시보드';

	function makeTree($paths, $company_data){
		$tree = array();
		foreach ($paths as $path){
			$current = &$tree;
			$pathLength = count($path);
			foreach ($path as $index => $name){
				$name = trim($name);
				if ($name == '') break;
				if (!isset($current[$name])){
					$current[$name] = array();
				}
				if($index == $pathLength - 1){
                	$pathKey = implode('|', array_slice($path, 0, $index + 1));
                	if(isset($company_data[$pathKey])){
                	    $current[$name]['__company__'] = $company_data[$pathKey];
                	}
            	}

				$current = &$current[$name];
			}
			unset($current);
		}
		return $tree;
	}

	# mainMenu 변수에 따른 서브메뉴 설정
	switch($mainMenu){
		case "error" :
			$mainMenuIcon = '<i class="fas fa-bomb"></i>';
			$mainMenuName = 'ERROR';
			
			array_push($secMenuList, "404 ERROR@404");
			break;
		case "my" :
			$mainMenuIcon = '<i class="fas fa-cog"></i>';
			$mainMenuName = '기본설정';
			
			array_push($secMenuList, "나의정보@my@myV");
			array_push($trdMenuList, []);
			
			array_push($secMenuList, "회사정보@company@companyV");
			array_push($trdMenuList, []);
			
			switch($user['auth_code']){
				case "001" : # 최고관리자일 경우 
					array_push($secMenuList, "테마설정@theme@themeU");
					array_push($trdMenuList, []);
					
					array_push($secMenuList, "SMS설정@sms@smsSettingV");
					array_push($trdMenuList, ["SMS설정@setting@smsSettingV", "템플릿관리@template@smsTemplateL", "발신번호요청@request@smsRequestL"]);
					
					array_push($secMenuList, "일정설정@schedule@scheduleTypeL");
					array_push($trdMenuList, ["구분설정@type@scheduleTypeL"]);
					
					array_push($secMenuList, "보안설정@block@blockL");
					array_push($trdMenuList, ["로그인차단목록@block@blockL", "보안카드목록@snum@snumL", "블랙리스트목록@black@blackL", "허용IP목록@permit@permitL","차단IP목록@ipblock@ipblockL"]);
					break;					


				case "002" : # 관리자일 경우 
					array_push($secMenuList, "테마설정@theme@themeU");
					array_push($trdMenuList, []);
					
					array_push($secMenuList, "SMS설정@sms@smsSettingV");
					array_push($trdMenuList, ["SMS설정@setting@smsSettingV", "템플릿관리@template@smsTemplateL", "발신번호요청@request@smsRequestL"]);
					
					array_push($secMenuList, "일정설정@schedule@scheduleTypeL");
					array_push($trdMenuList, ["구분설정@type@scheduleTypeL"]);
					
					break;
			}
			
			break;
		case "db_setting" :
			$mainMenuIcon = '<i class="fas fa-user-cog"></i>';
			$mainMenuName = 'DB관리설정';
			
			array_push($secMenuList, "DB관리항목@column@columnU");
			array_push($trdMenuList, []);

			array_push($secMenuList, "DB고객등급@grade@dbGradeL");
			array_push($trdMenuList, []);
			
			array_push($secMenuList, "상담구분값설정@csStatus@csStatusL");
			array_push($trdMenuList, []);
			
			array_push($secMenuList, "DB분배설정@dist@distL");
			array_push($trdMenuList, []);
			
			break;
		case "group" :
			$mainMenuIcon = '<i class="fas fa-sitemap"></i>';
			$mainMenuName = '조직관리';
			
			switch($user['auth_code']){
				case "001" : # 최고관리자일 경우 
				case "002" : # 관리자일 경우 
					if($user["auth_code"] == "001"){
						array_push($secMenuList, "관리자설정@admin@adminL");
						array_push($trdMenuList, []);
					}
					
					array_push($secMenuList, "생산업체관리@pm@pmL");
					array_push($trdMenuList, ['생산업체관리@pm@pmL','카테고리등록@pmCategory@pmCategoryL']);

					array_push($secMenuList, "사용자관리@team@teamL");
					array_push($trdMenuList, ["{$customLabel["tm"]}관리@team@teamL", "{$customLabel["fc"]}관리@teamMember@teamMemberL", "실장관리@doctorManager@doctorManagerL", "닥터관리@doctor@doctorL"]);

					break;
				case "004" : # 팀마스터일 경우 
					array_push($secMenuList, "{$customLabel["fc"]}관리@myTeamMember@myTeamMemberL");
					array_push($trdMenuList, []);
					
					break;
			}
			
			break;
		case "db" :
			$mainMenuIcon = '<i class="fas fa-id-card"></i>';
			$mainMenuName = 'DB관리';
			
			switch($user['auth_code']){
				case "001" : # 최고관리자일 경우 
				case "002" : # 관리자일 경우 
					// $thisTotalCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001'")["cnt"];
					// $thisTotalCnt = ($thisTotalCnt) ? "<span class='cnt'>{$thisTotalCnt}</span>" : "";
					
					// array_push($secMenuList, "DB통합관리{$thisTotalCnt}@dbAll@dbAllL");
					// $trdMenuList_r = [];
					
					// array_push($trdMenuList_r, "전체보기{$thisTotalCnt}@all@dbAllL");
					// $value = array(':auth_code'=>'003');
					// $query = "SELECT * FROM mt_member_cmpy WHERE auth_code = :auth_code ORDER BY use_yn DESC, idx DESC";
					// $sql = list_pdo($query, $value);
					// while($row = $sql->fetch(PDO::FETCH_ASSOC)){
					// 	$row['company_name'] = dhtml($row['company_name']);
					// 	if($secMenu == 'dbAll'){
					// 		$thisTotalCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '001' AND pm_code = '{$row["idx"]}'")["cnt"];
					// 		$thisTotalCnt = ($thisTotalCnt) ? "<span class='cnt'>{$thisTotalCnt}</span>" : "";
					// 	}else{
					// 		$thisTotalCnt = '';
					// 	}
					// 	$styleleft = ($row['use_yn']=="N") ? "<span style='color:#000; opacity: 0.40;'>"  : "";
					// 	$styleright = ($row['use_yn']=="N") ? "<i class='far fa-window-close' style='margin-right:20px; line-height:52px; float:right;'></i></span>" : "";
					// 	array_push($trdMenuList_r, "{$styleleft}{$row['company_name']}{$styleright}{$thisTotalCnt}@db{$row['idx']}@dbAllL?code={$row['idx']}");
					// }
					// array_push($trdMenuList, $trdMenuList_r);

					array_push($secMenuList, "DB통합관리@dbAll@dbAllL");
					$trdMenuList_r = [];
					array_push($trdMenuList_r, "전체보기@all@dbAllL");

					$query = "SELECT 
							    mc.*,
							    mmc1.category_name AS depth1_name,
							    mmc2.category_name AS depth2_name,
							    mmc3.category_name AS depth3_name,
							    mmc4.category_name AS depth4_name,
							    mmc5.category_name AS depth5_name
							FROM mt_member_cmpy AS mc
							LEFT JOIN mc_member_cmpy_category AS mmc1 
								ON mc.depth1 = mmc1.category_code AND mmc1.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc2 
							    ON mc.depth2 = mmc2.category_code AND mmc2.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc3 
							    ON mc.depth3 = mmc3.category_code AND mmc3.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc4 
							    ON mc.depth4 = mmc4.category_code AND mmc4.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc5 
							    ON mc.depth5 = mmc5.category_code AND mmc5.use_yn = 'Y'
							WHERE mc.use_yn = 'Y'
							  AND mc.auth_code = '003';
					";
					$value = array();
					$result_menu = list_pdo($query, $value);

					$company_data = array();
					while($row = $result_menu->fetch(PDO::FETCH_ASSOC)){
						$path = array();

						$company_name = $row['company_name'];
						if($row["depth1_name"]){
							$path[] = $row["depth1_name"];
						}
						if($row["depth2_name"]){
							$path[] = $row["depth2_name"];
						}
						if($row["depth3_name"]){
							$path[] = $row["depth3_name"];
						}
						if($row["depth4_name"]){
							$path[] = $row["depth4_name"];
						}
						if($row["depth5_name"]){
							$path[] = $row["depth5_name"];
						}

						if(empty($path)){
    					    $no_depth_list[] = [
        					    'company_name' => $company_name,
        					    'url' => "dbAllL?code={$row['idx']}",
								'use_yn' => $row['use_yn']
        					];
        					continue;
    					}
						$pathKey = implode('|', $path);
						$depth_data[] = $path;
						$company_data[$pathKey][] = array(
							'company_name' => $company_name,
							'url' =>  "dbAllL?code={$row['idx']}",
							'idx' => $row['idx']
						);
					}
					$depthTree = makeTree($depth_data, $company_data);
					$depthTreeJson = json_encode($depthTree, JSON_UNESCAPED_UNICODE);
    				$companyDataJson = json_encode($company_data, JSON_UNESCAPED_UNICODE);


					foreach($no_depth_list as $row) {
						
    				    $styleleft = ($row['use_yn']=="N") ? "<span style='color:#000; opacity: 0.40;'>"  : "";
						$styleright = ($row['use_yn']=="N") ? "<i class='far fa-window-close' style='margin-right:20px; line-height:52px; float:right;'></i></span>" : "";

    				    $companyName = dhtml($row['company_name']);
    				    $url = "dbAllL?code={$row['idx']}";
					
    				    array_push($trdMenuList_r, "{$styleleft}{$companyName}{$styleright}@db{$row['idx']}@{$row['url']}");
    				}
    				array_push($trdMenuList, $trdMenuList_r);
					
					
						
						array_push($secMenuList, "DB분배관리@dbTeam@dbTeamL");
						$trdMenuList_r = [];
						
						array_push($trdMenuList_r, "전체보기@all@dbTeamL");
						$value = array(':use_yn'=>'Y');
						$query = "SELECT * FROM mt_member_team WHERE use_yn = :use_yn ORDER BY idx DESC";
						$sql = list_pdo($query, $value);
						while($row = $sql->fetch(PDO::FETCH_ASSOC)){
							$row['team_name'] = dhtml($row['team_name']);
							array_push($trdMenuList_r, "{$row['team_name']}@tm{$row['idx']}@dbTeamL?code={$row['idx']}");
						}
						array_push($trdMenuList, $trdMenuList_r);

					array_push($secMenuList, "금액업로드@dbpay@dbPayL");
					
					array_push($secMenuList, "휴지통@trash@trashAllL");
					array_push($trdMenuList, []);
					break;
				case "003" : # 생산마스터일 경우 
					array_push($secMenuList, "DB통합관리@dbDist@dbDistL");
					array_push($trdMenuList, []);
					
					array_push($secMenuList, "휴지통@trash@trashDistL");
					array_push($trdMenuList, []);
					break;
				case "004" : # 팀마스터일 경우 
					array_push($secMenuList, "DB분배관리@dbMyTeam@dbMyTeamL");
					$trdMenuList_r = [];
					
					array_push($trdMenuList_r, "전체보기@all@dbMyTeamL");
					$value = array(':use_yn'=>'Y',':tm_code'=>$user['tm_code'],':auth_code'=>'005');
					$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND tm_code = :tm_code AND auth_code = :auth_code ORDER BY idx DESC";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$row['m_name'] = dhtml($row['m_name']);
						array_push($trdMenuList_r, "{$row['m_name']}@fc{$row['idx']}@dbMyTeamL?code={$row['idx']}");
					}
					array_push($trdMenuList, $trdMenuList_r);
					
					$thisTotalCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '002' AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}' AND check_yn = 'N'")["cnt"];
					$thisTotalCnt = ($thisTotalCnt) ? "<span class='cnt'>{$thisTotalCnt}</span>" : "";
					
					array_push($secMenuList, "나의DB관리{$thisTotalCnt}@dbMy@dbMyL");
					array_push($trdMenuList, []);
					
					array_push($secMenuList, "휴지통@trash@trashMyL");
					array_push($trdMenuList, []);
					break;
				case "005" : # 영업자일 경우 
					$thisTotalCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '002' AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}' AND check_yn = 'N'")["cnt"];
					$thisTotalCnt = ($thisTotalCnt) ? "<span class='cnt'>{$thisTotalCnt}</span>" : "";
					
					array_push($secMenuList, "나의DB관리{$thisTotalCnt}@dbMy@dbMyL");
					array_push($trdMenuList, []);
					
					array_push($secMenuList, "휴지통@trash@trashMyL");
					array_push($trdMenuList, []);
					break;
				case "006" : # 실장일 경우
					// $thisTotalCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '002' AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}' AND check_yn = 'N'")["cnt"];
					// $thisTotalCnt = ($thisTotalCnt) ? "<span class='cnt'>{$thisTotalCnt}</span>" : "";
					
					array_push($secMenuList, "나의DB관리{$thisTotalCnt}@dbMd@dbMdL");
					array_push($trdMenuList, []);
					
					break;
				case "007" : # 실장일 경우
					// $thisTotalCnt = view_sql("SELECT COUNT(*) AS cnt FROM mt_db WHERE use_yn = 'Y' AND dist_code = '002' AND tm_code = '{$user['tm_code']}' AND m_idx = '{$user['idx']}' AND check_yn = 'N'")["cnt"];
					// $thisTotalCnt = ($thisTotalCnt) ? "<span class='cnt'>{$thisTotalCnt}</span>" : "";
					
					array_push($secMenuList, "나의DB관리{$thisTotalCnt}@dbDr@dbDrL");
					array_push($trdMenuList, []);
				
					break;
			}
			break;
		case "db_recall" :
			$mainMenuIcon = '<i class="fas fa-id-card"></i>';
			$mainMenuName = '회수 DB관리';
			switch($user['auth_code']){
				case "001" : # 최고관리자일 경우 
				case "002" : # 관리자일 경우 
				array_push($secMenuList, "회수DB통합관리@dbRecall@dbRecallL");
					$trdMenuList_r = [];
					array_push($trdMenuList_r, "전체보기@all@dbRecallL");

					$query = "SELECT 
							    mc.*,
							    mmc1.category_name AS depth1_name,
							    mmc2.category_name AS depth2_name,
							    mmc3.category_name AS depth3_name,
							    mmc4.category_name AS depth4_name,
							    mmc5.category_name AS depth5_name
							FROM mt_member_cmpy AS mc
							LEFT JOIN mc_member_cmpy_category AS mmc1 
								ON mc.depth1 = mmc1.category_code AND mmc1.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc2 
							    ON mc.depth2 = mmc2.category_code AND mmc2.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc3 
							    ON mc.depth3 = mmc3.category_code AND mmc3.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc4 
							    ON mc.depth4 = mmc4.category_code AND mmc4.use_yn = 'Y'
							LEFT JOIN mc_member_cmpy_category AS mmc5 
							    ON mc.depth5 = mmc5.category_code AND mmc5.use_yn = 'Y'
							WHERE mc.use_yn = 'Y'
							  AND mc.auth_code = '003';
					";
					$value = array();
					$result_menu = list_pdo($query, $value);

					$company_data = array();
					while($row = $result_menu->fetch(PDO::FETCH_ASSOC)){
						$path = array();

						$company_name = $row['company_name'];
						if($row["depth1_name"]){
							$path[] = $row["depth1_name"];
						}
						if($row["depth2_name"]){
							$path[] = $row["depth2_name"];
						}
						if($row["depth3_name"]){
							$path[] = $row["depth3_name"];
						}
						if($row["depth4_name"]){
							$path[] = $row["depth4_name"];
						}
						if($row["depth5_name"]){
							$path[] = $row["depth5_name"];
						}

						if(empty($path)){
    					    $no_depth_list[] = [
        					    'company_name' => $company_name,
        					    'url' => "dbRecallL?code={$row['idx']}",
								'use_yn' => $row['use_yn']
        					];
        					continue;
    					}
						$pathKey = implode('|', $path);
						$depth_data[] = $path;
						$company_data[$pathKey][] = array(
							'company_name' => $company_name,
							'url' =>  "dbRecallL?code={$row['idx']}",
							'idx' => $row['idx']
						);
					}
					$depthTree = makeTree($depth_data, $company_data);
					$depthTreeJson = json_encode($depthTree, JSON_UNESCAPED_UNICODE);
    				$companyDataJson = json_encode($company_data, JSON_UNESCAPED_UNICODE);


					foreach($no_depth_list as $row) {
						
    				    $styleleft = ($row['use_yn']=="N") ? "<span style='color:#000; opacity: 0.40;'>"  : "";
						$styleright = ($row['use_yn']=="N") ? "<i class='far fa-window-close' style='margin-right:20px; line-height:52px; float:right;'></i></span>" : "";

    				    $companyName = dhtml($row['company_name']);
    				    $url = "dbRecallL?code={$row['idx']}";
					
    				    array_push($trdMenuList_r, "{$styleleft}{$companyName}{$styleright}@db{$row['idx']}@{$row['url']}");
    				}
    				array_push($trdMenuList, $trdMenuList_r);
			}
			break;
		case "bbs" :
			$mainMenuIcon = '<i class="fas fa-headset"></i>';
			$mainMenuName = '커뮤니티';
			
			array_push($secMenuList, "공지사항@bbs001@bbs?bbs=001&inc=L");
			array_push($trdMenuList, []);
			
			array_push($secMenuList, "자료실@bbs003@bbs?bbs=003&inc=L");
			array_push($trdMenuList, []);

			array_push($secMenuList, "Q&A@bbs002@bbs?bbs=002&inc=L");
			array_push($trdMenuList, []);
			
			break;
		case "chart" :
			$mainMenuIcon = '<i class="fas fa-chart-line"></i>';
			$mainMenuName = '통계';

			switch($user['auth_code']){
				case "001" :
				case "002" :
					array_push($secMenuList, "조직현황통계@group@groupL");
					array_push($trdMenuList, []);
					
					array_push($secMenuList, "DB통합통계@dbAll@dbAll003L");
					array_push($trdMenuList, ["생산업체@all003@dbAll003L", "{$customLabel["tm"]}@all004@dbAll004L", "{$customLabel["fc"]}@all005@dbAll005L"]);
					
					array_push($secMenuList, "DB분배통계@dbTeam@dbTeamAllL");
					$trdMenuList_r = [];
					
					array_push($trdMenuList_r, "+ {$customLabel["tm"]}별분배현황@all@dbTeamAllL");
					$value = array(':use_yn'=>'Y');
					$query = "SELECT * FROM mt_member_team WHERE use_yn = :use_yn ORDER BY idx DESC";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$row['team_name'] = dhtml($row['team_name']);
						array_push($trdMenuList_r, "{$row['team_name']}@tm{$row['idx']}@dbTeamL?code={$row['idx']}");
					}
					array_push($trdMenuList, $trdMenuList_r);

					array_push($secMenuList, "DB생산업체통계@dbcmpy@dbCmpyAllL");
					array_push($trdMenuList, []);

					$firstCode = "";
					$trdMenuList_r = [];
					
					$value = array(':use_yn'=>'Y',':number_yn'=>'Y',':sms_yn'=>'N');
					$query = "SELECT * FROM mc_db_cs_status WHERE use_yn = :use_yn AND number_yn = :number_yn AND sms_yn = :sms_yn ORDER BY sort ASC";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$firstCode = ($firstCode) ? $firstCode : $row["status_code"];
						array_push($trdMenuList_r, "{$row["status_name"]}현황@code{$row["status_code"]}@dbCalculateAllL?code={$row["status_code"]}");
					}
					array_push($secMenuList, "DB정산통계@dbCalculate@dbCalculateAllL?code={$firstCode}");
					array_push($trdMenuList, $trdMenuList_r);
					
					array_push($secMenuList, "회원통계@member@loginL");
					array_push($trdMenuList, ["로그인@login@loginL"]);

					array_push($secMenuList, "엑셀 다운현황@excel@excelL");
					array_push($trdMenuList, ["다운현황@excel@excelL"]);
					break;
				case "003" :
					array_push($secMenuList, "DB통합통계@dbDist@dbDistL");
					array_push($trdMenuList, []);

					// $view = view_sql("SELECT * FROM mt_member_cmpy WHERE use_yn = 'Y' AND pm_code = '{$user['pm_code']}' ORDER BY idx DESC");
					// array_push($secMenuList, "DB생산업체통계@cmpy{$view['pm_code']}@dbcmpyL?code={$view['pm_code']}");
					// $trdMenuList_r = [];					

					break;
				case "004" :
					array_push($secMenuList, "DB분배통계@dbMyTeam@dbMyTeamL");
					$trdMenuList_r = [];
					
					array_push($trdMenuList_r, "전체보기@all@dbMyTeamL");
					$value = array(':use_yn'=>'Y',':tm_code'=>$user['tm_code'],':auth_code'=>'005');
					$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND tm_code = :tm_code AND auth_code = :auth_code ORDER BY idx DESC";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$row['m_name'] = dhtml($row['m_name']);
						array_push($trdMenuList_r, "{$row['m_name']}@fc{$row['idx']}@dbMyTeamL?code={$row['idx']}");
					}
					array_push($trdMenuList, $trdMenuList_r);
					
					array_push($secMenuList, "나의DB통계@dbMy@dbMyL");
					array_push($trdMenuList, []);
					break;
				case "005" :
					array_push($secMenuList, "나의DB통계@dbMy@dbMyL");
					array_push($trdMenuList, []);
					break;
			}
			
			break;
		case "sms" :
			$mainMenuIcon = '<i class="fas fa-envelope"></i>';
			$mainMenuName = 'SMS';
			
			array_push($secMenuList, "SMS전송@send@sendW");
			array_push($trdMenuList, []);
			
			array_push($secMenuList, "전송내역@log@logL");
			array_push($trdMenuList, []);
			
			if($user["auth_code"] <= 002){
				array_push($secMenuList, "발신번호 조회@sendTel@sendTelL");
				array_push($trdMenuList, []);
			}
			
			break;
		case "schedule" :
			$mainMenuIcon = '<i class="fas fa-calendar-alt"></i>';
			$mainMenuName = '일정관리';
			
			# 달력 정보
			$year = ($_GET["year"]) ? $_GET["year"] : date("Y"); # 년
			$month = ($_GET["month"]) ? $_GET["month"] : date("m"); # 월
			$day = $_GET["day"]; # 일
			$date = "{$year}-{$month}-01"; # 설정날짜
			
			$prevInfo["year"] = date("Y", strtotime("{$date} - 1 month"));
			$prevInfo["month"] = date("m", strtotime("{$date} - 1 month"));
			
			$nextInfo["year"] = date("Y", strtotime("{$date} + 1 month"));
			$nextInfo["month"] = date("m", strtotime("{$date} + 1 month"));
			
			$dateTime = strtotime($date); # 설정날짜 가공
			$startWeek = date("w", $dateTime); # 시작요일
			$totalDay = date("t", $dateTime); # 마지막일자
			$totalWeek = ceil(($totalDay + $startWeek) / 7); # 설정 월의 마지막 주차
			
			# 콘텐츠설정
			$contentsTitle = "{$year}년 {$month}월 일정관리";
			
			array_push($secMenuList, "일정구분@@scheduleL");
			$trdMenuList_r = [];

			$value = array(':use_yn'=>'Y');
			$query = "SELECT * FROM mc_schedule_type WHERE use_yn = :use_yn ORDER BY sort ASC";
			$sql = list_pdo($query, $value);
			while($row = $sql->fetch(PDO::FETCH_ASSOC)){
				$row['type_name'] = dhtml($row['type_name']);
				array_push($trdMenuList_r, "{$row['type_name']}@scheduleItemSelect typeItem typeItem{$row["type_code"]}@scheduleL");
			}
			array_push($trdMenuList, $trdMenuList_r);
			
			switch($user["auth_code"]){
				case "001" :
				case "002" :
					array_push($secMenuList, "일정담당자@@scheduleL");
					$trdMenuList_r = [];

					$num = "001, 002, 004, 005";
					$value = array(':use_yn'=>'Y');
					$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND auth_code IN ( $num ) ORDER BY idx DESC";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$row["idx"] = ltrim($row["idx"], '0');
						$row['m_name'] = dhtml($row['m_name']);
						array_push($trdMenuList_r, "{$row['m_name']}@scheduleItemSelect userItem userItem{$row["idx"]}@scheduleL");
					}
					array_push($trdMenuList, $trdMenuList_r);
					break;
				case "004" :
					array_push($secMenuList, "일정담당자@@scheduleL");
					$trdMenuList_r = [];

					$num = "004, 005";
					$value = array(':use_yn'=>'Y',':tm_code'=>$user["tm_code"]);
					$query = "SELECT * FROM mt_member WHERE use_yn = :use_yn AND auth_code IN ( $num ) AND tm_code = :tm_code ORDER BY idx DESC";
					$sql = list_pdo($query, $value);
					while($row = $sql->fetch(PDO::FETCH_ASSOC)){
						$row["idx"] = ltrim($row["idx"], '0');
						$row['m_name'] = dhtml($row['m_name']);
						array_push($trdMenuList_r, "{$row['m_name']}@scheduleItemSelect userItem userItem{$row["idx"]}@scheduleL");
					}
					array_push($trdMenuList, $trdMenuList_r);
					break;
			}
			
			break;
			
		case "db_dent" :
			$mainMenuIcon = '<i class="fas fa-id-card"></i>';
			$mainMenuName = '덴트웹DB관리';
			
			array_push($secMenuList, "DB관리@dbDent@dbDentL");
			array_push($trdMenuList, []);
			
			break;
	}

?>
<style>
.ul_icon{
	z-index:10;
}
.depth-group {
    position: relative;
    list-style: none;
}

.depth-group > .depth-title {
    display: inline-block;
    padding: 10px 15px;
    cursor: pointer;
    user-select: none;
}

.depth-group > .depth-title:hover {
    background: rgba(255, 255, 255, 0.05);
}

.depth-group > .ul_icon {
    cursor: pointer;
    vertical-align: middle;
}

.depth-children {
    padding-left: 0;
	width:100%;
	float:left;
}

.depth-company {
    list-style: none;
    cursor: pointer;
}

.depth-company.active {
    background: rgba(255, 255, 255, 0.1);
}

.depth-company a {
    display: block;
    padding: 8px 15px;
    text-decoration: none;
    color: inherit;
}

.depth-company a:hover {
    background: rgba(255, 255, 255, 0.05);
}


.depth-group{z-index:2;}
.depth-1{
    padding: 0 0 0 35px;
    font-size: 14px;
    color: #FFF;
    font-weight: 500;
}
.depth-2{
	padding-left: 10px;
}
.depth-3{
	padding-left: 10px;
}
.depth-4{
	padding-left: 10px;
}
.depth-company {
	padding-left: 10px;
}
.last-company{
	padding-left: 0px;
}
</style>
<script>
$(document).ready(function() {
    
    var depthTreeData = <?=($depthTreeJson) ? $depthTreeJson : '{}'?>;
    var companyData = <?=($companyDataJson) ? $companyDataJson : '{}'?>;
    
    
    // HTML 생성 함수 (depth 제목만)
    function createDepthItem(name, pathKey, depth, hasChildren) {
        var html = '';
        html += '<ul class="depth-group depth-' + depth + '" data-depth="' + depth + '" data-path="' + pathKey + '">';
        html += '<span class="depth-title">' + 'ㄴ' + name + '</span>';
        
        if (hasChildren) {
            html += '<svg class="ul_icon" width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">';
            html += '<g clip-path="url(#clip0_6174_11)">';
            html += '<path d="M4.60254 6.32812H10.3975C10.7109 6.32812 10.8691 6.70898 10.6465 6.92871L7.74902 9.80859C7.61133 9.94629 7.3916 9.94629 7.25391 9.80859L4.35645 6.92871C4.13086 6.70898 4.28906 6.32812 4.60254 6.32812ZM14.7656 7.5C14.7656 11.5137 11.5137 14.7656 7.5 14.7656C3.48633 14.7656 0.234375 11.5137 0.234375 7.5C0.234375 3.48633 3.48633 0.234375 7.5 0.234375C11.5137 0.234375 14.7656 3.48633 14.7656 7.5ZM13.3594 7.5C13.3594 4.2627 10.7373 1.64062 7.5 1.64062C4.2627 1.64062 1.64062 4.2627 1.64062 7.5C1.64062 10.7373 4.2627 13.3594 7.5 13.3594C10.7373 13.3594 13.3594 10.7373 13.3594 7.5Z" fill="white" fill-opacity="0.2"/>';
            html += '</g>';
            html += '</svg>';
        }
        
        html += '<div class="depth-children" style="display:none;"></div>';
        html += '</ul>';
        
        return html;
    }
    
    // ========== 2. 회사 목록 HTML 생성 ==========
    function createCompanyItems(pathKey) {
        var html = '';
        var companies = companyData[pathKey];
        
        if (companies && companies.length > 0) {
            companies.forEach(function(company) {
                var url = '/sub/<?=$mainMenu?>/' + company.url;
                var styleClass = company.use_yn == 'N' ? 'inactive' : '';
                var styleLeft = company.use_yn == 'N' ? '<span style="color:#000; opacity: 0.40;">' : '';
                var styleRight = company.use_yn == 'N' ? '<i class="far fa-window-close" style="margin-right:20px;"></i></span>' : '';
                
                html += '<li id="trdMenu_db' + company.idx + '" class="depth-company ' + styleClass + '">';
                html += '<div class="li_box"></div>';
                html += '<a href="' + url + '">';
                html += styleLeft + company.company_name + styleRight;
                html += '</a>';
                html += '</li>';
            });
        }
        
        return html;
    }
    
    // 하위 메뉴 생성 (회사 + depth)
function generateChildContent(tree, depth, parentPath) {
    var html = '';
    
    // 3-1. 현재 depth의 회사들 먼저 추가
    var currentPathKey = parentPath.join('|');
    var currentCompanies = createCompanyItems(currentPathKey);
    
    // 현재 depth에 회사가 있고, 하위 depth도 있으면 구분선이나 스타일 추가 가능
    if (currentCompanies) {
        html += currentCompanies;
    }
    
    // 3-2. 하위 depth들 추가
    for (var name in tree) {
        if (name === "__company__") continue;
        
        var currentPath = parentPath.slice();
        currentPath.push(name);
        var pathKey = currentPath.join('|');
        
        var keys = Object.keys(tree[name]).filter(k => k !== "__company__");
        var hasChildren = keys.length > 0;
        
        // 회사가 있는지도 확인
        var hasCompany = companyData[pathKey] && companyData[pathKey].length > 0;
        
        html += createDepthItem(name, pathKey, depth, hasChildren || hasCompany);
    }
    
    return html;
}
    
    // 초기 depth1 메뉴 생성
    if (typeof depthTreeData !== 'undefined' && Object.keys(depthTreeData).length > 0) {
        var initialHtml = '';
        
        for (var name in depthTreeData) {
            var keys = Object.keys(depthTreeData[name]).filter(k => k !== "__company__");
            var hasChildren = keys.length > 0;
            
            initialHtml += createDepthItem(name, name, 1, hasChildren);
        }
        
        $('#depthMenuContainer').html(initialHtml);
    }
    
    // 클릭 이벤트
    $(document).on('click', '.depth-group > .depth-title, .depth-group > .ul_icon', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        var depthGroup = $(this).parent('.depth-group');
        var childContainer = depthGroup.children('.depth-children');
        var depth = parseInt(depthGroup.data('depth'));
        var path = depthGroup.data('path').split('|');
        
        
        // 이미 생성되어 있으면 토글만
        if (childContainer.children().length > 0) {
            childContainer.slideToggle(200);
            depthGroup.toggleClass('expanded');
        } else {
            // 트리 탐색
            var tree = depthTreeData;
            for (var i = 0; i < path.length; i++) {
                tree = tree[path[i]];
            }
            
            if (tree) {
                // 하위 컨텐츠 생성 (회사 + depth)
                var childHtml = generateChildContent(tree, depth + 1, path);
                
                childContainer.html(childHtml);
                childContainer.slideDown(200);
                depthGroup.addClass('expanded');

            }
        }
        
        // 같은 레벨 다른 메뉴 닫기
        depthGroup.siblings('.depth-group').each(function() {
            $(this).children('.depth-children').slideUp(200);
            $(this).removeClass('expanded');
        });
    });
    
    // ========== 6. 회사 클릭 시 active ==========
    $(document).on('click', '.depth-company', function(e) {
        $('.depth-company').removeClass('active');
        $(this).addClass('active');
    });
    
    // ========== 7. URL 기반 자동 펼침 ==========
    var params = new URLSearchParams(window.location.search);
    var codeValue = params.get('code');
    
    if (codeValue) {
        
        // 회사 찾기
        var targetPathKey = null;
        var targetCompany = null;
        
        for (var pathKey in companyData) {
            var companies = companyData[pathKey];
            if (!companies) continue;
            
            for (var i = 0; i < companies.length; i++) {
                if (companies[i].url.indexOf('code=' + codeValue) !== -1) {
                    targetPathKey = pathKey;
                    targetCompany = companies[i];
                    break;
                }
            }
            if (targetPathKey) break;
        }
        
        if (targetPathKey) {
            var pathParts = targetPathKey.split('|');
            
            // 순차적으로 펼치기
            function expandPath(index) {
                if (index >= pathParts.length) {
                    // 마지막: 회사 active
                    setTimeout(function() {
                        var $company = $('.depth-company a[href*="code=' + codeValue + '"]').parent();
                        $company.addClass('active');
                        console.log('Company activated');
                    },0);
                    return;
                }
                
                var currentPath = pathParts.slice(0, index + 1).join('|');
                var $group = $('.depth-group[data-path="' + currentPath + '"]');
                
                if ($group.length > 0) {
                    var $child = $group.children('.depth-children');
                    
                    if ($child.children().length === 0) {
                        // 생성 필요
                        var tree = depthTreeData;
                        for (var i = 0; i < pathParts.length && i <= index; i++) {
                            tree = tree[pathParts[i]];
                        }
                        
                        var parentPath = pathParts.slice(0, index + 1);
                        var childHtml = generateChildContent(tree, index + 2, parentPath);
                        $child.html(childHtml);
                    }
                    
                    $child.show();
                    $group.addClass('expanded');
                
                    
                    // 다음 depth로
                    setTimeout(function() {
                        expandPath(index + 1);
                    }, 0);
                } else {
                    expandPath(index + 1);
                }
            }
            
            expandPath(0);
        }
    }
    
});
</script>
			<div class="mainMenuNameWrap">
				<?=$mainMenuIcon?>
				<span><?=$mainMenuName?></span>
				<div class="background"></div>
			</div>
			<?php if($mainMenu == "schedule"){ ?>
				<div id="scheduleCalendarWrap">
					<div class="titWrap">
						<div class="left"><?=$year?>년 <?=$month?>월</div>
						<div class="right">
							<a href="<?=$_SERVER["REDIRECT_URL"]?>?year=<?=$prevInfo["year"]?>&month=<?=$prevInfo["month"]?>">
								<i class="fas fa-angle-left"></i>
							</a>
							<a href="<?=$_SERVER["REDIRECT_URL"]?>?year=<?=$nextInfo["year"]?>&month=<?=$nextInfo["month"]?>">
								<i class="fas fa-angle-right"></i>
							</a>
						</div>
					</div>
					
					<div class="calendarWrap">
						<ul class="labelList">
							<li>일</li>
							<li>월</li>
							<li>화</li>
							<li>수</li>
							<li>목</li>
							<li>금</li>
							<li>토</li>
						</ul>
						<?php for($n = 1, $i = 0; $i < $totalWeek; $i++){ ?>
							<ul class="dayList">
							<?php for($k = 0; $k < 7; $k++){ ?>
								<li>
								<?php if(($n > 1 || $k >= $startWeek) && ($totalDay >= $n)){ ?>
								<?php
									$nn = ($n < 10) ? "0{$n}" : $n;
	
									$class = "";
									$class .= (date("Y-m-d") == "{$year}-{$month}-{$nn}") ? " today" : "";
								?>
									<a href="#" class="dayCalendarOpenBtn <?=$class?>" data-date="<?=$year?>-<?=$month?>-<?=$nn?>">
										<?=$n?>
									</a>
								<?php $n++; } ?>
								</li>
							<?php } ?>
							</ul>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
			<div class="subMenuListWrap">
				<ul class="secMenu">
				<?php for($i = 0; $i < count($secMenuList); $i++){ ?>
				<?php $secMenuInfo = explode("@", $secMenuList[$i]); ?>
				<?php $class = ($secMenu == $secMenuInfo[1]) ? "active" : ""; ?>
					<li id="secMenu_<?=$secMenuInfo[1]?>" class="<?=$class?>">
						<a href="/sub/<?=$mainMenu?>/<?=$secMenuInfo[2]?>" title="<?=explode("<i class='fas fa-plus-circle'></i>", $secMenuInfo[0])[1]?>">
							<span><?=$secMenuInfo[0]?></span>
							<?php if(count($trdMenuList[$i])){ ?>
								<i class="fas fa-angle-up on"></i>
								<i class="fas fa-angle-down off"></i>
							<?php } ?>
						</a>
						<ul class="trdMenu">
					    <?php for($ii = 0; $ii < count($trdMenuList[$i]); $ii++){ ?>
					        <?php 
					        $flag = explode("@", $secMenuList[$i]);
					        $flag = $flag[0];
						
					        if((strpos($flag, "DB통합관리") !== false || strpos($flag, "회수DB통합관리") !== false) && $ii == 0) {
					            // 첫 번째는 "전체보기"
					            $trdMenuInfo = explode("@", $trdMenuList[$i][$ii]);
					            $class = ($trdMenu == $trdMenuInfo[1]) ? "active" : "";
					            ?>
					            <li id="trdMenu_<?=$trdMenuInfo[1]?>" class="<?=$class?>">
					                <a href="/sub/<?=$mainMenu?>/<?=$trdMenuInfo[2]?>">
					                    <?=$trdMenuInfo[0]?>
					                </a>
					            </li>
							
					            <!-- depth1 동적 메뉴 컨테이너 -->
					            <div id="depthMenuContainer"></div>
							
					        <?php } elseif(is_array($trdMenuList[$i][$ii])) { ?>
					            <!-- 기존 배열 메뉴 처리 -->
					            <?php
					            // DB분배관리 등의 기존 메뉴 처리...
					            ?>
					        <?php } else { ?>
					            <!-- 일반 메뉴 -->
					            <?php
					            $trdMenuInfo = explode("@", $trdMenuList[$i][$ii]);
					            $class = ($trdMenu == $trdMenuInfo[1]) ? "active" : "";
					            ?>
					            <li id="trdMenu_<?=$trdMenuInfo[1]?>" class="<?=$class?>">
					                <a href="/sub/<?=$mainMenu?>/<?=$trdMenuInfo[2]?>">
					                    <?=$trdMenuInfo[0]?>
					                </a>
					            </li>
					        <?php } ?>
					    <?php } ?>
					    <li class="background"></li>
					</ul>
					</li>
				<?php } ?>
				</ul>
			</div>