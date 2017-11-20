=== reCAPTCHA Jetpack ===
Author: bozdoz
Author URI: https://www.twitter.com/bozdoz/
Plugin URI: https://wordpress.org/plugins/recaptcha-jetpack/
Contributors: bozdoz
Donate link: https://www.paypal.me/bozdoz
Tags: jetpack, google, recaptcha, captcha, contact
Requires at least: 3.0.1
Tested up to: 4.9
Version: 0.2.2
Stable tag: 0.2.2
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple plugin that adds a Google reCAPTCHA to the Jetpack contact form. Requires the Jetpack plugin.

== Description ==

[Jetpack](https://wordpress.org/plugins/jetpack/) makes it easy to add contact forms to your WordPress site, but you may want a simple way to add a [Google reCAPTCHA](https://www.google.com/recaptcha/) to prevent spam.  This plugin will manipulate the Jetpack `[contact-form]` shortcode to prepend the Google script, add a button, and parse the response.

This plugin allows both reCAPTCHA v2 and invisible reCAPTCHA (see screenshots and choose whatever works for you!).

Shoot me a question about it on Twitter: [@bozdoz](https://www.twitter.com/bozdoz/).

This plugin is open source on [GitHub](https://github.com/bozdoz/wp-plugin-recaptcha-jetpack)!

== Installation ==

1. Install from the WordPress plugin install page in the admin.

**OR**

1. Choose to add a new plugin, then click upload
2. Upload the recaptcha-jetpack zip
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

None yet! Shoot me a question about it on Twitter: [@bozdoz](https://www.twitter.com/bozdoz/).

== Screenshots ==

1. Register with [Google reCAPTCHA](https://www.google.com/recaptcha/) and fill in your site key, and secret key (also, choose which kind of reCAPTCHA; see other screenshots for examples)
2. Example Jetpack form with reCAPTCHA v2.
3. Example Jetpack form with invisible reCAPTCHA.

== Changelog ==

= 0.2.2 =
* Added additional `isset` checks to fix undefined index errors.

= 0.2.1 =
* Renamed plugin to reCAPTCHA Jetpack; added minor Google response validation and nonce verification.

= 0.2.0 =
* Added invisible reCAPTCHA; fixes to plugin activation.

= 0.1.0 =
* First Version. Basic `[contact-form]` manipulation.

== Upgrade Notice ==

= 0.2.0 =
Fixes to plugin activation.