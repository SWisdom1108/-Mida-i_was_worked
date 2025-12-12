<?php

	 // paging param
	function pagingURL($page=""){
		global $listCnt, $orderBy;
		
		$data = explode("?", $_SERVER['REQUEST_URI'])[1];
		$target = "page, listCnt, orderBy";
		$url = $_SERVER['PHP_SELF'];
		
		if($data){
			return "{$url}?page={$page}&listCnt={$listCnt}&orderBy={$orderBy}".getClean($data, $target);
		} else {
			return "{$url}?page={$page}&listCnt={$listCnt}&orderBy={$orderBy}";
		}
	}

	// list no set
	function listNo(){
		global $totalCnt, $limit;
		static $listNo;
		
		$resultNo = ($totalCnt - $limit) - $listNo;
		$listNo = $listNo + 1;
		
		return $resultNo;
	}

	// paging
	function paging($table=""){
		global $pageURL, $pageNum, $listCnt, $orderBy, $orderQuery, $pageNum, $block, $b_start_page, $b_start_page, $b_end_page, $total_block, $limit, $limitQuery, $totalCnt, $andQuery, $total_page;
		
		if($table){
			$totalCnt = view_sql("SELECT count(*) AS num FROM {$table} {$andQuery}")['num'];
			$pageNum = ($_GET['page']) ? $_GET['page'] : "1"; # 페이지 번호
			$listCnt = ($_GET['listCnt']) ? $_GET['listCnt'] : "15"; # 리스트 갯수 default 15
			if(!$orderBy){
				$orderBy = ($_GET['orderBy']) ? $_GET['orderBy'] : "reg_date DESC"; # 정렬기준 default idx 내림차순
			}
			$orderQuery = "ORDER BY {$orderBy}";

			$b_pageNum_listCnt = 5; # 한 블록에 보여줄 페이지 갯수 5개
			$block = ceil($pageNum/$b_pageNum_listCnt); # 총 블록 갯수 구하기
			$b_start_page = ( ($block - 1) * $b_pageNum_listCnt ) + 1; # 블록 시작 페이지 
			$b_end_page = $b_start_page + $b_pageNum_listCnt - 1;  # 블록 종료 페이지
			$total_page = ceil( $totalCnt / $listCnt ); # 총 페이지
			// 총 페이지 보다 블럭 수가 만을경우 블록의 마지막 페이지를 총 페이지로 변경
			if ($b_end_page > $total_page){ 
				$b_end_page = $total_page;
			}
			$total_block = ceil($total_page/$b_pageNum_listCnt);

			$limit = ($pageNum - 1) * $listCnt;
			$limitQuery = "limit {$limit}, {$listCnt}";

			if($limit > $totalCnt){
				msg("", "", $pageURL.paging(1));
				return false;
			}
		} else {
			if($totalCnt){
				echo '<div class="pagingWrap"><ul>';

				if($pageNum > 1){
					echo '<li class="first"><a href="'.pagingURL(1).'"><i class="fas fa-angle-double-left"></i></a></li>';
				}

				if($block > 1){
					echo '<li class="prev"><a href="'.pagingURL($b_start_page-1).'"><i class="fas fa-angle-left"></i></a></li>';
				}

				for($j = $b_start_page; $j <=$b_end_page; $j++){
					if($j <= 0){
						msg("", "", pagingURL(1));
						return false;
					}

					if($pageNum == $j){
						echo '<li><span>'.$j.'</span></li>';
					} else {
						echo '<li><a href="'.pagingURL($j).'">'.$j.'</a></li>';
					}
				}

				if($block < $total_block){
					echo '<li class="next"><a href="'.pagingURL($b_end_page+1).'"><i class="fas fa-angle-right"></i></a></li>';
				}

				if($pageNum < $total_page){
					echo '<li class="last"><a href="'.pagingURL($total_page).'"><i class="fas fa-angle-double-right"></i></a></li>';
				}

				echo '</ul></div>';
			}
			
			$_SESSION['prevURL'] = $_SERVER['REQUEST_URI'];
		}
	}

?>