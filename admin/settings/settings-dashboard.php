<?php
// Add WP Table Class.
require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-settings-table.php';
$mc_table = new MC_List_Table();
?>
	<div class="wrap web-activity">
		<div class="tablenav clear top">
			<?php
				$mc_table->extra_tablenav( 'top' );
			?>
		</div>
		<div class="board-items-main list-view">
		<h3 class="board-sec-title"><?php esc_html_e( 'Latest Activity', 'content-collaboration-inline-commenting' ); ?></h3>
			<?php
			// Website Activities.
			require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-website-activity.php';
			?>
		</div>
		<div class="board-items-main detail-view" style="display:none;">
			<div id="board-item-detail"></div>
		</div>
	</div>
