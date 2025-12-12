		<div class="dash_section notice">
			<span class="section_tit">최근 등록된 DB</span>
			<div class="section_con">
				<table>
					<colspan>
						<col width="72">
						<col width="18%">
					</colspan>
					<tbody>
					<?php
						$sql = "
							SELECT *
							FROM mt_db
							WHERE m_idx = '{$user['idx']}'
							ORDER BY idx DESC
							LIMIT 0, 8
						";
						$result = list_sql($sql);
						foreach ( $result as $row ){
							$listCss = ($row['use_yn'] == "N") ? "text-decoration: line-through; color: #CCC;" : "";
					?>
						<tr>
							<td class="tit"><i class="fas fa-user-circle userIcon"></i><span style="<?=$listCss?>"><?=$row['cs_name']?> - <?=$row['cs_tel']?></span></td>
							<td class="date"><?=date("m-d H:i", strtotime($row['reg_date']))?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>