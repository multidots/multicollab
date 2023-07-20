<?php

/**
 * Class for get plan details.
 */
if( ! class_exists('CF_EDD') ) :
	class CF_EDD {

		// ...

		/**
		 * Constructor.
		 */
		public function __construct() {

		}

		/**
		 * Return currunt plan name.
		 *
		 * @return string
		 */
		public function get_plan_name() {

			$license_options = get_option( 'cf_activated_license_details' );
			$license_data    = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) ) {
				return strtolower( $license_data->item_name );
			} else {
				return false;
			}

		}

		/**
		 * Return true if givven plan is correct or not.
		 *
		 * @param string $plan_name
		 * @return boolean
		 */
		public function is_plan( $plan_name ) {

			$license_options = get_option( 'cf_activated_license_details' );
			$license_data    = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) && ! empty( $plan_name ) ) {
				if ( strtolower( $license_data->item_name ) === $plan_name ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}

		}

		/**
		 * Return true if plan is valid.
		 *
		 * @return boolean
		 */
		public function is_valid() {

			$license_options = get_option( 'cf_activated_license_details' );
			$license_data    = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) ) {
				if ( 'valid' === $license_data->license_status ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}

		}

		/**
		 * Return true if plan is valid and premium.
		 *
		 * @return boolean
		 */
		public function is_premium() {

			$license_options = get_option( 'cf_activated_license_details' );

			$license_data = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) ) {
				if ( 'valid' === $license_data->license_status && ( EDD_PLAN_PRO == $license_data->item_id ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}

		}


		/**
		 * Return true if plan is valid and free.
		 *
		 * @return boolean
		 */
		public function is_free() {

			$license_options = get_option( 'cf_activated_license_details' );

			$license_data = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) ) {
				if ( 'valid' === $license_data->license_status && ( EDD_PLAN_PRO == $license_data->item_id ) ) {
					return false;
				} else {
					return true;
				}
			} else {
				return true;
			}

		}

		/**
		 * Return true if license is expired.
		 *
		 * @return boolean
		 */
		public function is_expired() {

			$license_options = get_option( 'cf_activated_license_details' );
			$license_data    = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) ) {
				if ( 'expired' === $license_data->license_status ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		public function is__premium_only() {
			$license_options = get_option( 'cf_activated_license_details' );
			$license_data    = maybe_unserialize( $license_options );

			$license_data = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) ) {
				if ( 'valid' === $license_data->license_status && ( EDD_PLAN_PRO == $license_data->item_id ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		public function can_use_premium_code() {
			$license_options = get_option( 'cf_activated_license_details' );
			$license_data    = maybe_unserialize( $license_options );

			$license_data = maybe_unserialize( $license_options );
			if ( ! empty( $license_data ) ) {
				if ( 'valid' === $license_data->license_status && ( EDD_PLAN_PRO == $license_data->item_id ) ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

	}
endif;
