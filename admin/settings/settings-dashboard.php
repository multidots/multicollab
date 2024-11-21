<?php
// Add WP Table Class.
require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-settings-table.php';
$mc_table = new MC_List_Table();
?>
	<div class="cf-dashboard-panel">
		<div class="tablenav clear top">
			<?php
				$mc_table->extra_tablenav( 'top' );
			?>
		</div>
		<div class="cf-dashboard-panel__board-main cf-list-view">
		<h3 class="cf-dashboard-panel__board-title"><?php esc_html_e( 'Latest Activity', 'content-collaboration-inline-commenting' ); ?></h3>
			<?php
			// Website Activities.
			require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-website-activity.php';
			?>
		</div>
		<div class="cf-dashboard-panel__board-main cf-detail-view" style="display:none;">
			<div id="board-item-detail"></div>
		</div>
	</div>
