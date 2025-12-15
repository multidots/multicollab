<?php
require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-settings-report-table.php';
$mc_table = new MC_List_Table_Report();

$activity_data = new Commenting_Block_Activities();
$data          = $activity_data->cf_get_cpt_activity_report();

// Check if month filter is active
$m_filter = filter_input( INPUT_GET, 'm_report', FILTER_SANITIZE_SPECIAL_CHARS );
$has_month_filter = ! empty( $m_filter ) && '0' !== $m_filter;

// If no posts found AND no month filter is active, show "getting started" file
// If no posts found BUT month filter is active, show table with "no items found" message
if ( 0 == $data['found_posts'] && ! $has_month_filter ) {
	require_once COMMENTING_BLOCK_DIR . 'admin/partials/commenting-block-reports-no-results.php';
}else{
	echo '<div class="cf-reports-panel">';
		$mc_table->prepare_items( $data );
		$mc_table->display();
	echo '</div>';
}

