<?php
/**
 * Freemius Integration Handler
 * Manages plan association, site limits, and license validation
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SSW_Freemius {

	/**
	 * Initialize Freemius SDK
	 */
	public static function init() {
		if ( function_exists( 'snap_sites_wp_freemius' ) ) {
			snap_sites_wp_freemius()->add_filter( 'is_submenu_visible', array( __CLASS__, 'filter_submenu_visibility' ), 10, 2 );
		}
	}

	/**
	 * Get current user's plan from Freemius
	 *
	 * @return string Plan ID (starter, growth, pro, or free)
	 */
	public static function get_current_plan() {
		if ( ! function_exists( 'snap_sites_wp_freemius' ) ) {
			return 'free';
		}

		$fs = snap_sites_wp_freemius();
		
		if ( ! $fs->is_user_logged_in() ) {
			return 'free';
		}

		if ( $fs->is_plan( 'pro' ) ) {
			return 'pro';
		}
		if ( $fs->is_plan( 'growth' ) ) {
			return 'growth';
		}
		if ( $fs->is_plan( 'starter' ) ) {
			return 'starter';
		}

		return 'free';
	}

	/**
	 * Get site creation limit for current plan
	 *
	 * @param string $plan Plan ID
	 * @return int Site limit
	 */
	public static function get_plan_limit( $plan = '' ) {
		if ( empty( $plan ) ) {
			$plan = self::get_current_plan();
		}

		$limits = array(
			'free'    => 1,
			'starter' => 1,
			'growth'  => 3,
			'pro'     => 6,
		);

		return isset( $limits[ $plan ] ) ? $limits[ $plan ] : 1;
	}

	/**
	 * Check if user can access a specific template
	 *
	 * @param string $template_id Template ID
	 * @param string $plan        Plan ID
	 * @return bool
	 */
	public static function user_can_access_template( $template_id, $plan = '' ) {
		if ( empty( $plan ) ) {
			$plan = self::get_current_plan();
		}

		// Free plan: Local Business only
		if ( 'free' === $plan ) {
			return 'local-business' === $template_id;
		}

		// Paid plans: All templates
		$all_templates = array(
			'local-business',
			'corporate',
			'hotel',
			'fashion',
			'real-estate',
			'beauty',
			'restaurant',
			'clinic',
		);

		return in_array( $template_id, $all_templates, true );
	}

	/**
	 * Get active site count for current user
	 *
	 * @return int
	 */
	public static function get_active_sites_count() {
		global $wpdb;

		$current_user_id = get_current_user_id();
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->blogs} b
				INNER JOIN {$wpdb->blogmeta} bm ON b.blog_id = bm.blog_id
				WHERE bm.meta_key = '_ssw_created_by_plugin'
				AND bm.meta_value = '1'
				AND b.deleted = 0
				AND b.blog_id != %d",
				get_current_blog_id()
			)
		);

		return (int) $count;
	}

	/**
	 * Get upgrade checkout URL for a plan
	 *
	 * @param string $plan Plan ID (starter, growth, pro)
	 * @return string Freemius checkout URL
	 */
	public static function get_upgrade_url( $plan = 'growth' ) {
		if ( ! function_exists( 'snap_sites_wp_freemius' ) ) {
			return '';
		}

		$fs = snap_sites_wp_freemius();
		
		// Map plan to Freemius plan ID
		$plan_map = array(
			'starter' => 'starter',
			'growth'  => 'growth',
			'pro'     => 'pro',
		);

		$freemius_plan = isset( $plan_map[ $plan ] ) ? $plan_map[ $plan ] : 'growth';

		return $fs->get_upgrade_url( $freemius_plan );
	}

	/**
	 * Check if current user has an active license
	 *
	 * @return bool
	 */
	public static function has_active_license() {
		if ( ! function_exists( 'snap_sites_wp_freemius' ) ) {
			return false;
		}

		$fs = snap_sites_wp_freemius();
		return $fs->is_paying();
	}

	/**
	 * Log plan/license events for audit trail
	 *
	 * @param string $event_type Type of event (plan_change, site_created, etc)
	 * @param array  $details    Event details
	 */
	public static function log_event( $event_type, $details = array() ) {
		$log_entry = array(
			'timestamp'  => current_time( 'mysql' ),
			'user_id'    => get_current_user_id(),
			'event_type' => sanitize_key( $event_type ),
			'details'    => wp_json_encode( $details ),
		);

		// Log to error log for now; can be extended to custom table later
		error_log( 'SSW Event: ' . wp_json_encode( $log_entry ) );
	}

	/**
	 * Filter Freemius submenu visibility
	 */
	public static function filter_submenu_visibility( $is_visible, $menu_id ) {
		// Hide Freemius debug menu in production
		if ( 'debug' === $menu_id && ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return false;
		}
		return $is_visible;
	}
}
