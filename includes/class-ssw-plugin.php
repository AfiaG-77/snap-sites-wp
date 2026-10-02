<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SSW_Plugin {

	public static function init() {
		if ( is_network_admin() ) {
			add_action( 'network_admin_menu', array( __CLASS__, 'register_menu' ) );
			add_action( 'admin_enqueue_scripts', array( 'SSW_Admin', 'enqueue_assets' ) );
		}
		add_action( 'wp_ajax_ssw_create_site', array( 'SSW_Site_Creator', 'ajax_create_site' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'front_css' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	public static function activate() {
		if ( ! is_multisite() ) {
			wp_die( 'Snap Sites WP requires WordPress Multisite to be enabled.' );
		}
	}

	public static function register_menu() {
		add_menu_page(
			'Snap Sites',
			'Snap Sites',
			'manage_network',
			'snap-sites',
			array( 'SSW_Admin', 'render' ),
			'dashicons-admin-multisite',
			3
		);
	}

	public static function template_list() {
		return array(
			'local-business' => array(
				'name'        => 'Local Business',
				'category'    => 'Business',
				'description' => 'Professional website for local services, trades and businesses.',
				'badge'       => 'Free',
			),
			'corporate' => array(
				'name'        => 'Corporate',
				'category'    => 'Business',
				'description' => 'Polished B2B site for consulting, agencies and enterprises.',
				'badge'       => 'Pro',
			),
			'hotel' => array(
				'name'        => 'Hotel',
				'category'    => 'Hospitality',
				'description' => 'Premium hospitality website for hotels and guest accommodations.',
				'badge'       => 'Pro',
			),
			'fashion' => array(
				'name'        => 'Fashion',
				'category'    => 'Retail',
				'description' => 'Editorial boutique and fashion brand website.',
				'badge'       => 'Pro',
			),
			'real-estate' => array(
				'name'        => 'Real Estate',
				'category'    => 'Property',
				'description' => 'Property-focused site for agents and developers.',
				'badge'       => 'Pro',
			),
			'beauty' => array(
				'name'        => 'Beauty',
				'category'    => 'Wellness',
				'description' => 'Salon and beauty services website with elegant design.',
				'badge'       => 'Pro',
			),
			'restaurant' => array(
				'name'        => 'Restaurant',
				'category'    => 'Food',
				'description' => 'Food and hospitality website for restaurants and cafés.',
				'badge'       => 'Pro',
			),
			'clinic' => array(
				'name'        => 'Clinic',
				'category'    => 'Healthcare',
				'description' => 'Professional healthcare website for clinics and practices.',
				'badge'       => 'Pro',
			),
		);
	}

	public static function plans() {
		return array(
			'free' => array( 'name' => 'Free', 'limit' => 1 ),
			'starter' => array( 'name' => 'Starter', 'limit' => 1 ),
			'growth' => array( 'name' => 'Growth', 'limit' => 3 ),
			'pro' => array( 'name' => 'Pro', 'limit' => 6 ),
		);
	}

	public static function get_current_plan() {
		return apply_filters( 'ssw_current_plan', 'pro' );
	}

	public static function get_plan_limit( $plan ) {
		$plans = self::plans();
		return isset( $plans[ $plan ]['limit'] ) ? $plans[ $plan ]['limit'] : 1;
	}

	public static function sites_used() {
		$ids = get_sites(
			array(
				'network_id' => get_current_network_id(),
				'number'     => 0,
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'   => '_ssw_created_by_plugin',
						'value' => '1',
					),
				),
			)
		);
		return count( $ids );
	}

	public static function ssw_user_can_access_template( $template_id, $plan ) {
		$templates = self::template_list();
		if ( ! isset( $templates[ $template_id ] ) ) {
			return false;
		}

		$template = $templates[ $template_id ];
		if ( 'Free' === $template['badge'] ) {
			return true;
		}

		return 'free' !== $plan;
	}

	public static function front_css() {
		if ( is_admin() ) {
			return;
		}

		$template = get_option( '_ssw_template_id' );
		if ( ! $template ) {
			return;
		}

		wp_enqueue_style(
			'ssw-frontend',
			SSW_URL . 'assets/frontend.css',
			array(),
			SSW_VERSION
		);

		$css_vars = self::css_vars_for_template( $template );
		wp_add_inline_style( 'ssw-frontend', $css_vars );
	}

	private static function css_vars_for_template( $template ) {
		$vars = array(
			'local-business' => array( '--ssw-primary' => '#1d4ed8', '--ssw-accent' => '#2563eb' ),
			'corporate'      => array( '--ssw-primary' => '#1e40af', '--ssw-accent' => '#2563eb' ),
			'hotel'          => array( '--ssw-primary' => '#78350f', '--ssw-accent' => '#b45309' ),
			'fashion'        => array( '--ssw-primary' => '#7c2d12', '--ssw-accent' => '#ea580c' ),
			'real-estate'    => array( '--ssw-primary' => '#0f172a', '--ssw-accent' => '#1e293b' ),
			'beauty'         => array( '--ssw-primary' => '#831843', '--ssw-accent' => '#be185d' ),
			'restaurant'     => array( '--ssw-primary' => '#7c2d12', '--ssw-accent' => '#ca8a04' ),
			'clinic'         => array( '--ssw-primary' => '#134e4a', '--ssw-accent' => '#0d9488' ),
		);

		$template_vars = isset( $vars[ $template ] ) ? $vars[ $template ] : $vars['local-business'];
		$css = ':root {';
		foreach ( $template_vars as $var => $value ) {
			$css .= $var . ':' . esc_attr( $value ) . ';';
		}
		$css .= '}';
		return $css;
	}

	public static function body_class( $classes ) {
		$template = get_option( '_ssw_template_id' );
		if ( $template ) {
			$classes[] = 'ssw-site';
			$classes[] = 'ssw-' . sanitize_html_class( $template );
		}
		return $classes;
	}
}
