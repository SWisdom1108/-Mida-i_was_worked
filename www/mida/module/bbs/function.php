<?php

	$_SEARCH = array();

	// htmlspecialchars_encode
	function bbsEhtml($data){
		return addslashes(htmlspecialchars($data));
	}

	// htmlspecialchars_decode
	function bbsDhtml($data){
		return htmlspecialchars_decode(nl2br($data));
	}

	 // paging param
	function bbsPagingURL($page){
		global $listCnt, $bbsCode, $searchLabel, $searchVal;
		
		$data = explode("?", $_SERVER['REQUEST_URI'])[1];
		$url = str_replace("index.php", "", $_SERVER['PHP_SELF']);
		
		return "{$url}?bbs={$bbsCode}&inc=L&page={$page}&listCnt={$listCnt}&label={$searchLabel}&value={$searchVal}";
	}

	// list no set
	function bbsListNo(){
		global $totalCnt, $limit;
		static $listNo;
		
		$resultNo = ($totalCnt - $limit) - $listNo;
		$listNo = $listNo + 1;
		
		return $resultNo;
	}

	// bbsListSet
	function bbsListSet(){
		global $listCnt;
		
		$url = bbsPagingURL(1);
		$url = explode("&listCnt={$listCnt}", $url);
		
		echo "<div class='bbsListSetWrap'>";
		echo "<select id='bbsListCntSet'>";
		echo "<option value='15'>15개씩 보기</option>";
		echo "<option value='30'>30개씩 보기</option>";
		echo "<option value='50'>50개씩 보기</option>";
		echo "<option value='100'>100개씩 보기</option>";
		echo "</select>";
		echo "</div>";
		
		echo "
			<script>
				$('#bbsListCntSet').val('{$listCnt}');
				if($('#bbsListCntSet').val() == null){
					$('#bbsListCntSet').append('<option value=\"{$listCnt}\">{$listCnt}개씩 보기</option>');
					$('#bbsListCntSet').val('{$listCnt}');
				}
				
				$('#bbsListCntSet').change(function(){
					var val = $(this).val();
					
					window.location.href = '{$url[0]}&listCnt=' + val + '{$url[1]}';
				});
			</script>
		";
	}

	// bbsSearch
	function bbsSearch(){
		global $searchLabel, $searchVal, $bbsCode, $page, $listCnt;
		
		$url = bbsPagingURL(1);
		$url = explode("&listCnt={$listCnt}", $url);
		
		echo "<form class='bbsSearchWrap' method='get'>";
		echo "<input type='hidden' name='bbs' value='{$bbsCode}'>";
		echo "<input type='hidden' name='inc' value='L'>";
		echo "<input type='hidden' name='page' value='{$page}'>";
		echo "<input type='hidden' name='listCnt' value='{$listCnt}'>";
		echo "<ul>";
		echo "<li><select id='bbsSearchLabel' name='label'><option value='title'>제목</option><option value='name'>작성자</option></select></li>";
		echo "<li><input type='text' name='value' value='{$searchVal}'></li>";
		echo "<li><button type='submit'>검색</button></li>";
		echo "</ul>";
		echo "</form>";
		
		echo "
			<script>
				$('#bbsSearchLabel').val('{$searchLabel}');
				if($('#bbsSearchLabel').val() == null){
					$('#bbsSearchLabel').append('<option value=\"{$searchLabel}\">{$searchLabel}</option>');
					$('#bbsSearchLabel').val('{$searchLabel}');
				}
			</script>
		";
	}

	// paging
	function bbsPaging($table){
		global $pageURL, $pageNum, $listCnt, $orderBy, $orderQuery, $pageNum, $block, $b_start_page, $b_start_page, $b_end_page, $total_block, $limit, $limitSQL, $totalCnt, $andQuery, $total_page;
		
		if($table){
			$totalCnt = view_sql("SELECT count(*) AS num FROM {$table} {$andQuery}")['num'];
			$pageNum = ($_GET['page']) ? $_GET['page'] : 1; # 페이지 번호
			$listCnt = ($_GET['listCnt']) ? $_GET['listCnt'] : 15; # 리스트 갯수 default 15

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
			$limitSQL = "LIMIT {$limit}, {$listCnt}";

			if($limit > $totalCnt){
				msg("", "", $pageURL.bbsPaging(1));
				return false;
			}
		} else {
			echo '<ul class="bbsPagingWrap">';
			
			if($pageNum > 1){
				echo '<li><a href="'.bbsPagingURL(1).'"><i class="fas fa-angle-double-left"></i></a></li>';
			}
			
			if($block > 1){
				echo '<li><a href="'.bbsPagingURL($b_start_page-1).'"><i class="fas fa-angle-left"></i></a></li>';
			}
			
			for($j = $b_start_page; $j <=$b_end_page; $j++){
				if($j <= 0){
					msg("", "", bbsPagingURL(1));
					return false;
				}
				
				if($pageNum == $j){
					echo '<li><span>'.$j.'</span></li>';
				} else {
					echo '<li><a href="'.bbsPagingURL($j).'">'.$j.'</a></li>';
				}
			}
			
			if($block < $total_block){
				echo '<li><a href="'.bbsPagingURL($b_end_page+1).'"><i class="fas fa-angle-right"></i></a></li>';
			}
			
			if($pageNum < $total_page){
				echo '<li><a href="'.bbsPagingURL($total_page).'"><i class="fas fa-angle-double-right"></i></a></li>';
			}
			
			echo '</ul>';
			echo '<script>';
			echo 'if($(".bbsPagingWrap > li").length == 1){';
			echo '$(".bbsPagingWrap > li > span").css("border-radius", "7px");';
			echo '}';
			echo '</script>';
		}
	}

	// getUserName
	function getUserName($idx){
		global $userTable, $userNameColum;
		
		$view = view_sql("SELECT {$userNameColum} FROM {$userTable} WHERE idx = '{$idx}'")[$userNameColum];
		return $view;
	}

?>