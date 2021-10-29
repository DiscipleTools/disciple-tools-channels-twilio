<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Channels_Twilio_Menu
 */
class Disciple_Tools_Channels_Twilio_Menu {

    public $token = 'disciple_tools_channels_twilio';

    private static $_instance = null;

    /**
     * Disciple_Tools_Channels_Twilio_Menu Instance
     *
     * Ensures only one instance of Disciple_Tools_Channels_Twilio_Menu is loaded or can be loaded.
     *
     * @return Disciple_Tools_Channels_Twilio_Menu instance
     * @since 0.1.0
     * @static
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        add_action( "admin_menu", array( $this, "register_menu" ) );
        add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ], 10, 1 );

    } // End __construct()

    /**
     * Loads scripts and styles.
     */
    public function load_scripts( $hook ) {
//        if ( isset( $_GET["page"] ) && ( $_GET["page"] === $this->token ) ) {
//            // TODO -> If Needed...!
//        }
    }

    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_submenu_page( 'dt_extensions', 'Channels - Twilio', 'Channels - Twilio', 'manage_dt', $this->token, [
            $this,
            'content'
        ] );
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {
    }

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( ! current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'general';
        }

        $link = 'admin.php?page=' . $this->token . '&tab=';

        ?>
        <div class="wrap">
            <h2>DISCIPLE TOOLS : CHANNELS - TWILIO</h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'general' || ! isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">General</a>
            </h2>

            <?php
            switch ( $tab ) {
                case "general":
                    require( 'general-tab.php' );
                    $object = new Disciple_Tools_Channels_Twilio_Tab_General();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }
}

Disciple_Tools_Channels_Twilio_Menu::instance();

