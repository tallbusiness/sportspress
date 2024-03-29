<?php
/**
 * Plugin Name: SportsPress
 * Plugin URI: http://themeboy.com/sportspress/
 * Description: Manage your club and its players, staff, events, league tables, and player lists.
 * Version: 1.1.7
 * Author: ThemeBoy
 * Author URI: http://themeboy.com/
 * Requires at least: 3.8
 * Tested up to: 3.9.1
 *
 * Text Domain: sportspress
 * Domain Path: /languages/
 *
 * @package SportsPress
 * @category Core
 * @author ThemeBoy
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'SportsPress' ) ) :

/**
 * Main SportsPress Class
 *
 * @class SportsPress
 * @version	1.1.7
 */
final class SportsPress {

	/**
	 * @var string
	 */
	public $version = '1.1.7';

	/**
	 * @var SporsPress The single instance of the class
	 * @since 0.7
	 */
	protected static $_instance = null;

	/**
	 * @var SP_Countries $countries
	 */
	public $countries = null;

	/**
	 * @var SP_Formats $formats
	 */
	public $formats = null;

	/**
	 * @var array
	 */
	public $text = array();

	/**
	 * @var string
	 */
	public $mode = 'team';

	/**
	 * Main SportsPress Instance
	 *
	 * Ensures only one instance of SportsPress is loaded or can be loaded.
	 *
	 * @since 0.7
	 * @static
	 * @see SP()
	 * @return SportsPress - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 0.7
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sportspress' ), '0.7' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 0.7
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'sportspress' ), '0.7' );
	}

	/**
	 * SportsPress Constructor.
	 * @access public
	 * @return SportsPress
	 */
	public function __construct() {
		// Auto-load classes on demand
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		// Define constants
		$this->define_constants();

		// Include required files
		$this->includes();

		// Hooks
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		add_action( 'widgets_init', array( $this, 'include_widgets' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( $this, 'include_template_functions' ) );
		add_action( 'init', array( 'SP_Shortcodes', 'init' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );

		// Loaded action
		do_action( 'sportspress_loaded' );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links
	 * @return array
	 */
	public function action_links( $links ) {
		return array_merge( array(
			'<a href="' . admin_url( 'admin.php?page=sportspress' ) . '">' . __( 'Settings', 'sportspress' ) . '</a>',
			'<a href="' . apply_filters( 'sportspress_themes_url', 'http://themeboy.com/sportspress/themes/' ) . '">' . __( 'Themes', 'sportspress' ) . '</a>',
			'<a href="' . apply_filters( 'sportspress_extensions_url', 'http://themeboy.com/sportspress/extensions/' ) . '">' . __( 'Extensions', 'sportspress' ) . '</a>',
		), $links );
	}

	/**
	 * Auto-load SP classes on demand to reduce memory consumption.
	 *
	 * @param mixed $class
	 * @return void
	 */
	public function autoload( $class ) {
		$path  = null;
		$class = strtolower( $class );
		$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

		if ( strpos( $class, 'sp_shortcode_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/shortcodes/';
		} elseif ( strpos( $class, 'sp_meta_box' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/admin/post-types/meta-boxes/';
		} elseif ( strpos( $class, 'sp_admin' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/admin/';
		}

		if ( $path && is_readable( $path . $file ) ) {
			include_once( $path . $file );
			return;
		}

		// Fallback
		if ( strpos( $class, 'sp_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/';
		}

		if ( $path && is_readable( $path . $file ) ) {
			include_once( $path . $file );
			return;
		}
	}

	/**
	 * Define SP Constants.
	 */
	private function define_constants() {
		define( 'SP_PLUGIN_FILE', __FILE__ );
		define( 'SP_VERSION', $this->version );

		if ( ! defined( 'SP_TEMPLATE_PATH' ) ) {
			define( 'SP_TEMPLATE_PATH', $this->template_path() );
		}

		if ( ! defined( 'SP_DELIMITER' ) ) {
			define( 'SP_DELIMITER', '|' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes() {
		include_once( 'includes/sp-core-functions.php' );
		include_once( 'includes/class-sp-install.php' );

		if ( is_admin() ) {
			include_once( 'includes/admin/class-sp-admin.php' );
		}

		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			$this->frontend_includes();
		}

		// Post types
		include_once( 'includes/class-sp-post-types.php' );						// Registers post types

		// Include abstract classes
		include_once( 'includes/abstracts/abstract-sp-custom-post.php' );		// Custom posts

		// Classes (used on all pages)
		include_once( 'includes/class-sp-countries.php' );						// Defines continents and countries
		include_once( 'includes/class-sp-formats.php' );						// Defines custom post type formats

		// Include template hooks in time for themes to remove/modify them
		include_once( 'includes/sp-template-hooks.php' );
	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {
		include_once( 'includes/class-sp-template-loader.php' );		// Template Loader
		include_once( 'includes/class-sp-frontend-scripts.php' );		// Frontend Scripts
		include_once( 'includes/class-sp-shortcodes.php' );				// Shortcodes class
	}

	/**
	 * Function used to Init SportsPress Template Functions - This makes them pluggable by plugins and themes.
	 */
	public function include_template_functions() {
		include_once( 'includes/sp-template-functions.php' );
	}

	/**
	 * Include core widgets
	 */
	public function include_widgets() {
		include_once( 'includes/widgets/class-sp-widget-countdown.php' );
		include_once( 'includes/widgets/class-sp-widget-event-calendar.php' );
		include_once( 'includes/widgets/class-sp-widget-event-list.php' );
		include_once( 'includes/widgets/class-sp-widget-event-blocks.php' );
		include_once( 'includes/widgets/class-sp-widget-league-table.php' );
		include_once( 'includes/widgets/class-sp-widget-player-list.php' );
		include_once( 'includes/widgets/class-sp-widget-player-gallery.php' );

		do_action( 'sportspress_widgets' );
	}

	/**
	 * Init SportsPress when WordPress Initialises.
	 */
	public function init() {
		// Before init action
		do_action( 'before_sportspress_init' );

		// Set up localisation
		$this->load_plugin_textdomain();

		// Load class instances
		$this->countries = new SP_Countries();	// Countries class
		$this->formats = new SP_Formats();		// Formats class

		// Load string options
		$this->text = get_option( 'sportspress_text', array() );

		// Get mode option
		$this->mode = sp_get_option( 'sportspress_mode', 'team' );

		// Init action
		do_action( 'sportspress_init' );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'sportspress' );
		
		// Global + Frontend Locale
		load_textdomain( 'sportspress', WP_LANG_DIR . "/sportspress/sportspress-$locale.mo" );
		load_plugin_textdomain( 'sportspress', false, plugin_basename( dirname( __FILE__ ) . "/languages" ) );
	}

	/**
	 * Ensure theme and server variable compatibility and setup image sizes.
	 */
	public function setup_environment() {
		add_theme_support( 'post-thumbnails' );
		
		// Standard (3:2)
		add_image_size( 'sportspress-standard', 640, 480, true );
		add_image_size( 'sportspress-standard-thumbnail', 320, 240, true );

		// Wide (16:9)
		add_image_size( 'sportspress-wide-header', 1920, 1080, true );
		add_image_size( 'sportspress-wide', 640, 360, true );
		add_image_size( 'sportspress-wide-thumbnail', 320, 180, true );

		// Square (1:1)
		add_image_size( 'sportspress-square', 640, 640, true );
		add_image_size( 'sportspress-square-thumbnail', 320, 320, true );

		// Fit (Proportional)
		add_image_size( 'sportspress-fit',  640, 640, false );
		add_image_size( 'sportspress-fit-thumbnail',  320, 320, false );
		add_image_size( 'sportspress-fit-icon',  128, 128, false );
		add_image_size( 'sportspress-fit-mini',  32, 32, false );
	}

	/**
	 * Replace team strings with player if individual mode.
	 */
	public function gettext( $translated_text, $untranslated_text, $domain = 'default' ) {
		if ( SP()->mode == 'player' && $domain == 'sportspress' ):
			switch ( $untranslated_text ):
			case 'Teams':
				return __( 'Players', 'sportspress' );
			case 'Team':
				return __( 'Player', 'sportspress' );
			case 'teams':
				return __( 'players', 'sportspress' );
			case 'Add New Team':
				return __( 'Add New Player', 'sportspress' );
			case 'Edit Team':
				return __( 'Edit Player', 'sportspress' );
			case 'Team Options':
				return __( 'Player Options', 'sportspress' );
			case 'Team Results':
				return __( 'Player Performance', 'sportspress' );
			case 'Logo':
				return __( 'Photo', 'sportspress' );
			case 'Add logo':
				return __( 'Add photo', 'sportspress' );
			case 'Remove logo':
				return __( 'Remove photo', 'sportspress' );
			case 'Select Logo':
				return __( 'Select Photo', 'sportspress' );
			case 'Display logos':
				return __( 'Display photos', 'sportspress' );
			case 'Link teams':
				return __( 'Link players', 'sportspress' );
			endswitch;
		endif;
		
		return $translated_text;
	}

	/** Helper functions ******************************************************/

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'SP_TEMPLATE_PATH', 'sportspress/' );
	}
}

endif;

/**
 * Returns the main instance of SP to prevent the need to use globals.
 *
 * @since  0.7
 * @return SportsPress
 */
function SP() {
	return SportsPress::instance();
}

SP();
