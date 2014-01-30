=== WP DB Driver ===
Contributors: kurtpayne, markoheijnen
Tags: database, backend, pdo, mysqli, mysql
Requires at least: 3.6
Tested up to: 3.8.1
Stable tag: 1.7
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

**In what order are the drivers picked?**

PDO > MySQLi > MySQL

**How do I specify a driver?**

In your wp-config.php, add a new constant:

`define( 'WPDB_DRIVER', 'pdo_mysql' );`

You can specify `pdo_mysql`, `mysqli`, or `mysql`.  Any other driver will cause an error.

**Which driver is best for my site?**

They should all function equally well for WordPress.  The MySQL extension is being retired.  In PHP 5.5, using this extension issues E_DEPRECATED errors.
In PHP 5.6, it will no longer be available.  The two alternative drivers are PDO and MySQLi.  If WordPress switches to MySQLi or PDO, some cool new features
become available to developers.

<http://net.tutsplus.com/tutorials/php/pdo-vs-mysqli-which-should-you-use/>

**How to configure SSL?**
You can set defined in your wp-config.php to make it work. This only works for MySQLi and PDO.
These defines are: DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA, DB_SSL_CA_PATH and DB_SSL_CIPHER.

In case of a different port number then you can pass this to your database host like: 127.0.0.1:

For more information see:
- http://dev.mysql.com/doc/refman/5.5/en/ssl-connections.html

== Upgrade Notice ==

Added emergency override

== Changelog ==

= 1.7 (2014-01-30) =
* Synced with the latest changes of WordPress in trunk
* Works when plugins folder has been changed
* Added network support
* Security enhanchement when using a network installation
* Updated readme

= 1.6 (2013-09-18) =
* Fix returning incorrect number of rows for some queries. Props markmont
* Add error_handler
* Trowing doing_it_wrong message for all mysql_* functions

= 1.5 (2013-08-04) =
* Fix dbDelta() to create tables when the tables do not exists
* Fix fatal error when database can't get selected by PDO
* Fix notices when using MySQLi query() when $this->result isn't an object
* When database can't get selected show the default message instead of installation screen

= 1.4 (2013-08-02) =
* Fix notices due changes in WordPress 3.6.
* Add SSL support. Props hypertextranch.

= 1.3 (2013-07-09) =
* Show install button when db.php is different.
* Don't show remove button when mysql extension isn't installed.
* Compatibility fixes for unit tests.

= 1.2 (2013-06-30) =
* Added emergency override.
* Updated readme.

= 1.1 ( 2013-06-28 ) =
* Fixes for MySQLi driver, PDO driver.
* Uses WP_Filesystem for writing / removing db.php when possible.
* Added deactivate / uninstall code.

= 1.0 ( 2013-06-28 ) =
* First version that supports PDO and MySQLi. Props kurtpayne and scribu.