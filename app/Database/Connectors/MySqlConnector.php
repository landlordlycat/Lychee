<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\MySqlConnector as BaseMySqlConnector;

class MySqlConnector extends BaseMySqlConnector
{
	/**
	 * {@inheritDoc}
	 */
	public function connect(array $config)
	{
		$connection = parent::connect($config);
		printf('%-50.50s: returning %s' . PHP_EOL, __METHOD__ . '()', get_class($connection));

		return $connection;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configureIsolationLevel($connection, array $config): void
	{
		if (isset($config['isolation_level'])) {
			printf('%-50.50s: isolation_level = %s' . PHP_EOL, __METHOD__ . '()', $config['isolation_level']);
		} else {
			printf('%-50.50s: isolation_level is not set' . PHP_EOL, __METHOD__ . '()');
		}

		parent::configureIsolationLevel($connection, $config);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configureEncoding($connection, array $config): void
	{
		if (isset($config['charset'])) {
			printf('%-50.50s: charset = %s' . PHP_EOL, __METHOD__ . '()', $config['charset']);
		} else {
			printf('%-50.50s: charset is not set' . PHP_EOL, __METHOD__ . '()');
		}

		parent::configureEncoding($connection, $config);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configureTimezone($connection, array $config): void
	{
		if (isset($config['timezone'])) {
			printf('%-50.50s: timezone = %s' . PHP_EOL, __METHOD__ . '()', $config['timezone']);
		} else {
			printf('%-50.50s: timezone is not set' . PHP_EOL, __METHOD__ . '()');
		}

		parent::configureTimezone($connection, $config);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function hasSocket(array $config): bool
	{
		$hasSocket = isset($config['unix_socket']) && !empty($config['unix_socket']);
		printf('%-50.50s: $hasSocket = %s' . PHP_EOL, __METHOD__ . '()', $hasSocket ? 'true' : 'false');

		return $hasSocket;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setCustomModes(\PDO $connection, array $config)
	{
		$modes = implode(',', $config['modes']);
		printf('%-50.50s: $modes = %s' . PHP_EOL, __METHOD__ . '()', $modes);

		$connection->prepare("set session sql_mode='{$modes}'")->execute();
	}

	/**
	 * Get the query to enable strict mode.
	 *
	 * @param \PDO  $connection
	 * @param array $config
	 *
	 * @return string
	 */
	protected function strictMode(\PDO $connection, $config)
	{
		$version = $config['version'] ?? $connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
		printf('%-50.50s: $version = %s (8.0.11 introduced a change)' . PHP_EOL, __METHOD__ . '()', $version);

		$strictModeSql =
			version_compare($version, '8.0.11') >= 0 ?
				"set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'" :
				"set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
		printf('%-50.50s: $strictModeSql = %s' . PHP_EOL, __METHOD__ . '()', $strictModeSql);

		return $strictModeSql;
	}
}
