<?php
/**
 * Plugin Name: Snap Sites WP
 * Description: Build and launch polished starter websites on a WordPress Multisite network.
 * Version: 1.0.0
 * Author: Snap Sites WP
 * Text Domain: snap-sites-wp
 * Domain Path: /languages
 * License: GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package SnapSitesWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SSW_PLUGIN_FILE' ) ) {
	define( 'SSW_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'SSW_PLUGIN_DIR' ) ) {
	define( 'SSW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SSW_PLUGIN_URL' ) ) {
	define( 'SSW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SSW_PLUGIN_VERSION' ) ) {
	define( 'SSW_PLUGIN_VERSION', '1.0.0' );
}

function ssw_is_multisite() {
	return is_multisite();
}

function ssw_is_super_admin() {
	return is_super_admin();
}

function ssw_get_template_definitions() {
	return array(
		'local-business' => array(
			'name'        => 'Local Business',
			'category'    => 'Business',
			'badge'       => 'Free',
			'description' => 'Simple business website for services, trades and local companies.',
			'body_class'  => 'ssw-local-business',
			'color'       => '#1d4ed8',
			'premium'     => false,
		),
		'corporate' => array(
			'name'        => 'Corporate',
			'category'    => 'Business',
			'badge'       => 'Pro',
			'description' => 'Professional company website for consulting, agencies and B2B firms.',
			'body_class'  => 'ssw-corporate',
			'color'       => '#2563eb',
			'premium'     => true,
		),
		'hotel' => array(
			'name'        => 'Hotel',
			'category'    => 'Hospitality',
			'badge'       => 'Pro',
			'description' => 'Hospitality site for hotels and guest houses with premium visuals.',
			'body_class'  => 'ssw-hotel',
			'color'       => '#b89555',
			'premium'     => true,
		),
		'fashion' => array(
			'name'        => 'Fashion',
			'category'    => 'Retail',
			'badge'       => 'Pro',
			'description' => 'Editorial boutique and fashion website for brands and stores.',
			'body_class'  => 'ssw-fashion',
			'color'       => '#a45d5d',
			'premium'     => true,
		),
		'real-estate' => array(
			'name'        => 'Real Estate',
			'category'    => 'Property',
			'badge'       => 'Pro',
			'description' => 'Property-focused website for estate agents and developers.',
			'body_class'  => 'ssw-real-estate',
			'color'       => '#16324f',
			'premium'     => true,
		),
		'beauty' => array(
			'name'        => 'Beauty',
			'category'    => 'Wellness',
			'badge'       => 'Pro',
			'description' => 'Salon and beauty-services website with a warm, elegant feel.',
			'body_class'  => 'ssw-beauty',
			'color'       => '#c98f8f',
			'premium'     => true,
		),
		'restaurant' => array(
			'name'        => 'Restaurant',
			'category'    => 'Food',
			'badge'       => 'Pro',
			'description' => 'Food and hospitality website for cafés, restaurants and bakeries.',
			'body_class'  => 'ssw-restaurant',
			'color'       => '#c66a3d',
			'premium'     => true,
		),
		'clinic' => array(
			'name'        => 'Clinic',
			'category'    => 'Healthcare',
			'badge'       => 'Pro',
			'description' => 'Professional healthcare website for clinics and wellness businesses.',
			'body_class'  => 'ssw-clinic',
			'color'       => '#2b7a78',
			'premium'     => true,
		),
	);
}

function ssw_get_plan_limits() {
	return array(
		'Free'    => 1,
		'Starter' => 1,
		'Growth'  => 3,
		'Pro'     => 6,
	);
}

function ssw_get_current_plan() {
	return apply_filters( 'ssw_current_plan', 'Pro' );
}

function ssw_get_active_site_count() {
	if ( ! is_multisite() ) {
		return 0;
	}

	$sites = get_sites(
		array(
			'number'     => 0,
			'network_id' => get_current_network_id(),
		)
	);

	$count = 0;
	foreach ( $sites as $site ) {
		$template = get_blog_option( $site->blog_id, 'ssw_template_used' );
		if ( ! empty( $template ) ) {
			$count++;
		}
	}

	return $count;
}

function ssw_get_plan_limit_for_current_plan() {
	$plan   = ssw_get_current_plan();
	$limits = ssw_get_plan_limits();
	return isset( $limits[ $plan ] ) ? $limits[ $plan ] : 1;
}

function ssw_get_created_sites() {
	if ( ! is_multisite() ) {
		return array();
	}

	$sites = get_sites(
		array(
			'number'     => 0,
			'network_id' => get_current_network_id(),
			'order'      => 'DESC',
			'orderby'    => 'registered',
		)
	);

	$items = array();
	foreach ( $sites as $site ) {
		$template = get_blog_option( $site->blog_id, 'ssw_template_used' );
		if ( empty( $template ) ) {
			continue;
		}

		$items[] = array(
			'blog_id'   => $site->blog_id,
			'template'  => $template,
			'site_name' => get_blog_option( $site->blog_id, 'blogname' ),
			'url'       => get_site_url( $site->blog_id ),
			'admin_url' => get_admin_url( $site->blog_id ),
			'created'   => get_blog_option( $site->blog_id, 'ssw_created_on' ),
		);
	}

	return $items;
}

function ssw_user_can_access_template( $template_id, $plan = '' ) {
	if ( ! is_multisite() ) {
		return false;
	}

	if ( ! is_super_admin() ) {
		return false;
	}

	return apply_filters( 'ssw_user_can_access_template', true, $template_id, $plan );
}

function ssw_enqueue_admin_assets( $hook ) {
	if ( 'toplevel_page_snap-sites' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'ssw-admin-style',
		SSW_PLUGIN_URL . 'assets/css/snap-sites-admin.css',
		array(),
		SSW_PLUGIN_VERSION
	);

	wp_enqueue_script(
		'ssw-admin-script',
		SSW_PLUGIN_URL . 'assets/js/snap-sites-admin.js',
		array( 'jquery' ),
		SSW_PLUGIN_VERSION,
		true
	);

	wp_localize_script(
		'ssw-admin-script',
		'sswAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ssw_create_site_nonce' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'ssw_enqueue_admin_assets' );

function ssw_register_network_admin_menu() {
	if ( ! is_multisite() ) {
		return;
	}

	add_menu_page(
		'Snap Sites',
		'Snap Sites',
		'manage_network',
		'snap-sites',
		'ssw_render_dashboard_page',
		'dashicons-admin-multisite',
		26
	);
}
add_action( 'network_admin_menu', 'ssw_register_network_admin_menu' );

function ssw_render_dashboard_page() {
	if ( ! is_multisite() || ! is_super_admin() ) {
		wp_die( 'Access denied.' );
	}

	$templates    = ssw_get_template_definitions();
	$current_plan = ssw_get_current_plan();
	$limit        = ssw_get_plan_limit_for_current_plan();
	$used         = ssw_get_active_site_count();
	$sites        = ssw_get_created_sites();
	?>
	<div class="wrap ssw-admin-wrap">
		<h1>Snap Sites</h1>
		<div class="ssw-plan-box">
			<div>
				<strong>Current Plan:</strong> <?php echo esc_html( $current_plan ); ?><br>
				<strong>Sites used:</strong> <?php echo esc_html( $used ); ?> / <?php echo esc_html( $limit ); ?>
			</div>
		</div>

		<h2>Create a New Site</h2>
		<div class="ssw-template-grid">
			<?php foreach ( $templates as $template_id => $template ) : ?>
				<div class="ssw-template-card">
					<div class="ssw-template-preview <?php echo esc_attr( 'ssw-preview-' . sanitize_html_class( $template_id ) ); ?>">
						<span class="ssw-card-badge"><?php echo esc_html( $template['badge'] ); ?></span>
					</div>
					<div class="ssw-template-content">
						<h3><?php echo esc_html( $template['name'] ); ?></h3>
						<p><?php echo esc_html( $template['description'] ); ?></p>
						<div class="ssw-template-meta"><?php echo esc_html( $template['category'] ); ?></div>
						<button type="button" class="button button-primary ssw-create-site-button" data-template-id="<?php echo esc_attr( $template_id ); ?>" <?php echo ( $used >= $limit ) ? 'disabled' : ''; ?>>Create Site</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="ssw-form-box">
			<h2>Site Details</h2>
			<table class="form-table">
				<tr>
					<th><label for="ssw_customer_email">Customer email</label></th>
					<td><input type="email" id="ssw_customer_email" name="customer_email" class="regular-text" placeholder="hello@example.com" /></td>
				</tr>
				<tr>
					<th><label for="ssw_business_name">Business / site name</label></th>
					<td><input type="text" id="ssw_business_name" name="business_name" class="regular-text" placeholder="My Business" /></td>
				</tr>
			</table>
		</div>

		<div id="ssw-result-box" class="ssw-result-box" aria-live="polite" aria-atomic="true"></div>

		<div class="ssw-site-list-box">
			<h2>Created Sites</h2>
			<?php if ( empty( $sites ) ) : ?>
				<p>No Snap Sites have been created yet.</p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th>Business / Site</th>
							<th>Template</th>
							<th>Site URL</th>
							<th>Admin URL</th>
							<th>Date Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $sites as $site ) : ?>
							<tr>
								<td><?php echo esc_html( $site['site_name'] ); ?></td>
								<td><?php echo esc_html( $templates[ $site['template'] ]['name'] ?? ucfirst( str_replace( '-', ' ', $site['template'] ) ) ); ?></td>
								<td><a href="<?php echo esc_url( $site['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( wp_parse_url( $site['url'], PHP_URL_HOST ) ); ?></a></td>
								<td><a href="<?php echo esc_url( $site['admin_url'] ); ?>" target="_blank" rel="noopener noreferrer">Dashboard</a></td>
								<td><?php echo ! empty( $site['created'] ) ? esc_html( gmdate( 'M d, Y', strtotime( $site['created'] ) ) ) : '&mdash;'; ?></td>
								<td><a href="<?php echo esc_url( $site['url'] ); ?>" class="button button-small" target="_blank" rel="noopener noreferrer">Visit</a> <a href="<?php echo esc_url( $site['admin_url'] ); ?>" class="button button-small" target="_blank" rel="noopener noreferrer">Edit</a></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="ssw-plan-box ssw-license-box">
			<h2>License & Plan</h2>
			<p><strong>Plan:</strong> <?php echo esc_html( $current_plan ); ?></p>
			<p><strong>Sites allowed:</strong> <?php echo esc_html( $limit ); ?></p>
			<p><strong>Sites used:</strong> <?php echo esc_html( $used ); ?></p>
			<p><strong>License status:</strong> Active</p>
		</div>
	</div>
	<?php
}

function ssw_detect_template_slug( $template_id ) {
	$template_id = sanitize_key( $template_id );
	return preg_replace( '/[^a-z0-9-]+/', '-', strtolower( $template_id ) );
}

function ssw_create_page( $title, $content, $slug ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => sanitize_text_field( $title ),
			'post_content' => wp_kses_post( $content ),
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
			'post_type'    => 'page',
			'post_name'    => sanitize_title( $slug ),
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		return $page_id;
	}

	return $page_id;
}

function ssw_generate_site_slug( $template_id ) {
	$base = ssw_detect_template_slug( $template_id );
	$random = substr( md5( $base . microtime() . wp_rand( 10000, 99999 ) ), 0, 6 );
	return $base . '-' . $random;
}

function ssw_create_navigation_menu( $template_id, $pages ) {
	$menu_name = ucfirst( str_replace( '-', ' ', $template_id ) ) . ' Menu';

	$menu_exists = wp_get_nav_menu_object( $menu_name );
	$menu_id = $menu_exists ? $menu_exists->term_id : wp_create_nav_menu( $menu_name );
	if ( is_wp_error( $menu_id ) ) {
		return false;
	}

	foreach ( $pages as $page ) {
		if ( empty( $page['id'] ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $page['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page['id'],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	$locations = get_theme_mod( 'nav_menu_locations' ) ? get_theme_mod( 'nav_menu_locations' ) : array();
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return $menu_id;
}

function ssw_set_front_page( $page_id ) {
	if ( ! $page_id ) {
		return;
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', intval( $page_id ) );
}

function ssw_apply_template_class( $template_id ) {
	$template_definitions = ssw_get_template_definitions();
	$body_class = isset( $template_definitions[ $template_id ]['body_class'] ) ? 'ssw-template ' . $template_definitions[ $template_id ]['body_class'] : 'ssw-template';
	update_option( 'ssw_template_body_class', $body_class );
}

function ssw_setup_template_pages( $template_id, $pages ) {
	$page_ids = array();

	foreach ( $pages as $page_definition ) {
		$page_id = ssw_create_page( $page_definition['title'], $page_definition['content'], $page_definition['slug'] );
		if ( is_wp_error( $page_id ) || ! $page_id ) {
			continue;
		}
		$page_ids[] = array( 'id' => $page_id, 'title' => $page_definition['title'] );
	}

	$home_id = 0;
	foreach ( $page_ids as $page ) {
		if ( 'home' === strtolower( $page['title'] ) ) {
			$home_id = $page['id'];
			break;
		}
	}

	if ( $home_id ) {
		ssw_set_front_page( $home_id );
	}

	ssw_apply_template_class( $template_id );
	ssw_create_navigation_menu( $template_id, $page_ids );

	return $page_ids;
}

function ssw_get_template_setup_function( $template_id ) {
	$snake_case = str_replace( '-', '_', sanitize_key( $template_id ) );
	return 'ssw_setup_' . $snake_case . '_template';
}

function ssw_setup_local_business_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-local-business"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>Helping Local Businesses Grow</h1><p>Professional website support for local service businesses, trades and growing companies.</p><div class="ssw-button-row"><a class="ssw-button" href="#services">Our Services</a><a class="ssw-button-secondary" href="/contact/">Contact Us</a></div></div><div class="ssw-image-panel">Business Placeholder</div></section></div></div>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>About Your Business</h2><p>Your Business Name provides practical service, local support and dependable care for customers looking for trustworthy help.</p>',
		),
		array(
			'title'   => 'Services',
			'slug'    => 'services',
			'content' => '<h2>Services</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Service Name</h3><p>Describe your service in a short, editable paragraph.</p></div><div class="ssw-card"><h3>Service Name</h3><p>Describe your service in a short, editable paragraph.</p></div><div class="ssw-card"><h3>Service Name</h3><p>Describe your service in a short, editable paragraph.</p></div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Contact</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p>',
		),
	);
	return ssw_setup_template_pages( 'local-business', $pages );
}

function ssw_setup_corporate_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-corporate"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>Helping Businesses Move Forward</h1><p>Build a stronger business with practical solutions designed around your goals.</p><div class="ssw-button-row"><a class="ssw-button" href="/services/">Explore Our Services</a><a class="ssw-button-secondary" href="/contact/">Contact Us</a></div></div><div class="ssw-image-panel">Corporate Placeholder</div></section><section class="ssw-section"><div class="ssw-card-grid"><div class="ssw-card"><h3>Professional Service</h3></div><div class="ssw-card"><h3>Practical Solutions</h3></div><div class="ssw-card"><h3>Reliable Support</h3></div></div></section><section class="ssw-section"><div class="ssw-section-header"><h2>A Business Partner You Can Rely On</h2><p>We help clients solve problems, improve operations and achieve business goals.</p></div></section></div></div>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>A Business Partner You Can Rely On</h2><p>We help clients solve problems, improve operations and achieve business goals with flexible, practical support tailored to changing needs.</p>',
		),
		array(
			'title'   => 'Services',
			'slug'    => 'services',
			'content' => '<h2>Our Services</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Business Consulting</h3><p>Practical support for strategic direction and business improvement.</p></div><div class="ssw-card"><h3>Operations Support</h3><p>Streamlined delivery for teams and day-to-day performance.</p></div><div class="ssw-card"><h3>Strategy & Planning</h3><p>Clear planning around priorities, budgets and milestones.</p></div><div class="ssw-card"><h3>Professional Services</h3><p>Flexible, value-driven support for operational goals.</p></div></div>',
		),
		array(
			'title'   => 'Projects',
			'slug'    => 'projects',
			'content' => '<h2>Projects</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Sample Project</h3><p>Sample project — replace this placeholder with a real case study or portfolio item.</p></div><div class="ssw-card"><h3>Sample Project</h3><p>Sample project — replace this placeholder with a real case study or portfolio item.</p></div><div class="ssw-card"><h3>Sample Project</h3><p>Sample project — replace this placeholder with a real case study or portfolio item.</p></div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Contact Us</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p>',
		),
	);
	return ssw_setup_template_pages( 'corporate', $pages );
}

function ssw_setup_hotel_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-hotel"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>A Comfortable Stay, Made Simple</h1><p>Relax, recharge and enjoy a welcoming stay designed around comfort and convenience.</p><div class="ssw-button-row"><a class="ssw-button" href="/rooms/">View Rooms</a><a class="ssw-button-secondary" href="/contact/">Contact Us</a></div></div><div class="ssw-image-panel">Hospitality Placeholder</div></section><section class="ssw-section"><div class="ssw-section-header"><h2>Welcome to Your Stay</h2><p>Your guest experience begins with thoughtful service, clean comfort and effortless convenience.</p></div></section><section class="ssw-section"><div class="ssw-card-grid"><div class="ssw-card"><h3>Standard Room</h3><p>Simple, calm and beautifully equipped.</p><p>From $XX</p></div><div class="ssw-card"><h3>Deluxe Room</h3><p>Comfortable, bright and restful.</p><p>From $XX</p></div><div class="ssw-card"><h3>Executive Suite</h3><p>More space, extra comfort and a premium stay.</p><p>From $XX</p></div></div></section></div></div>',
		),
		array(
			'title'   => 'Rooms',
			'slug'    => 'rooms',
			'content' => '<h2>Our Rooms</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Standard Room</h3><p>Comfortable stay with a simple, relaxed feel.</p><p>From $XX</p></div><div class="ssw-card"><h3>Deluxe Room</h3><p>Spacious guest rooms designed for longer stays.</p><p>From $XX</p></div><div class="ssw-card"><h3>Executive Suite</h3><p>Extra room, thoughtful details and elevated comfort.</p><p>From $XX</p></div></div>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>Welcome to Your Stay</h2><p>Welcome to Your Business Name, where comfort, convenience and thoughtful service help every guest feel at home.</p><h3>Amenities</h3><ul><li>Wi-Fi</li><li>Air Conditioning</li><li>Parking</li><li>Breakfast</li><li>Housekeeping</li><li>Guest Support</li></ul>',
		),
		array(
			'title'   => 'Gallery',
			'slug'    => 'gallery',
			'content' => '<h2>Gallery</h2><div class="ssw-gallery-grid"><div class="ssw-gallery-item">Room</div><div class="ssw-gallery-item">Lobby</div><div class="ssw-gallery-item">Pool</div><div class="ssw-gallery-item">Breakfast</div><div class="ssw-gallery-item">Exterior</div><div class="ssw-gallery-item">Suite</div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Find Us</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p><p><em>Optional map placeholder.</em></p>',
		),
	);
	return ssw_setup_template_pages( 'hotel', $pages );
}

function ssw_setup_fashion_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-fashion"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>Style Made for Your Everyday</h1><p>Discover thoughtfully selected pieces designed to help you express your style.</p><div class="ssw-button-row"><a class="ssw-button" href="/shop/">Explore Collection</a><a class="ssw-button-secondary" href="/about/">Our Story</a></div></div><div class="ssw-image-panel">Fashion Placeholder</div></section><section class="ssw-section"><div class="ssw-card-grid"><div class="ssw-card"><h3>Women</h3></div><div class="ssw-card"><h3>Men</h3></div><div class="ssw-card"><h3>Accessories</h3></div></div></section></div></div>',
		),
		array(
			'title'   => 'Shop',
			'slug'    => 'shop',
			'content' => '<h2>Shop / Collection</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>New Arrival</h3><p>Item Name — $XX</p></div><div class="ssw-card"><h3>New Arrival</h3><p>Item Name — $XX</p></div><div class="ssw-card"><h3>New Arrival</h3><p>Item Name — $XX</p></div><div class="ssw-card"><h3>New Arrival</h3><p>Item Name — $XX</p></div></div>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>More Than What You Wear</h2><p>Our collections are built to help customers feel confident, expressive and comfortable in everyday moments.</p>',
		),
		array(
			'title'   => 'Lookbook',
			'slug'    => 'lookbook',
			'content' => '<h2>Lookbook</h2><div class="ssw-gallery-grid"><div class="ssw-gallery-item">Look 1</div><div class="ssw-gallery-item">Look 2</div><div class="ssw-gallery-item">Look 3</div><div class="ssw-gallery-item">Look 4</div><div class="ssw-gallery-item">Look 5</div><div class="ssw-gallery-item">Look 6</div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Contact</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p>',
		),
	);
	return ssw_setup_template_pages( 'fashion', $pages );
}

function ssw_setup_real_estate_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-real-estate"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>Find a Property That Feels Right</h1><p>Explore homes, commercial spaces and property opportunities suited to your needs.</p><div class="ssw-button-row"><a class="ssw-button" href="/properties/">View Properties</a><a class="ssw-button-secondary" href="/contact/">Speak to an Agent</a></div></div><div class="ssw-image-panel">Property Placeholder</div></section><section class="ssw-section"><div class="ssw-card-grid"><div class="ssw-card"><h3>Location</h3><p>Type here for a search field placeholder.</p></div><div class="ssw-card"><h3>Property Type</h3><p>Display-only starter filters.</p></div><div class="ssw-card"><h3>Price Range</h3><p>Use later for filtered listings.</p></div></div></section></div></div>',
		),
		array(
			'title'   => 'Properties',
			'slug'    => 'properties',
			'content' => '<h2>Featured Properties</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Demo Property</h3><p>Location: Your City</p><p>Price: $XXX</p><p>Type: Residential</p><p>Bedrooms: 3 | Bathrooms: 2</p></div><div class="ssw-card"><h3>Demo Property</h3><p>Location: Your City</p><p>Price: $XXX</p><p>Type: Residential</p><p>Bedrooms: 4 | Bathrooms: 3</p></div><div class="ssw-card"><h3>Demo Property</h3><p>Location: Your City</p><p>Price: $XXX</p><p>Type: Commercial</p><p>Bedrooms: 2 | Bathrooms: 2</p></div></div>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>About Us</h2><p>We help buyers, sellers and investors find the right opportunity with clear guidance and local knowledge.</p>',
		),
		array(
			'title'   => 'Services',
			'slug'    => 'services',
			'content' => '<h2>Our Services</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Property Sales</h3></div><div class="ssw-card"><h3>Property Rentals</h3></div><div class="ssw-card"><h3>Property Management</h3></div><div class="ssw-card"><h3>Investment Support</h3></div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Contact Us</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p>',
		),
	);
	return ssw_setup_template_pages( 'real-estate', $pages );
}

function ssw_setup_beauty_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-beauty"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>Feel Good. Look Your Best.</h1><p>Professional beauty services in a relaxing environment designed around you.</p><div class="ssw-button-row"><a class="ssw-button" href="/services/">View Services</a><a class="ssw-button-secondary" href="/contact/">Contact Us</a></div></div><div class="ssw-image-panel">Beauty Placeholder</div></section><section class="ssw-section"><div class="ssw-card-grid"><div class="ssw-card"><h3>Hair</h3><p>Style and finishing services.</p></div><div class="ssw-card"><h3>Nails</h3><p>Manicures and polished finishes.</p></div><div class="ssw-card"><h3>Facials</h3><p>Skin and glow-focused treatments.</p></div><div class="ssw-card"><h3>Beauty Treatments</h3><p>Tailored care and finishing touches.</p></div></div></section></div></div>',
		),
		array(
			'title'   => 'Services',
			'slug'    => 'services',
			'content' => '<h2>Our Services</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Hair</h3><p>Professional cutting, styling and treatments.</p></div><div class="ssw-card"><h3>Nails</h3><p>Manicures, pedicures and nail art.</p></div><div class="ssw-card"><h3>Facials</h3><p>Skin therapy and glow treatments.</p></div><div class="ssw-card"><h3>Beauty Treatments</h3><p>Waxing, threading and finishing touches.</p></div></div>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>Care in Every Detail</h2><p>Your Business Name offers a calm, polished experience with personalised beauty treatments in a welcoming environment.</p>',
		),
		array(
			'title'   => 'Gallery',
			'slug'    => 'gallery',
			'content' => '<h2>Gallery</h2><div class="ssw-gallery-grid"><div class="ssw-gallery-item">Salon</div><div class="ssw-gallery-item">Nails</div><div class="ssw-gallery-item">Hair</div><div class="ssw-gallery-item">Spa</div><div class="ssw-gallery-item">Beauty</div><div class="ssw-gallery-item">Treatment</div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Contact Us</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p>',
		),
	);
	return ssw_setup_template_pages( 'beauty', $pages );
}

function ssw_setup_restaurant_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-restaurant"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>Good Food. Good Moments.</h1><p>Freshly prepared dishes served in a warm and welcoming setting.</p><div class="ssw-button-row"><a class="ssw-button" href="/menu/">View Menu</a><a class="ssw-button-secondary" href="/contact/">Find Us</a></div></div><div class="ssw-image-panel">Food Placeholder</div></section><section class="ssw-section"><div class="ssw-card-grid"><div class="ssw-card"><h3>Popular Dish</h3><p>Freshly made and full of flavour.</p></div><div class="ssw-card"><h3>Popular Dish</h3><p>Freshly made and full of flavour.</p></div><div class="ssw-card"><h3>Popular Dish</h3><p>Freshly made and full of flavour.</p></div></div></section></div></div>',
		),
		array(
			'title'   => 'Menu',
			'slug'    => 'menu',
			'content' => '<h2>Menu</h2><h3>Starters</h3><p>House Salad — $XX</p><p>Soup of the Day — $XX</p><h3>Mains</h3><p>Signature Grill — $XX</p><p>Chef Special — $XX</p><h3>Drinks</h3><p>Fresh Juice — $XX</p><p>House Blend Coffee — $XX</p><h3>Desserts</h3><p>Chef Dessert — $XX</p>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>Made With Care</h2><p>We create welcoming dining experiences with thoughtful food, warm service and a relaxed atmosphere.</p>',
		),
		array(
			'title'   => 'Gallery',
			'slug'    => 'gallery',
			'content' => '<h2>Gallery</h2><div class="ssw-gallery-grid"><div class="ssw-gallery-item">Food</div><div class="ssw-gallery-item">Dining</div><div class="ssw-gallery-item">Kitchen</div><div class="ssw-gallery-item">Dessert</div><div class="ssw-gallery-item">Venue</div><div class="ssw-gallery-item">Drinks</div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Contact Us</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p><p><strong>Opening Hours:</strong></p><p>Monday – Friday: XX:XX – XX:XX<br>Saturday: XX:XX – XX:XX<br>Sunday: XX:XX – XX:XX</p>',
		),
	);
	return ssw_setup_template_pages( 'restaurant', $pages );
}

function ssw_setup_clinic_template() {
	$pages = array(
		array(
			'title'   => 'Home',
			'slug'    => 'home',
			'content' => '<div class="ssw-template ssw-clinic"><div class="ssw-site-shell"><section class="ssw-hero"><div><h1>Professional Care, Close to You</h1><p>Providing accessible healthcare services in a welcoming and professional environment.</p><div class="ssw-button-row"><a class="ssw-button" href="/services/">View Services</a><a class="ssw-button-secondary" href="/contact/">Contact the Clinic</a></div></div><div class="ssw-image-panel">Healthcare Placeholder</div></section><section class="ssw-section"><div class="ssw-card-grid"><div class="ssw-card"><h3>General Consultation</h3><p>Friendly, approachable care for everyday concerns.</p></div><div class="ssw-card"><h3>Health Screening</h3><p>Routine and preventive checks.</p></div><div class="ssw-card"><h3>Follow-Up Care</h3><p>Continued, consistent support.</p></div><div class="ssw-card"><h3>Specialist Services</h3><p>Targeted care with clear next steps.</p></div></div></section></div></div>',
		),
		array(
			'title'   => 'Services',
			'slug'    => 'services',
			'content' => '<h2>Our Services</h2><ul><li>General Consultation</li><li>Health Screening</li><li>Follow-Up Care</li><li>Specialist Services</li></ul>',
		),
		array(
			'title'   => 'About',
			'slug'    => 'about',
			'content' => '<h2>Care Built Around People</h2><p>We focus on accessible, compassionate care in a supportive environment.</p>',
		),
		array(
			'title'   => 'Team',
			'slug'    => 'team',
			'content' => '<h2>Meet the Team</h2><div class="ssw-card-grid"><div class="ssw-card"><h3>Name Placeholder</h3><p>Role Placeholder</p><p>Short biography placeholder.</p></div><div class="ssw-card"><h3>Name Placeholder</h3><p>Role Placeholder</p><p>Short biography placeholder.</p></div><div class="ssw-card"><h3>Name Placeholder</h3><p>Role Placeholder</p><p>Short biography placeholder.</p></div></div>',
		),
		array(
			'title'   => 'Contact',
			'slug'    => 'contact',
			'content' => '<h2>Contact Us</h2><p>Your Address</p><p>Your Phone Number</p><p><a href="mailto:hello@example.com">hello@example.com</a></p><p><strong>Opening Hours:</strong></p><p>Monday – Friday: XX:XX – XX:XX</p><p style="padding: 14px; background: #f0f9ff; border-left: 4px solid #2b7a78;">This website does not provide emergency medical assistance. Contact your local emergency service when urgent help is required.</p>',
		),
	);
	return ssw_setup_template_pages( 'clinic', $pages );
}

function ssw_handle_site_creation() {
	try {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ssw_create_site_nonce' ) ) {
			throw new Exception( 'Security verification failed. Please try again.' );
		}

		if ( ! is_multisite() ) {
			throw new Exception( 'This plugin requires a WordPress Multisite network.' );
		}

		if ( ! is_super_admin() ) {
			throw new Exception( 'Only network super admins can create Snap Sites.' );
		}

		$template_id = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( $_POST['template_id'] ) ) : '';
		$templates = ssw_get_template_definitions();
		if ( empty( $template_id ) || ! array_key_exists( $template_id, $templates ) ) {
			throw new Exception( 'Please choose a valid starter template.' );
		}

		if ( ! ssw_user_can_access_template( $template_id, ssw_get_current_plan() ) ) {
			throw new Exception( 'This starter template is not available for the current plan.' );
		}

		$limit = ssw_get_plan_limit_for_current_plan();
		$used = ssw_get_active_site_count();
		if ( $used >= $limit ) {
			throw new Exception( 'You\'ve reached your plan limit. Upgrade to create more sites.' );
		}

		$business_name = isset( $_POST['business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['business_name'] ) ) : '';
		$business_name = trim( $business_name );
		if ( empty( $business_name ) ) {
			$business_name = 'My Business';
		}

		$customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';
		if ( empty( $customer_email ) ) {
			$customer_email = 'hello@example.com';
		}

		$site_slug = ssw_generate_site_slug( $template_id );
		$network = get_network();
		if ( ! $network ) {
			throw new Exception( 'Unable to retrieve network information.' );
		}

		$username = 'admin-' . substr( md5( $site_slug . microtime() ), 0, 8 );
		$password = wp_generate_password( 18, true, true );

		$user_id = username_exists( $username );
		if ( ! $user_id ) {
			$user_id = wp_create_user( $username, $password, $customer_email );
		}

		if ( is_wp_error( $user_id ) ) {
			throw new Exception( $user_id->get_error_message() );
		}

		$new_site_id = wpmu_create_blog(
			$network->domain,
			'/' . $site_slug . '/',
			$business_name,
			$user_id,
			array( 'public' => 1 ),
			$network->id
		);

		if ( is_wp_error( $new_site_id ) ) {
			throw new Exception( $new_site_id->get_error_message() );
		}

		$new_blog = get_blog_details( array( 'blog_id' => $new_site_id ) );
		if ( ! $new_blog ) {
			throw new Exception( 'The new site could not be loaded after creation.' );
		}

		add_user_to_blog( $new_site_id, $user_id, 'administrator' );

		switch_to_blog( $new_site_id );
		update_blog_option( $new_site_id, 'ssw_template_used', $template_id );
		update_blog_option( $new_site_id, 'ssw_site_status', 'active' );
		update_blog_option( $new_site_id, 'ssw_business_name', $business_name );
		update_blog_option( $new_site_id, 'ssw_created_on', current_time( 'mysql' ) );
		update_option( 'blogname', $business_name );
		update_option( 'blogdescription', 'Your tagline here' );

		$hello_post = get_page_by_title( 'Hello World' );
		if ( $hello_post ) {
			wp_delete_post( $hello_post->ID, true );
		}

		$sample_page = get_page_by_title( 'Sample Page' );
		if ( $sample_page ) {
			wp_delete_post( $sample_page->ID, true );
		}

		$setup_function = ssw_get_template_setup_function( $template_id );
		if ( function_exists( $setup_function ) ) {
			call_user_func( $setup_function );
		}

		restore_current_blog();

		wp_send_json_success(
			array(
				'site_url'   => get_site_url( $new_site_id ),
				'admin_url'  => get_admin_url( $new_site_id ),
				'username'   => $username,
				'password'   => $password,
				'template'   => $template_id,
				'site_title' => $business_name,
			)
		);
	} catch ( Throwable $e ) {
		wp_send_json_error( array( 'message' => $e->getMessage() ) );
	}
}
add_action( 'wp_ajax_ssw_create_site', 'ssw_handle_site_creation' );

function ssw_body_class_filter( $classes ) {
	$template_class = get_option( 'ssw_template_body_class' );
	if ( ! empty( $template_class ) ) {
		$class_array = explode( ' ', $template_class );
		foreach ( $class_array as $class_name ) {
			if ( ! empty( $class_name ) && ! in_array( $class_name, $classes, true ) ) {
				$classes[] = $class_name;
			}
		}
	}
	return $classes;
}
add_filter( 'body_class', 'ssw_body_class_filter' );

function ssw_enqueue_frontend_assets() {
	wp_enqueue_style(
		'ssw-frontend-style',
		SSW_PLUGIN_URL . 'assets/css/snap-sites-frontend.css',
		array(),
		SSW_PLUGIN_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'ssw_enqueue_frontend_assets' );

function ssw_plugin_activation() {
	if ( ! is_multisite() ) {
		wp_die( 'Snap Sites WP requires WordPress Multisite to be enabled.' );
	}
}
register_activation_hook( SSW_PLUGIN_FILE, 'ssw_plugin_activation' );
