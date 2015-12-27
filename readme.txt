=== WP DB Driver ===
Contributors: markoheijnen, kurtpayne
Donate link: https://markoheijnen.com/donate
Tags: database, backend, pdo, mysqli, mysql
Requires at least: 4.2.0
Tested up to: 4.4.0
Stable tag: 2.1.0
License: GPLv2 or later

An improved database layer for WordPress

== Description ==

This plugin adds an improved database layer to WordPress. It allows you to do more then the default one and is always up-to-date with the changes core makes.

**mysql_* functions**

The mysql_* functions are officially deprecated for PHP 5.5 and are throwing E_DEPRECATED errors.
On http://core.trac.wordpress.org/ticket/21663 there is discussion on this topic.

This plugin reflects those discussions.

**Why should I use this plugin?**

You should use this plugin if you want to start using PDO / MySQLi for WordPress.

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

`define( 'WPDB_DRIVER', 'wpdb_driver_pdo_mysql' );`

You can specify `wpdb_driver_pdo_mysql`, `wpdb_driver_mysqli`, or `wpdb_driver_mysql`.  Any other driver will cause an error.

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

= 2.1.0 =
* Sync with 4.4 ( Changeset 35787)
* Changed is_mysql logic. 

= 2.0.1 =
* Reupload code from GitHub

= 2.0 (2015-07-25) =
* Sync with 4.2.3 ( Changeset 33310)
* Increased minimal WordPress version to 4.2
* Extending wpdb back again
* Add ability to extend it with more drivers through the constant 'WPDB_DRIVERS'
* Fully compatible with the unit tests of WordPress except HHVM PDO

= 1.9.3 (2015-05-07) =
* Sync with 4.2.2

= 1.9.2 (2015-04-27) =
* Sync with 4.2.1

= 1.9.1 (2015-04-23) =
* Fix setting charset and SQL mode for PDO

= 1.9 (2015-04-23) =
* Sync to changeset 32261

= 1.8.1 (2014-08-08) =
* Fix setting charset and SQL mode for PDO

= 1.8 (2014-08-07) =
* Synced with trunk to Changeset 29165 excluding 27075
 * Ensure compatibility with MySQL 5.6 which has stricter SQL modes by default
 * Throw an incorrect usage notice when the query argument of wpdb::prepare() does not include a placeholder.
 * When the MySQL server has "gone away," attempt to reconnect and retry the query.
* Don't extend wpdb anymore to be on the safe side
* Works with socket connections
* More abstraction from the main db class to our interface.
* Added a banner image for WordPress.org. Thanks to Marcel van der Horst

= 1.7 (2014-01-30) =
* Synced with trunk to Changeset 25703
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