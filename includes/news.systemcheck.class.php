<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

class newsSystemCheck {
    var $options = array();

    public function __construct() {
        $this->options = get_site_option( 'news_systemcheck' );
    }

    public function check() {
        $this->dismissMessages();
        $this->checkWordPressVersion();
        $this->checkWpFooter();
        $this->updateSystemCheck();
    }

    private function dismissMessages() {
        if ( isset( $_REQUEST['dismissMessage'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
            $nonce = $_REQUEST['_wpnonce'];
            $key = $_REQUEST['dismissMessage'];
            if ( wp_verify_nonce( $nonce, "news-dismiss-{$key}" ) ) {
                $this->options[$key] = false;
                update_site_option( 'news_systemcheck', $this->options );
            }
        }
    }

    private function updateSystemCheck() {
        update_site_option( 'news_systemcheck', $this->options );
    }

    private function checkWordPressVersion() {
        if ( isset( $this->options['wordPressVersion'] ) && $this->options['wordPressVersion']  === false ) {
            return;
        }
        if ( !function_exists( 'wp_enqueue_media' ) ) {
            $error = "Time Trader requires WordPress 3.5 or above. Please upgrade your WordPress installation.";
            $this->printMessage( $error, 'wordPressVersion' );
        } else {
            $this->options['wordPressVersion'] = false;
        }
    }

    private function checkWpFooter() {
        $current_theme = wp_get_theme();
        $theme_name = $current_theme->Template;
        $key = 'wpFooter:' . $theme_name;
        if ( isset( $this->options[$key] ) && $this->options[$key] === false ) {
            return;
        }
        $child_footer = get_stylesheet_directory() . '/footer.php';
        $parent_footer = TEMPLATEPATH . '/footer.php';
        $theme_type = 'parent';
        if ( file_exists( $child_footer ) ) {
            $theme_type = 'child';
            $footer_file = file_get_contents( $child_footer );
            if ( strpos( $footer_file, 'wp_footer()' ) ) {
                return;
            }
        } else if ( file_exists( $parent_footer . '/footer.php' ) ) {
            $theme_type = 'parent';
            $footer_file = file_get_contents( $parent_footer . '/footer.php' );
            if ( strpos( $footer_file, 'wp_footer()' ) ) {
                return;
            }
        }
        if ( $theme_type == 'parent' ) {
            $file_path = $parent_footer;
        } else {
            $file_path = $child_footer;
        }
        $error = "Required call to wp_footer() not found in file <b>{$file_path}</b>. <br /><br />Please check the <a href='http://codex.wordpress.org/Function_Reference/wp_footer'>wp_footer()</a> documentation and make sure your theme has a call to wp_footer() just above the closing </body> tag.";
        $this->printMessage( $error, $key );
    }

    private function printMessage( $message, $key ) {
        $nonce = wp_create_nonce( "news-dismiss-{$key}" );
        echo "<div id='message' class='updated'><p><b>Warning:</b> {$message}<br /><br /><a class='button' href='?page=news&dismissMessage={$key}&_wpnonce={$nonce}'>Hide</a></p></div>";
    }
}
