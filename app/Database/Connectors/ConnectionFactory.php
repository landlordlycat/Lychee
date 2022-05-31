<?php

namespace App\Database\Connectors;

use App\Database\MySqlConnection;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory as BaseConnectionFactory;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Database\Connectors\SQLiteConnector;
use Illuminate\Database\Connectors\SqlServerConnector;

class ConnectionFactory extends BaseConnectionFactory
{
	/**
	 * {@inheritDoc}
	 */
	protected function createConnection($driver, $connection, $database, $prefix = '', array $config = []): Connection
	{
		if ($driver === 'mysql') {
			return new MySqlConnection($connection, $database, $prefix, $config);
		} else {
			return parent::createConnection($driver, $connection, $database, $prefix, $config);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function createConnector(array $config)
	{
		if (!isset($config['driver'])) {
			throw new \InvalidArgumentException('A driver must be specified.');
		}

		if ($this->container->bound($key = "db.connector.{$config['driver']}")) {
			$connector = $this->container->make($key);
			printf('%-50.50s: db.connector.$s is bound to: %s' . PHP_EOL, __METHOD__ . '()', $config['driver'], get_class($connector));

			return $connector;
		} else {
			printf('%-50.50s: db.connector.$s is not bound' . PHP_EOL, __METHOD__ . '()', $config['driver']);
		}

		switch ($config['driver']) {
			case 'mysql':
				return new MySqlConnector();
			case 'pgsql':
				return new PostgresConnector();
			case 'sqlite':
				return new SQLiteConnector();
			case 'sqlsrv':
				return new SqlServerConnector();
		}

		throw new \InvalidArgumentException("Unsupported driver [{$config['driver']}].");
	}
}
