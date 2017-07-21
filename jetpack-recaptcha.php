<?php
    /*
    Plugin Name: Jetpack reCAPTCHA
    Plugin URI: https://github.com/bozdoz/wp-plugin-jetpack-recaptcha
    Description: A simple plugin that adds a Google reCAPTCHA to the Jetpack contact form. Requires the Jetpack plugin.
    Author: bozdoz
    Author URI: https://twitter.com/bozdoz/
    Version: 0.1.0
    License: GPL2

    Jetpack reCAPTCHA is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.
     
    Jetpack reCAPTCHA is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
     
    You should have received a copy of the GNU General Public License
    along with Jetpack reCAPTCHA.
    */

if (!class_exists('Bozdoz_JPR_Plugin')) {
    
    class Bozdoz_JPR_Plugin {

        // generic variables for titles and URLs
        static $title = 'Jetpack reCAPTCHA';
        static $slug = 'jetpack-recaptcha';

        // $prefix makes db entries and script/styles unique
        static $prefix = 'bozdoz_jpr_';

        // $error holds an error msg if POST method fails
        private $error = '';

        /*
        *
        * add_recaptcha
        *
        * adds script and button to contact-form
        *
        * @param string $content    WordPress provided HTML
        * @return string            conditionally formatted html
        */
        function add_recaptcha ($content) {
            preg_match('/\[contact-form.*?\](.*?)\[\/contact-form\]/si', $content, $matches);

            if (!$matches) return $content;

            if (self::get_option('recaptcha_type') === 'invisible') {
                // calls script below internally
                wp_enqueue_script(self::$prefix . 'invisible_recaptcha_script');
            } else {
                wp_enqueue_script(self::$prefix . 'recaptcha_script');
            }

            
            // append the button to the form shortcode
            $content = str_replace('[/contact-form]', '[bozdoz-jpr-button][/contact-form]', $content);

            return $content;
        }

        /*
        *
        * button_html
        *
        * Adds html to the Jetpack contact form
        *
        * @return string    html to put at the end of the form
        */

        function button_html () {
            $site_key = self::get_option('site_key');

            if (!$site_key) {
                return sprintf('<div>No Site Key Found! Please Set this value in <a href="%s">Jetpack reCAPTCHA plugin!</a></div>', self::get_settings_url());
            }

            // get variable function name
            $recaptcha_type = self::get_option('recaptcha_type') . '_html';

            // retrieve desired HTML for type
            $button = self::$recaptcha_type( $site_key );

            if ($this->error) {
                return sprintf("<div class=\"error\">%s</div> %s", $this->error, $button);
            }

            return $button;
        }

        /*
        *
        * v2_html
        *
        * Adds html to the Jetpack contact form
        *
        * @param string $site_key   site key from db/Google
        * @return string            html to insert into the form
        */
        private function v2_html ($site_key) {
            return sprintf("<div class=\"g-recaptcha\" data-sitekey=\"%s\"></div>", $site_key);
        }

        /*
        *
        * invisible_html
        *
        * Adds html to the Jetpack contact form
        *
        * @param string $site_key   site key from db/Google
        * @return string            html to insert into the form
        */
        private function invisible_html ($site_key) {
            return sprintf("<div class=\"invisible-recaptcha\" 
                            data-sitekey=\"%s\"
                            data-callback=\"%s\"
                            ></div>", $site_key, self::$prefix . 'onSubmit');
        }

        /*
        *
        * google_verify
        *
        * set up the request to google to test form for spam
        *
        * @param string $default    whether the form is spam (seems strange)
        * @return boolean           true if spam, else default
        */

        function google_verify ($default) {
            // reset error
            $this->error = '';

            // invisible is done client-side (I believe)
            /*if (self::get_option('recaptcha_type') === 'invisible') {
                return $default;
            }*/

            $secret_key = self::get_option('secret_key');

            // if we can't make the request, return default
            if (!$secret_key ||
                !isset($_POST['g-recaptcha-response'])) {
                return $default;
            } 

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $querystring = sprintf('secret=%s&response=%s', $secret_key, $_POST['g-recaptcha-response']);
            $response = self::get_url($url, $querystring);
            $response = json_decode($response);

            if (!$response->success) {
                $this->error = 'Google could not verify you; please try again.';
                return new WP_Error('spam', $this->error);
            }

            return $default;
        }

        /*
        *
        * get_url
        *
        * curl wrapper for posting/retrieving from a url
        *
        * @param string $url                the urlencoded request url
        * @param querystring $querystring   the urlencoded querystring
        * @return varies
        */

        static function get_url($url, $querystring) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

            $data = curl_exec($ch);
            curl_close($ch);

            return $data;
        }

        static $options = array(
            'site_key' => array(
                'default'=>'',
                'type'=>'text',
                'helptext'=>'Your Site Key. Get yours here: <a href="https://www.google.com/recaptcha/" target="_blank">Google reCAPTCHA</a>.'
            ),
            'secret_key' => array(
                'default'=>'',
                'type'=>'text',
                'helptext'=>'Your Secret Key. Get yours here: <a href="https://www.google.com/recaptcha/" target="_blank">Google reCAPTCHA</a>.'
            ),
            'recaptcha_type' => array(
                'default'=>'v2',
                'type' => 'select',
                'options' => array(
                    'v2' => 'reCAPTCHA V2',
                    'invisible' => 'Invisible reCAPTCHA'
                ),
                'helptext'=>'Which reCAPTCHA did you choose when you set it up with Google? More options coming soon!'
            )
        );

        /*
    
        Helper functions

        */

        /*
        *
        * get_option
        *
        * wrapper for WordPress get_options (adds prefix to default options)
        *
        * @param string $key                
        * @param varies $default   default value if not found in db
        * @return varies
        */

        private function get_option ($key) {
            $key = self::$prefix . $key;
            return get_option($key, self::$options[$key]['default']);
        }

        /*
        *
        * foreachoption
        *
        * useful for iterating db options above
        *
        * @param function $method   the method executed on the array name and default value (ex: add_option)
        * @return null
        */

        static function foreachoption ( $method ) {
            foreach(self::$options as $name=>$atts) {
                // prevent "plugin generated XX characters" error
                if (isset($atts['default'])) {
                    $method(self::$prefix . $name, $atts['default']);
                }
            }
        }

        /*
        *
        * ADMIN STUFF (pages, db options)
        *
        */

        public function __construct() {
            /* admin hooks */
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
            
            /* add settings to plugin page */
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));

            add_action('wp_enqueue_scripts', array($this, 'register_scripts'));

            /* add the real functionality to the plugin */
            add_filter('the_content', array($this, 'add_recaptcha'));
            add_shortcode('bozdoz-jpr-button', array($this, 'button_html'));
            add_filter('jetpack_contact_form_is_spam', array($this, 'google_verify'));
        }

        /*
        *
        * register_scripts
        *
        * registers scripts
        * @return null
        */

        static function register_scripts () {
            wp_register_script(self::$prefix . 'invisible_recaptcha_script', plugins_url('assets/js/invisible-recaptcha.js', __FILE__), Array('jquery'));
            wp_register_script(self::$prefix . 'recaptcha_script', 'https://www.google.com/recaptcha/api.js');
        }

        /*
        *
        * activate
        *
        * adds the default values to the db
        * @return null
        */

        static function activate () {
            self::foreachoption( 'add_option' );
        }

        /*
        *
        * uninstall
        *
        * adds the default values to the db
        * @return null
        */

        static function uninstall () {
            self::foreachoption( 'delete_option' );
        }

        /*
        *
        * admin_init
        *
        * registers the style for the admin page
        * @return null
        */
        
        public function admin_init () {
            wp_register_style(self::$prefix . 'admin_style', plugins_url('admin/style.css', __FILE__));
        }

        /*
        *
        * admin_menu
        *
        * adds the admin page link to the admin menu
        * @return null
        */

        public function admin_menu () {
            add_options_page(self::$title, self::$title, 'manage_options', self::$slug, array($this, 'settings_page'));
        }

        public function settings_page () {
            $defaults = self::$options;
            $plugin_title = self::$title;
            $prefix = self::$prefix;

            wp_enqueue_style($prefix . 'admin_style');

            include 'admin/admin.php';
        }

        /*
        *
        * Add settings link to the plugin on Installed Plugins page
        *
        * @param array $links   array of links on plugin page
        * @return array $links  manipulated array of links 
        */
        public function plugin_action_links ( $links ) {
            $links[] = sprintf('<a href="%s">Settings</a>', self::get_settings_url());
            return $links;
        }

        /*
        *
        * Get settings link
        *
        * @return string    link to admin settings page
        */
        public function get_settings_url () {
            return esc_url( get_admin_url(null, 'options-general.php?page=' . self::$slug) );
        }


    }

    register_activation_hook(__FILE__, array('Bozdoz_JPR_Plugin', 'activate'));
    register_uninstall_hook(__FILE__, array('Bozdoz_JPR_Plugin', 'uninstall'));

    new Bozdoz_JPR_Plugin();
}