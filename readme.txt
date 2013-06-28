=== WP DB Driver ===
Contributors: kurtpayne, markoheijnen
Tags: database, backend
Requires at least: 3.6
Tested up to: 3.6
Stable tag: 1.0
License: GPLv2 or later

The possible new database layer for WordPress core

== Description ==

The mysql_* functions are officially deprecated for PHP 5.5 and are throwing E_DEPRECATED errors.
On http://core.trac.wordpress.org/ticket/21663 there is discussion on this topic.

This plugin reflects those discussions. 

== Installation ==

1. Verify that you have PDO or MySQLi
2. Go to the settings page to install db.php or copy `db.php` to your WordPress content directory (`wp-content/` by default.
3. Done!

== Changelog ==

= 1.0 ( 2013-06-28 ) =
* First version that supports PDO and MySQLi