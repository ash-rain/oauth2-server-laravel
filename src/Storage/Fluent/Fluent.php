<?php namespace Microweber\OAuth2\Storage\Fluent;

use Illuminate\Database\Connection;

abstract class Fluent {

	/**
	 * Illuminate database connection.
	 * 
	 * @var \Illuminate\Database\Connection
	 */
	protected $connection;

	/**
	 * Array of database table names.
	 * 
	 * @var array
	 */
	protected $tables;

	/**
	 * Cached queries and the results.
	 * 
	 * @var array
	 */
	protected $cache;

	/**
	 * Create a new Microweber\OAuth2\Storage\Fluent\Fluent instance.
	 * 
	 * @param  \Illuminate\Database\Connection  $connection
	 * @param  array  $tables
	 * @return void
	 */
	public function __construct(Connection $connection, array $tables)
	{
		$this->connection = $connection;
		$this->tables = $tables;
	}

}