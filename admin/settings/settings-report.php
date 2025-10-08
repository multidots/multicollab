<?php
require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-settings-report-table.php';
$mc_table = new MC_List_Table_Report();

$activity_data = new Commenting_Block_Activities();
$data          = $activity_data->cf_get_cpt_activity_report();

if ( 0 == $data['found_posts'] ) {
	require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-reports-no-results.php';
}else{
	echo '<div class="cf-reports-panel">';
		$mc_table->prepare_items( $data );
		$mc_table->display();
	echo '</div>';
}