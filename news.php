<?php
/*
Plugin Name: News
Plugin URI: http://indiorlei.com/
Description: Cadastro de Emails para newss
Version: 1.0
Author: Indiorlei de Oliveira
Author URI: http://indiorlei.com/
Requires at least: 4.0
Tested up to: 4.2
License: GPLv2
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists( 'NewsPlugin' ) ) :

    class NewsPlugin {
        public $version = '1.0';

        public static function init() {
            $news = new self();
        }

        
        public function __construct() {
            $this->define_constants();
            $this->includes();
            $this->setup_actions();
            $this->create_tables();
        }

        
        private function define_constants() {
            define( 'NEWS_VERSION',    $this->version );
            define( 'NEWS_BASE_URL',   trailingslashit( plugins_url( 'news' ) ) );
            define( 'NEWS_ASSETS_URL', trailingslashit( NEWS_BASE_URL . 'assets' ) );
            define( 'NEWS_PATH',       plugin_dir_path( __FILE__ ) );
        }

        
        private function plugin_classes() {
            return array(
                'newssystemcheck'  => NEWS_PATH . 'includes/news.systemcheck.class.php',
                // 'simple_html_dom'        => NEWS_PATH . 'includes/simple_html_dom.php'
                );
        }

        
        private function includes() {
            $autoload_is_disabled = defined( 'NEWS_AUTOLOAD_CLASSES' ) && NEWS_AUTOLOAD_CLASSES === false;
            if ( function_exists( "spl_autoload_register" ) && ! ( $autoload_is_disabled ) ) {
                // >= PHP 5.2 - Use auto loading
                if ( function_exists( "__autoload" ) ) {
                    spl_autoload_register( "__autoload" );
                }
                spl_autoload_register( array( $this, 'autoload' ) );
            } else {
                // < PHP5.2 - Require all classes
                foreach ( $this->plugin_classes() as $id => $path ) {
                    if ( is_readable( $path ) && ! class_exists( $id ) ) {
                        require_once( $path );
                    }
                }
            }
        }


        /**
        * Autoload news classes to reduce memory consumption
        */
        public function autoload( $class ) {
            $classes = $this->plugin_classes();
            $class_name = strtolower( $class );
            if ( isset( $classes[$class_name] ) && is_readable( $classes[$class_name] ) ) {
                require_once( $classes[$class_name] );
            }
        }


        /**
        * Hook news into WordPress
        */
        private function setup_actions() {
            add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 9554 );
            add_action( 'init', array( $this, 'register_post_type' ) );
            add_action( 'init', array( $this, 'register_taxonomy' ) );
            add_action( 'widgets_init', array( $this, 'register_news_widget' ) );
            if ( defined( 'news_ENABLE_RESOURCE_MANAGER' ) && news_ENABLE_RESOURCE_MANAGER === true ) {
                add_action( 'template_redirect', array( $this, 'start_resource_manager'), 0 );
            }
        }


        /**
        * Register news widget
        */
        public function register_news_widget() {
            // register_widget( 'news_Widget' );
        }


        /**
        * Register news post type
        */
        public function register_post_type() {
            register_post_type( 'news', array(
                'query_var' => false,
                'rewrite' => false,
                'public' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_in_nav_menus' => false,
                'show_ui' => false,
                'labels' => array(
                    'name' => 'news'
                    )
                )
            );
        }


        /**
        * Register taxonomy to news
        */
        public function register_taxonomy() {
            register_taxonomy( 'news', 'attachment', array(
                'hierarchical' => true,
                'public' => false,
                'query_var' => false,
                'rewrite' => false
                )
            );
        }


        /**
        * Add the menu page
        */
        public function register_admin_menu() {
            global $user_ID;
            $title = apply_filters( 'news_menu_title', 'news' );
            $capability = apply_filters( 'news_capability', 'edit_others_posts' );
            
            $page = add_menu_page(
                $title,
                $title,
                $capability,
                'news',
                array( $this, 'render_admin_page' ),
                NEWS_ASSETS_URL . 'news/logo-news.png'
                );

            // ensure our JavaScript is only loaded on the news admin page
            add_action( 'admin_print_scripts-' . $page, array( $this, 'register_admin_scripts' ) );
            add_action( 'admin_print_styles-' . $page, array( $this, 'register_admin_styles' ) );
            add_action( 'load-' . $page, array( $this, 'help_tab' ) );


            // $page = add_submenu_page(
            //     'news',
            //     __( 'Algum SubMenu', 'news' ),
            //     __( 'Algum SubMenu', 'news' ),
            //     $capability,
            //     'news-sub-menu',//aqui vai a function da nova pagina
            //     array( $this, 'sub_menu' ) //aqui vai a funcition que renderiza a pagina
            //     );
            // add_action( 'admin_print_styles-' . $page, array( $this, 'register_admin_styles' ) );

        }


        /**
        * Register admin styles
        */
        public function register_admin_styles() {
            // wp_enqueue_style( 'news-fullcalendar-styles', NEWS_ASSETS_URL . 'calendar/css/fullcalendar.css', false, NEWS_VERSION );
            // do_action( 'news_register_admin_styles' );
        }

        /**
        * Register admin JavaScript
        */
        public function register_admin_scripts() {
            // // media library dependencies
            // wp_enqueue_media();
            // // plugin dependencies
            // wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
            // wp_enqueue_script( 'jquery-ui-sortable', array( 'jquery', 'jquery-ui-core' ) );
            // wp_dequeue_script( 'link' ); // WP Posts Filter Fix (Advanced Settings not toggling)
            // wp_dequeue_script( 'ai1ec_requirejs' ); // All In One Events Calendar Fix (Advanced Settings not toggling)
            
            // wp_enqueue_script( 'news-calendar-moment', NEWS_ASSETS_URL . 'calendar/js/moment.min.js', array( 'jquery' ), NEWS_VERSION );
            // wp_enqueue_script( 'news-calendar-fullcalendar', NEWS_ASSETS_URL . 'calendar/js/fullcalendar.min.js', array( 'jquery' ), NEWS_VERSION );
            // do_action( 'news_register_admin_scripts' );
        }


        /**
        * Check our WordPress installation is compatible with news
        */
        public function do_system_check() {
            $systemCheck = new newsSystemCheck();
            $systemCheck->check();
        }


        /**
        * Return the users saved view preference.
        */
        public function get_view() {
            global $user_ID;
            if ( get_user_meta( $user_ID, "news_view", true ) ) {
                return get_user_meta( $user_ID, "news_view", true );
            }
            return 'tabs';
        }


        /**
        * Render the admin page
        */
        public function render_admin_page() {
            // code php of admin page
            ?>
            <script type='text/javascript'>
            // code javascript
            jQuery(document).ready(function() {

            });
            </script>
            <!-- body plugin -->
            <div class="wrap news">
                <h1>Aqui vai o corpo do plugin</h1>
            </div>
            <?php
        }


        /**
        * render sub-menu page
        */
        public function sub_menu() {
            ?>
            <h2>Uma página de sub menu</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            <?php
        }


        /**
        * Start output buffering.
        *
        * Note: wp_ob_end_flush_all is called by default 
        *  - see shutdown action in default-filters.php
        */
        public function start_resource_manager() {
            ob_start( array( $this, 'resource_manager' ) );
        }


        /**
        * Process the whole page output. Move link tags with an ID starting
        * with 'news' into the <head> of the page.
        */
        public function resource_manager( $buffer ) {
            // create dom document from buffer
            $html = new simple_html_dom();
            // Load from a string
            $html->load( $buffer, true, false );
            if ( ! $html->find( 'body link[id^="news"]' ) )
                return $buffer;
                // selectors to find news links
            $selectors = array( 
                'body link[id^="news"]',
                );
            $selectors = apply_filters( "news_resource_manager_selectors", $selectors );
            if ( $head = $html->find( 'head', 0 ) ) {
                // move news elemends to <head>
                foreach ( $selectors as $selector ) {
                    foreach ( $html->find( $selector ) as $element ) {
                        $head->innertext .= "\t" . $element->outertext . "\n";
                        $element->outertext = '';
                    }
                }
            }
            return $html->save();
        }


        /**
        * Create Tables
        */
        private function create_tables() {
            // global $wpdb;

            // $charset_collate = $wpdb->get_charset_collate();

            // $sql = "CREATE TABLE {$wpdb->prefix}news_reservation (
            //         id mediumint(9) NOT NULL AUTO_INCREMENT,
            //         client_id mediumint(9) NOT NULL,
            //         subject varchar(200),
            //         description varchar(1000),
            //         status_id mediumint(9) NOT NULL,
            //         reservation_info mediumint(9) NOT NULL,
            //         UNIQUE KEY id (id)
            //     ) $charset_collate;
            //     CREATE TABLE {$wpdb->prefix}news_status (
            //         id mediumint(9) NOT NULL AUTO_INCREMENT,
            //         description varchar(45),
            //         UNIQUE KEY id (id)
            //     ) $charset_collate;
            //     CREATE TABLE {$wpdb->prefix}news_client (
            //         id mediumint(9) NOT NULL AUTO_INCREMENT,
            //         name varchar(45),
            //         email varchar(45),
            //         telephone varchar(45),
            //         age int,
            //         gender varchar(45),
            //         country varchar(45),
            //         states varchar(45),
            //         skype varchar(45),
            //         UNIQUE KEY id (id)
            //     ) $charset_collate;
            //     CREATE TABLE {$wpdb->prefix}news_date_available (
            //         id mediumint(9) NOT NULL AUTO_INCREMENT,
            //         date_available DATE,
            //         UNIQUE KEY id (id)
            //     ) $charset_collate;
            //     CREATE TABLE {$wpdb->prefix}news_time_available (
            //         id mediumint(9) NOT NULL AUTO_INCREMENT,
            //         time_available DATETIME,
            //         UNIQUE KEY id (id)
            //     ) $charset_collate;
            //     CREATE TABLE {$wpdb->prefix}news_reservation_info (
            //         id mediumint(9) NOT NULL AUTO_INCREMENT,
            //         time_available_id mediumint(9) NOT NULL,
            //         date_available_id mediumint(9) NOT NULL,
            //         UNIQUE KEY id (id)
            //     ) $charset_collate;";

            // require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            // dbDelta( $sql );

            // inserts default
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 1,  'time_available' => '0000-00-00 08:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 2,  'time_available' => '0000-00-00 08:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 3,  'time_available' => '0000-00-00 09:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 4,  'time_available' => '0000-00-00 09:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 5,  'time_available' => '0000-00-00 10:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 6,  'time_available' => '0000-00-00 10:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 7,  'time_available' => '0000-00-00 11:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 8,  'time_available' => '0000-00-00 11:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 9,  'time_available' => '0000-00-00 12:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 10, 'time_available' => '0000-00-00 12:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 11, 'time_available' => '0000-00-00 13:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 12, 'time_available' => '0000-00-00 13:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 13, 'time_available' => '0000-00-00 14:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 14, 'time_available' => '0000-00-00 14:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 15, 'time_available' => '0000-00-00 15:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 16, 'time_available' => '0000-00-00 15:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 17, 'time_available' => '0000-00-00 16:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 18, 'time_available' => '0000-00-00 16:30:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 19, 'time_available' => '0000-00-00 17:00:00', ) );
            // $wpdb->insert( $wpdb->prefix . 'news_time_available', array('id' => 20, 'time_available' => '0000-00-00 17:30:00', ) );

        }


        /**
        * Add the help tab to the screen.
        */
        public function help_tab() {
            $screen = get_current_screen();
            // documentation tab
            $screen->add_help_tab( array(
                'id'    => 'documentation',
                'title' => __( 'Documentation', 'news' ),
                'content'   => "<p><a href='http://www.news.com/documentation/' target='blank'>news Documentation</a></p>",
                )
            );
        }

    }

endif;
add_action( 'plugins_loaded', array( 'NewsPlugin', 'init' ), 10 );
