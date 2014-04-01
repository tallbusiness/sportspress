<?php
/**
 * Handle frontend forms
 *
 * @class 		SP_Frontend_Scripts
 * @version		0.7
 * @package		SportsPress/Classes/
 * @category	Class
 * @author 		ThemeBoy
 */
class SP_Frontend_Scripts {

	/**
	 * Constructor
	 */
	public function __construct () {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( $this, 'check_jquery' ), 25 );;
		add_action( 'wp_print_scripts', array( $this, 'custom_css' ), 30 );;
	}

	/**
	 * Register/queue frontend scripts.
	 *
	 * @access public
	 * @return void
	 */
	public function load_scripts() {
		// Styles
		wp_enqueue_style( 'sportspress', plugin_dir_url( SP_PLUGIN_FILE ) . 'assets/css/sportspress.css', array( 'dashicons' ), time() );

		// Scripts
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array(), '3.exp', true );
		wp_enqueue_script( 'jquery-datatables', plugin_dir_url( SP_PLUGIN_FILE ) .'assets/js/jquery.dataTables.min.js', array( 'jquery' ), '1.9.4', true );
		wp_enqueue_script( 'jquery-countdown', plugin_dir_url( SP_PLUGIN_FILE ) .'assets/js/jquery.countdown.min.js', array( 'jquery' ), '2.0.2', true );
		wp_enqueue_script( 'sportspress', plugin_dir_url( SP_PLUGIN_FILE ) .'assets/js/sportspress.js', array( 'jquery' ), time(), true );

		// Localize scripts.
		wp_localize_script( 'sportspress', 'localized_strings', array( 'days' => __( 'days', 'sportspress' ), 'hrs' => __( 'hrs', 'sportspress' ), 'mins' => __( 'mins', 'sportspress' ), 'secs' => __( 'secs', 'sportspress' ), 'previous' => __( 'Previous', 'sportspress' ), 'next' => __( 'Next', 'sportspress' ) ) );
	}

	/**
	 * SP requires jQuery 1.8 since it uses functions like .on() for events and .parseHTML.
	 * If, by the time wp_print_scrips is called, jQuery is outdated (i.e not
	 * using the version in core) we need to deregister it and register the
	 * core version of the file.
	 *
	 * @access public
	 * @return void
	 */
	public function check_jquery() {
		global $wp_scripts;

		// Enforce minimum version of jQuery
		if ( ! empty( $wp_scripts->registered['jquery']->ver ) && ! empty( $wp_scripts->registered['jquery']->src ) && 0 >= version_compare( $wp_scripts->registered['jquery']->ver, '1.8' ) ) {
			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', '/wp-includes/js/jquery/jquery.js', array(), '1.8' );
			wp_enqueue_script( 'jquery' );
		}
	}

	public function custom_css() {
		$enabled = get_option( 'sportspress_enable_frontend_css', 'yes' );
		$custom = get_option( 'sportspress_custom_css', null );

		if ( $enabled == 'yes' || ! empty( $custom ) ) {

			$colors = get_option( 'sportspress_frontend_css_colors' );
			
			echo '<style type="text/css">.sp-data-table tbody a,.sp-data-table tbody a:hover,.sp-calendar tbody a,.sp-calendar tbody a:hover{background:none;}';

			if ( $enabled == 'yes' && sizeof( $colors ) > 0 ) {
				echo ' /* SportsPress Frontend CSS */ ';

				if ( isset( $colors['primary'] ) )
					echo '.sp-data-table th,.sp-calendar th,.sp-data-table tfoot,.sp-calendar tfoot{background:' . $colors['primary'] . ' !important}.sp-data-table tbody a,.sp-calendar tbody a{color:' . $colors['primary'] . ' !important}';

				if ( isset( $colors['heading'] ) )
					echo '.sp-data-table th,.sp-data-table th a,.sp-data-table tfoot,.sp-data-table tfoot a,.sp-calendar th,.sp-calendar th a,.sp-calendar tfoot,.sp-calendar tfoot a{color: ' . $colors['heading'] . ' !important}';

				if ( isset( $colors['text'] ) )
					echo '.sp-data-table tbody,.sp-calendar tbody{color: ' . $colors['text'] . ' !important}';

				if ( isset( $colors['background'] ) )
					echo '.sp-data-table tbody,.sp-calendar tbody{background: ' . $colors['background'] . ' !important}';

				if ( isset( $colors['alternate'] ) )
					echo '.sp-data-table tbody tr.odd,.sp-data-table tbody tr.alternate,.sp-calendar tbody td#today{background: ' . $colors['alternate'] . ' !important}';
			}

			if ( ! empty( $custom ) )
				echo ' /* SportsPress Custom CSS */ ' . $custom;
			
			echo '</style>';
		}
	}
}

new SP_Frontend_Scripts();