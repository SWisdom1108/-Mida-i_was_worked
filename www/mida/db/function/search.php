<?php

	$_SEARCH = array();
	function search(){
		global $andQuery, $_SEARCH;
		
		$data = explode("?", $_SERVER['REQUEST_URI'])[1];
		if($data){
			$data = explode("&", $data);
			for($i = 0; $i < count($data); $i++){
				$label = explode("=", $data[$i])[0];
				$_GET['value'] = ehtml($_GET['value']);
				switch($label){
					case "label" :
						$val = explode("=", $data[$i])[1];
						if($val=="cs_tel"){
							$andQuery .= " AND replace(cs_tel,'-','') LIKE replace('%{$_GET['value']}%','-','')";
						}else if($_GET['value']){
							if(array_key_exists($val, $_SEARCH)){
								$andQuery .= $_SEARCH[$val];
							} else {
								$andQuery .= " AND {$val} LIKE '%{$_GET['value']}%'";
							}
						}else if($val=="cs_nt"){
							$andQuery .= " AND (replace(cs_tel,'-','') LIKE replace('%{$_GET['value']}%','-','') OR cs_name LIKE '%{$_GET['value']}%')";
						}
						break;
					case "setDate" :
						if($_GET['s_date']){
							if(array_key_exists('s_date', $_SEARCH)){
								$andQuery .= $_SEARCH['s_date'];
							} else {
								$andQuery .= " AND date_format({$_GET['setDate']}_date, '%Y-%m-%d') >= date_format('{$_GET['s_date']}', '%Y-%m-%d') ";
							}
						}
						if($_GET['e_date']){
							if(array_key_exists('e_date', $_SEARCH)){
								$andQuery .= $_SEARCH['e_date'];
							} else {
								$andQuery .= " AND date_format({$_GET['setDate']}_date, '%Y-%m-%d') <= date_format('{$_GET['e_date']}', '%Y-%m-%d') ";
							}
						}
						break;
				}
			}
		}
		$_GET['value'] = dhtml($_GET['value']);
	}

?>