<?php

require_once COMMENTING_BLOCK_DIR . 'admin/classes/class-commenting-block-settings-report-table.php';
$mc_table = new MC_List_Table_Report();

$activity_data = new Commenting_Block_Activities();
$data          = $activity_data->cf_get_cpt_activity_report();

echo '<div class="wrap post-activity">';

if ( 0 === $data['found_posts'] ) {
	echo '<p>' . esc_html__( 'No activities found.', 'content-collaboration-inline-commenting' ) . '</p>';
} else {
	$mc_table->prepare_items( $data );
	$mc_table->display();
}
echo '</div>';
