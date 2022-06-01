<?php

namespace App\Database;

use Illuminate\Database\PDO\MySqlDriver;
use Illuminate\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\MySqlSchemaState;
use Illuminate\Filesystem\Filesystem;
use PDO;

class MySqlConnection extends Connection
{
	public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
	{
		parent::__construct($pdo, $database, $tablePrefix, $config);

		$pdo = $this->getPdo();
		printf('%-50.50s: PDO driver name: %s' . PHP_EOL, __METHOD__ . '()', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME));

		try {
			printf(
				'%-50.50s: PDO error mode: %s' . PHP_EOL, __METHOD__ . '()',
				match ($pdo->getAttribute(PDO::ATTR_ERRMODE)) {
					PDO::ERRMODE_SILENT => 'silent',
					PDO::ERRMODE_WARNING => 'warning',
					PDO::ERRMODE_EXCEPTION => 'exception',
					default => 'undefined'
				}
			);
		} catch (\Throwable) {
			printf('%-50.50s: PDO error mode: %s' . PHP_EOL, __METHOD__ . '()', 'undefined');
		}

		try {
			printf('%-50.50s: PDO buffer size: %d' . PHP_EOL, __METHOD__ . '()', $pdo->getAttribute(PDO::MYSQL_ATTR_MAX_BUFFER_SIZE));
		} catch (\Throwable) {
			printf('%-50.50s: PDO buffer size: %s' . PHP_EOL, __METHOD__ . '()', 'undefined');
		}
		try {
			printf('%-50.50s: PDO direct query: %d' . PHP_EOL, __METHOD__ . '()', $pdo->getAttribute(PDO::MYSQL_ATTR_DIRECT_QUERY));
		} catch (\Throwable) {
			printf('%-50.50s: PDO direct query: %s' . PHP_EOL, __METHOD__ . '()', 'undefined');
		}
		try {
			printf('%-50.50s: PDO network compression: %d' . PHP_EOL, __METHOD__ . '()', $pdo->getAttribute(PDO::MYSQL_ATTR_COMPRESS));
		} catch (\Throwable) {
			printf('%-50.50s: PDO network compression: %s' . PHP_EOL, __METHOD__ . '()', 'undefined');
		}
		try {
			printf('%-50.50s: PDO multi statements: %d' . PHP_EOL, __METHOD__ . '()', $pdo->getAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS));
		} catch (\Throwable) {
			printf('%-50.50s: PDO multi statements: %s' . PHP_EOL, __METHOD__ . '()', 'undefined');
		}
	}

	/**
	 * Determine if the connected database is a MariaDB database.
	 *
	 * @return bool
	 */
	public function isMaria()
	{
		return strpos($this->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), 'MariaDB') !== false;
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Illuminate\Database\Query\Grammars\MySqlGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar());
	}

	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return \Illuminate\Database\Schema\MySqlBuilder
	 */
	public function getSchemaBuilder()
	{
		if (is_null($this->schemaGrammar)) {
			$this->useDefaultSchemaGrammar();
		}

		return new MySqlBuilder($this);
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Illuminate\Database\Schema\Grammars\MySqlGrammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return $this->withTablePrefix(new SchemaGrammar());
	}

	/**
	 * Get the schema state for the connection.
	 *
	 * @param \Illuminate\Filesystem\Filesystem|null $files
	 * @param callable|null                          $processFactory
	 *
	 * @return \Illuminate\Database\Schema\MySqlSchemaState
	 */
	public function getSchemaState(Filesystem $files = null, callable $processFactory = null)
	{
		return new MySqlSchemaState($this, $files, $processFactory);
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return \Illuminate\Database\Query\Processors\MySqlProcessor
	 */
	protected function getDefaultPostProcessor()
	{
		return new MySqlProcessor();
	}

	/**
	 * Get the Doctrine DBAL driver.
	 *
	 * @return \Doctrine\DBAL\Driver\PDOMySql\Driver|\Illuminate\Database\PDO\MySqlDriver
	 */
	protected function getDoctrineDriver()
	{
		$driver = class_exists(Version::class) ? new DoctrineDriver() : new MySqlDriver();
		printf('%-50.50s: using driver: %s' . PHP_EOL, __METHOD__ . '()', get_class($driver));

		return $driver;
	}
}
