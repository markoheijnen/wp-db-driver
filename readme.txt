=== WP DB Driver ===
Contributors: kurtpayne, markoheijnen
Tags: database, backend
Requires at least: 3.6
Tested up to: 3.6
Stable tag: 1.1
License: GPLv2 or later

The possible new database layer for WordPress core

== Description ==

The mysql_* functions are officially deprecated for PHP 5.5 and are throwing E_DEPRECATED errors.
On http://core.trac.wordpress.org/ticket/21663 there is discussion on this topic.

This plugin reflects those discussions.

**Why should I use this plugin?**

You should use this plugin if you want to help test the proposed changes for PDO / MySQLi for WordPress.  Or if you
need PDO / MySQLi support for some reason (e.g. you're running php 5.5, your server doesn't have classic mysql
bindings, etc.)

This plugin is still in development.

***Not recommended for use on production sites***

== Installation ==

1. Verify that you have PDO or MySQLi
2. Go to the settings page to install db.php or copy `wp-content/db.php` to your WordPress content directory (`wp-content/` by default.
3. Done!

== Screenshots ==

1. The main settings page reports on what database drivers your PHP installation supports and lets you enable or disable the custom db.php drop-in for this plugin.

== Frequently Asked Questions ==
**Help, I've broken my site!**

You can visit <http://yoursite.com/?wp-db-driver-emergency-override=1> (replace yoursite.com with your real WordPress blog address) to temporarily disable this plugin.
Then you can login to your admin to deactivate the plugin and restore your site's functionality.

If you need to uninstall manually, you should remove the plugin folder as well as `wp-content/db.php`.
== Upgrade Notice ==


Added emergency override

== Changelog ==

= 1.2 (2013-06-30) =
* Added emergency override
* Updated readme

= 1.1 ( 2013-06-28 ) =
* Fixes for MySQLi driver, PDO driver
* Uses WP_Filesystem for writing / removing db.php when possible
* Added deactivate / uninstall code

= 1.0 ( 2013-06-28 ) =
* First version that supports PDO and MySQLi