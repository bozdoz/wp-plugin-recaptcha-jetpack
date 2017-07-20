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

        public static $title = 'Jetpack reCAPTCHA';
        public static $slug = 'jetpack-recaptcha';
        // $error holds an error msg if POST method fails
        public static $error = '';

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

            // prepend the script
            wp_enqueue_script('jetpack_recaptcha_script', 'https://www.google.com/recaptcha/api.js');
            
            // add the button
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
            $site_key = get_option('bozdoz_jpr_site_key', '');

            if (!$site_key) {
                return '<div>No Site Key Found! Please Set this value in Jetpack reCAPTCHA plugin!</div>';
            }

            $button = sprintf("<div class=\"g-recaptcha\" data-sitekey=\"%s\"></div>", $site_key);

            if (self::$error) {
                return sprintf("<div class=\"error\">%s</div> %s", self::$error, $button);
            }

            return $button;
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
            self::$error = '';

            $secret_key = get_option('bozdoz_jpr_secret_key');

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
                self::$error = 'Google could not verify you; please try again.';
                return new WP_Error('spam', self::$error);
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

        public function get_url($url, $querystring) {
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

            // look for [contact-form] in the post content
            add_filter('the_content', array($this, 'add_recaptcha'));
            // add the button to the form
            add_shortcode('bozdoz-jpr-button', array($this, 'button_html'));
            add_filter('jetpack_contact_form_is_spam', array($this, 'google_verify'));
        }

        public static $options = array(
            'bozdoz_jpr_site_key' => array(
                'default'=>'',
                'type'=>'text',
                'helptext'=>'Your Site Key. Get yours here: <a href="https://www.google.com/recaptcha/" target="_blank">Google reCAPTCHA</a>.'
            ),
            'bozdoz_jpr_secret_key' => array(
                'default'=>'',
                'type'=>'text',
                'helptext'=>'Your Secret Key. Get yours here: <a href="https://www.google.com/recaptcha/" target="_blank">Google reCAPTCHA</a>.'
            ),
            'bozdoz_jpr_recaptcha_type' => array(
                'default'=>'v2',
                'type' => 'select',
                'options' => array(
                    'v2' => 'reCAPTCHA V2',
                    /*'invisible' => 'Invisible reCAPTCHA',
                    'android' => 'reCAPTCHA Android'*/
                ),
                'helptext'=>'Which reCAPTCHA did you choose when you set it up with Google? More options coming soon!'
            ),
        );

        /*
        *
        * foreachoption
        *
        * useful for iterating db options above
        *
        * @param function $method   the method executed on the array name and default value (ex: add_option)
        * @return null
        */

        public function foreachoption ( $method ) {
            foreach(self::$options as $name=>$atts) {
                $method($name, $atts['default']);
            }
        }

        /*
        *
        * bozdoz_jpr_activation
        *
        * adds the default values to the db
        * @return null
        */

        public function activation () {
            self::foreachoption( add_option );
        }

        /*
        *
        * bozdoz_jpr_uninstall
        *
        * adds the default values to the db
        * @return null
        */

        public function uninstall () {
            self::foreachoption( delete_option );
        }

        /*
        *
        * bozdoz_jpr_admin_init
        *
        * registers the style for the admin page
        * @return null
        */
        
        public function admin_init () {
            wp_register_style('bozdoz_jpr_admin_style', plugins_url('admin/style.css', __FILE__));
        }

        /*
        *
        * bozdoz_jpr_admin_menu
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

            wp_enqueue_style( 'bozdoz_jpr_admin_style' );

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
            $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=' . self::$slug) ) .'">Settings</a>';
            return $links;
        }
    }

    register_activation_hook( __FILE__, array('Bozdoz_JPR_Plugin', 'activate'));
    register_uninstall_hook( __FILE__, array('Bozdoz_JPR_Plugin', 'uninstall') );

    new Bozdoz_JPR_Plugin();
}