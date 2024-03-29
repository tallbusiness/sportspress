<?php
/**
 * Load assets.
 *
 * @author 		ThemeBoy
 * @category 	Admin
 * @package 	SportsPress/Admin
 * @version     1.1.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'SP_Admin_Assets' ) ) :

/**
 * SP_Admin_Assets Class
 */
class SP_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue styles
	 */
	public function admin_styles() {
		global $wp_scripts;

		// Sitewide menu CSS
		wp_enqueue_style( 'sportspress-admin-menu-styles', SP()->plugin_url() . '/assets/css/menu.css', array(), SP_VERSION );

		$screen = get_current_screen();

		if ( in_array( $screen->id, sp_get_screen_ids() ) ) {

			// Admin styles for SP pages only
			wp_enqueue_style( 'jquery-chosen', SP()->plugin_url() . '/assets/css/chosen.css', array(), '1.1.0' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'sportspress-admin', SP()->plugin_url() . '/assets/css/admin.css', array(), SP_VERSION );
		}

		if ( in_array( $screen->id, array( 'dashboard' ) ) ) {
			wp_enqueue_style( 'sportspress-admin-dashboard-styles', SP()->plugin_url() . '/assets/css/dashboard.css', array(), SP_VERSION );
		}

		if ( in_array( $screen->id, array( 'widgets' ) ) ) {
			wp_enqueue_style( 'sportspress-admin-widgets-styles', SP()->plugin_url() . '/assets/css/widgets.css', array(), SP_VERSION );
		}

		if ( in_array( $screen->id, array( 'customize' ) ) ) {
			wp_enqueue_style( 'sportspress-admin-customize-styles', SP()->plugin_url() . '/assets/css/customize.css', array(), SP_VERSION );
		}

		if ( in_array( $screen->id, array( 'sp_column', 'sp_statistic' ) ) ) {
			wp_enqueue_style( 'sportspress-admin-equation-styles', SP()->plugin_url() . '/assets/css/equation.css', array(), SP_VERSION );
		}

		if ( SP()->mode == 'player' ):
	        $custom_css = '#adminmenu #menu-posts-sp_team .menu-icon-sp_team div.wp-menu-image:before,.sp-icon-shield:before{content: "\f307"}';
	        wp_add_inline_style( 'sportspress-admin-menu-styles', $custom_css );
		endif;

		do_action( 'sportspress_admin_css' );
	}

	/**
	 * Enqueue scripts
	 */
	public function admin_scripts() {
		global $wp_query, $post;

		$screen = get_current_screen();

		// Register scripts
		wp_register_script( 'chosen', SP()->plugin_url() . '/assets/js/chosen.jquery.min.js', array( 'jquery' ), '1.1.0', true );

		wp_register_script( 'jquery-tiptip', SP()->plugin_url() . '/assets/js/jquery.tipTip.min.js', array( 'jquery' ), '1.3', true );

		wp_register_script( 'jquery-caret', SP()->plugin_url() . '/assets/js/jquery.caret.min.js', array( 'jquery' ), '1.02', true );

		wp_register_script( 'jquery-countdown', SP()->plugin_url() . '/assets/js/jquery.countdown.min.js', array( 'jquery' ), '2.0.2', true );

		wp_register_script( 'google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false&libraries=places' );

		wp_register_script( 'jquery-locationpicker', SP()->plugin_url() . '/assets/js/locationpicker.jquery.js', array( 'jquery', 'google-maps' ), '0.1.6', true );

		wp_register_script( 'sportspress-admin-locationpicker', SP()->plugin_url() . '/assets/js/admin/locationpicker.js', array( 'jquery', 'google-maps', 'jquery-locationpicker' ), SP_VERSION, true );

		wp_register_script( 'sportspress-admin-equationbuilder', SP()->plugin_url() . '/assets/js/admin/equationbuilder.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable' ), SP_VERSION, true );
	
		wp_register_script( 'sportspress-admin', SP()->plugin_url() . '/assets/js/admin/sportspress-admin.js', array( 'jquery', 'chosen', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-tiptip', 'jquery-caret', 'jquery-countdown' ), SP_VERSION, true );

		wp_register_script( 'sportspress-admin-widgets', SP()->plugin_url() . '/assets/js/admin/widgets.js', array( 'jquery' ), SP_VERSION, true );

		// SportsPress admin pages
	    if ( in_array( $screen->id, sp_get_screen_ids() ) ) {

	    	wp_enqueue_script( 'jquery' );
	    	wp_enqueue_script( 'chosen' );
	    	wp_enqueue_script( 'jquery-ui-core' );
	    	wp_enqueue_script( 'jquery-ui-draggable' );
	    	wp_enqueue_script( 'jquery-ui-droppable' );
	    	wp_enqueue_script( 'jquery-ui-sortable' );
	    	wp_enqueue_script( 'jquery-tiptip' );
	    	wp_enqueue_script( 'jquery-caret' );
	    	wp_enqueue_script( 'jquery-countdown' );
	    	wp_enqueue_script( 'sportspress-admin' );

	    	$params = array(
				'none' => __( 'None', 'sportspress' ),
				'remove_text' => __( '&mdash; Remove &mdash;', 'sportspress' ),
				'days' => __( 'days', 'sportspress' ),
				'hrs' => __( 'hrs', 'sportspress' ),
				'mins' => __( 'mins', 'sportspress' ),
				'secs' => __( 'secs', 'sportspress' )
	    	);

	    	// Localize scripts
			wp_localize_script( 'sportspress-admin', 'localized_strings', $params );
	    }

		if ( in_array( $screen->id, array( 'widgets' ) ) ) {
	    	wp_enqueue_script( 'sportspress-admin-widgets' );
		}

	    // Edit venue pages
	    if ( in_array( $screen->id, array( 'edit-sp_venue' ) ) ) {
	    	wp_enqueue_script( 'google-maps' );
	    	wp_enqueue_script( 'jquery-locationpicker' );
	    	wp_enqueue_script( 'sportspress-admin-locationpicker' );
		}

		// Edit equation
		if ( in_array( $screen->id, array( 'sp_column', 'sp_statistic' ) ) ) {
	    	wp_enqueue_script( 'sportspress-admin-equationbuilder' );
		}
	}
}

endif;

return new SP_Admin_Assets();
