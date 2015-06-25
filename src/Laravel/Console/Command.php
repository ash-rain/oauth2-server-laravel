<?php namespace Microweber\OAuth2\Laravel\Console;

use Microweber\OAuth2\Laravel\TableBuilder;
use Illuminate\Console\Command as IlluminateCommand;
use Dingo\OAuth2\Storage\Adapter;

class Command extends IlluminateCommand {

	/**
	 * Storage adapter instance.
	 * 
	 * @var \Microweber\OAuth2\Storage\Adapter
	 */
	protected $storage;

	/**
	 * Table builder instance.
	 * 
	 * @var \Microweber\OAuth2\Laravel\TableBuilder
	 */
	protected $builder;

	/**
	 * Insert a blank line into the output.
	 * 
	 * @return \Microweber\OAuth2\Laravel\Console\Command
	 */
	public function blankLine()
	{
		$this->line('');

		return $this;
	}

	/**
	 * Get a storage from the storage adapter.
	 * 
	 * @param  string  $storage
	 * @return mixed
	 */
	protected function storage($storage)
	{
		return $this->storage->get($storage);
	}

	/**
	 * Set the storage adapter instance.
	 * 
	 * @param  \Microweber\OAuth2\Storage\Adapter  $storage
	 * @return \Microweber\OAuth2\Laravel\Console\NewCommand
	 */
	public function setStorage(Adapter $storage)
	{
		$this->storage = $storage;

		return $this;
	}

	/**
	 * Set the table builder instance.
	 * 
	 * @param  \Microweber\OAuth2\Laravel\TableBuilder  $builder
	 * @return \Microweber\OAuth2\Laravel\Console\InstallCommand
	 */
	public function setTableBuilder(TableBuilder $builder)
	{
		$this->builder = $builder;

		return $this;
	}

	/**
	 * Get the database connection.
	 * 
	 * @return \Illuminate\Database\Connection
	 */
	protected function getConnection()
	{
		if ( ! $connection = $this->option('connection'))
		{
			$connection = $this->laravel['config']->get('database.default');
		}

		if ( ! array_key_exists($connection, $this->laravel['config']->get('database.connections')))
		{
			$this->error('Unable to use the given connection as it is not defined within the available connections.');

			exit;
		}

		return $this->laravel['db']->connection($connection);
	}

}