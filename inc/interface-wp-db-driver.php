<?php

interface wpdb_driver {
	public function escape( $string );
	public function get_error_message();
	public function flush();
	public function connect( $host, $user, $pass, $port = 3306 );
	public function select( $name );
	public function query( $query );
	public function load_col_info();
	public function db_version();
	public function affected_rows();
	public function insert_id();
	public function get_results();
}
