<?php

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST, PUT');
	header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

	function pm_chk() {
		// 모바일 기종(배열 순서 중요, 대소문자 구분 안함)
		$ary_m = array("iPhone","iPod","IPad","Android","Blackberry","SymbianOS|SCH-M\d+","Opera Mini","Windows CE","Nokia","Sony","Samsung","LGTelecom","SKT","Mobile","Phone");
		for($i=0; $i<count($ary_m); $i++){
			if(preg_match("/$ary_m[$i]/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return $ary_m[$i];
				break;
			}
		}
		return "PC";
	}
	function get_browser2(){
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = "";
		$platform = "";
		$version = "";

		if(preg_match('/linux/i', $u_agent)){
			$platform = "linux";
		}elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		if ( preg_match("/MSIE*/", $u_agent) ) { 
			// 익스플로러
			if ( preg_match("/MSIE 6.0[0-9]*/", $u_agent) ) {
				$bname = "Explorer 6"; 
			}elseif ( preg_match("/MSIE 7.0*/", $u_agent) ) {
				$bname = "Explorer 7";
			}elseif ( preg_match("/MSIE 8.0*/", $u_agent) ) {
				$bname = "Explorer 8"; 
			}elseif ( preg_match("/MSIE 9.0*/", $u_agent) ) {
				$bname = "Explorer 9"; 
			}elseif ( preg_match("/MSIE 10.0*/", $u_agent) ) {
				$bname = "Explorer 10"; 
			}else{
				$bname = "Explorer ETC"; 
			}
			$ub = "MSIE";
		}elseif( preg_match("/Trident*/", $u_agent) &&  preg_match("/rv:11.0*/", $u_agent) &&  preg_match("/Gecko*/", $u_agent) ) {
			$bname = "Explorer 11"; 
			$ub = "MSIE";
		}elseif( preg_match("/Edge\/*/", $u_agent)) {
			$bname = "Microsoft Edge"; 
			$ub = "MSIE";
		}elseif(preg_match('/Firefox/i',$u_agent)) {
			$bname = 'Mozilla Firefox'; 
			$ub = "Firefox"; 
		}elseif(preg_match('/Chrome/i',$u_agent)) {
			$bname = 'Google Chrome'; 
			$ub = "Chrome"; 
		}elseif(preg_match('/Safari/i',$u_agent)) {
			$bname = 'Apple Safari'; 
			$ub = "Safari"; 
		}elseif(preg_match('/Netscape/i',$u_agent)) {
			$bname = 'Netscape';
			$ub= "Netscape";
		}
		if(preg_match('/OPR/i',$u_agent)) {
			$bname = 'Opera';
			$ub= "OPR";
		}

		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>'.join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}

		$i = count($matches['browser']);
		if ($i != 1) {
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version = $matches['version'][0];
			}else {
				$version = $matches['version'][1];
			}
		} else {
			$version = $matches['version'][0]; 
		}

		if ($version==null || $version=="") {$version="?";}
		return array('userAgent'=>$u_agent, 'name'=>$bname, 'version'=>$version, 'platform'=>$platform);
	}

	function mobile_type(){
		if( stristr($_SERVER['HTTP_USER_AGENT'],'ipad') ) {
			return "ipad";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'iphone') || strstr($_SERVER['HTTP_USER_AGENT'],'iphone') ) {
			return "iphone";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'blackberry') ) {
			return "blackberry";
		} else if( stristr($_SERVER['HTTP_USER_AGENT'],'android') ) {
			$known = array('Version','Android','other');
			$pattern = '#(?<browser>'.join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			preg_match_all($pattern, $_SERVER['HTTP_USER_AGENT'], $matches);
			return $matches[0][0];
		} else {
			return "etc";
		}
	}

	$chk_m = pm_chk();
	$ua = get_browser2();
	$referer_url = $_POST['referer_url'];
	$userAgent = $ua['userAgent'];
	$platform = $ua['platform'];
	$remote_addr = $_SERVER['REMOTE_ADDR'];

	if($chk_m == "PC"){
		$browser = $ua['name'];
		$version = $ua['version'];
	} else {
		$browser = $chk_m;
		$version = mobile_type();

		if(strpos($userAgent,"NAVER(")){
			preg_match_all("/NAVER\(.*\)/iU", $userAgent,$c);
			$t = explode("; ",$c[0][0]);
			if($t[1] == "search"){
				$browser = "NAVER APP ".$t[1];
			}else{
				$browser = "NAVER ".$t[1]." APP";
			}
		}

		if(strpos($userAgent,"DaumApps")){
			$browser = "DaumApps";
		}

		if(strpos($userAgent,"TistoryApp")){
			$browser = "TistoryApp";
		}
	}

	if(strpos($userAgent,"KAKAOTALK")){
		$referer_url = "KAKAOTALK";
	}

	$referer_url = ($referer_url) ? $referer_url : "Direct";
	$nowDate = date("Y-m-d");
	$view = view_sql("SELECT * FROM mt_visit_log WHERE reg_ip = '{$remote_addr}' AND reg_date LIKE '{$nowDate}%'");
	if(!$view){
		excute("
			INSERT INTO mt_visit_log
				( referer_url, browser, userAgent, version, platform, reg_ip )
			VALUES
				( '{$referer_url}', '{$browser}', '{$userAgent}', '{$version}', '{$platform}', '{$remote_addr}' )
		");
	}

?> 