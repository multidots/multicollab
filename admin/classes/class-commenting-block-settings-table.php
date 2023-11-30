<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MC_List_Table extends WP_List_Table {

	private $found_data;
	
	/**
	 * Gets column of table.
	 *
	 * @return string[]
	 */
	public function get_columns() {
		// here you have to assign column names of your table.

		return array(
			'title'          => __( 'Title', 'content-collaboration-inline-commenting' ),
			'comments_count' => __( 'Comments', 'content-collaboration-inline-commenting' ),
			'collaborators'  => __( 'Collaborators', 'content-collaboration-inline-commenting' ),
			'activities'     => __( 'Recent Activities', 'content-collaboration-inline-commenting' ),
		);
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'title' => array( 'title', false ),
		);

		return $sortable_columns;
	}

	function prepare_items( $cf_activities = array() ) {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $cf_activities['activities_data'];

		$per_page     = $cf_activities['items_per_page'];
		$current_page = $this->get_pagenum();
		$total_items  = $cf_activities['found_posts'];

		$this->found_data = array_slice( $cf_activities['activities_data'], ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
		$this->items = $cf_activities['activities_data'];
	}

	/**
	 * Default values in the column.
	 *
	 * @param array|object $item
	 * @param string       $column_name
	 *
	 * @return bool|mixed|string|void
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'comments_count':
			case 'collaborators':
			case 'activities':
			case 'last_updated':
				return $item[ $column_name ];
			default:
				return $item; // Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Extra table nav.
	 *
	 * @param string $which
	 */
	public function extra_tablenav( $which ) {

		$cpt_filter = filter_input( INPUT_GET, 'cpt', FILTER_SANITIZE_SPECIAL_CHARS );
		$cpt_where  = $cpt_filter ? "post_type = '$cpt_filter'" : "(post_type = 'page' || post_type = 'post')";
		$cat_filter = filter_input( INPUT_GET, 'cat', FILTER_SANITIZE_SPECIAL_CHARS );
		$view       = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_SPECIAL_CHARS );
		$m          = filter_input( INPUT_GET, 'm', FILTER_SANITIZE_SPECIAL_CHARS );
		/* add code to solve get_categories issue */
		$cat_args   = array(
			'parent'     => 0,
			'hide_empty' => 0,
			'order'      => 'ASC',
		);
		$categories = get_categories( $cat_args );

		global $wpdb;
		$months       = $wpdb->get_results( "SELECT DISTINCT MONTH(post_modified) AS post_month, YEAR(post_modified) AS post_year FROM $wpdb->posts WHERE $cpt_where  ORDER BY post_modified ASC" ); // phpcs:ignore
		if ( $which === 'top' ) { ?>
			<div class="alignleft actions bulkactions">
				<form action="" method="get">
					<select name="m" id="filter-by-date">
						<option selected="selected" value="0"> <?php esc_html_e( 'All dates', 'content-collaboration-inline-commenting' ); ?> </option>
						<?php
						foreach ( $months as $single_month ) {
							$month       = $single_month->post_month;
							$year        = $single_month->post_year;
							$month_value = $year . '0' . $month;
							$month_title = date_i18n( 'F', mktime( 0, 0, 0, $month, 10 ) );
							?>
							<option value="<?php esc_attr_e( $month_value ); ?>" <?php selected( $m, $month_value ); ?>>
								<?php esc_html_e( $month_title . ' ' . $year ); ?>
							</option>

						<?php } ?>
					</select>
					<?php
					$args = array(
						'public' => true,
					);

					$output   = 'objects';
					$operator = 'and';

					$post_types = get_post_types( $args, $output, $operator );
					?>
					<select name="cpt" class="filter-allpagepost">
						<option value=""><?php esc_html_e( 'All pages/posts', 'content-collaboration-inline-commenting' ); ?> </option>
						<?php
						if ( ! empty( $post_types ) ) {
							foreach ( $post_types as $key => $post_types_values ) {
								if ( 'attachment' !== $key ) {
									?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $cpt_filter, $key ); ?>> <?php esc_html_e( $post_types_values->label, 'content-collaboration-inline-commenting' ); ?> </option>
									<?php
								}
							}
						}
						?>
					</select>
					<select name="cat" class="filter-allcategory">
						<option value=""><?php esc_html_e( 'All categories', 'content-collaboration-inline-commenting' ); ?> </option>
						<?php
						foreach ( $categories as $category ) {
							echo '<option value="' . esc_attr( $category->term_id ) . '" ' . selected( $cat_filter, $category->term_id ) . '>' . esc_html( $category->name ) . '</option>';
						}
						?>
					</select>
					<input type="hidden" name="page" value="editorial-comments">
					<input type="hidden" name="view" value="<?php esc_attr_e( $view ); ?>">
					<input type="submit" value="<?php esc_attr_e( 'Filter', 'content-collaboration-inline-commenting' ); ?>" class="btn button btn-primary">
					<a href="javascript:void(0)" class="btn button btn-primary reset-filter"><?php esc_html_e( 'Reset Filter', 'content-collaboration-inline-commenting' ); ?></a>
				</form>
			</div>
			<?php
		}
	}
}
