<?php

namespace App\Database;

use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\QueryException;

class Connection extends BaseConnection
{
	public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
	{
		parent::__construct($pdo, $database, $tablePrefix, $config);

		printf('%-50.50s: $config =' . PHP_EOL, __METHOD__ . '()');
		var_dump($config);
	}

	/**
	 * Get a new query builder instance.
	 *
	 * This returns our own query builder with instrumentation.
	 *
	 * @return Query\Builder
	 */
	public function query(): Query\Builder
	{
		return new Query\Builder(
			$this, $this->getQueryGrammar(), $this->getPostProcessor()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function select($query, $bindings = [], $useReadPdo = true): array
	{
		return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
			return Connection::selectCallback($this, $query, $bindings, $useReadPdo);
		});
	}

	/**
	 * {@inheritDoc}
	 */
	protected function run($query, $bindings, \Closure $callback)
	{
		foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
			$beforeExecutingCallback($query, $bindings, $this);
		}

		$this->reconnectIfMissingConnection();

		$start = microtime(true);

		// Here we will run this query. If an exception occurs we'll determine if it was
		// caused by a connection that has been lost. If that is the cause, we'll try
		// to re-establish connection and re-run the query with a fresh connection.
		try {
			$result = $this->runQueryCallback($query, $bindings, $callback);
		} catch (QueryException $e) {
			$result = $this->handleQueryException(
				$e, $query, $bindings, $callback
			);
		}

		// Once we have run the query we will calculate the time that it took to run and
		// then log the query, bindings, and execution time so we will report them on
		// the event that the developer needs them. We'll log time in milliseconds.
		$this->logQuery(
			$query, $bindings, $this->getElapsedTime($start)
		);

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function runQueryCallback($query, $bindings, \Closure $callback)
	{
		// To execute the statement, we'll simply call the callback, which will actually
		// run the SQL against the PDO connection. Then we can calculate the time it
		// took to execute and log the query SQL, bindings and time in our memory.
		try {
			return $callback($query, $bindings);
		}

		// If an exception occurs when attempting to run a query, we'll format the error
		// message to include the bindings with SQL, which will make this exception a
		// lot more helpful to the developer instead of just the database's errors.
		catch (\Exception $e) {
			throw new QueryException($query, $this->prepareBindings($bindings), $e);
		}
	}

	public static function selectCallback(Connection $self, string $query, array $bindings, $useReadPdo = true)
	{
		printf('%-50.50s: %s' . PHP_EOL, __METHOD__ . '()', $query);

		if ($self->pretending()) {
			return [];
		}

		// For select statements, we'll simply execute the query and return an array
		// of the database result set. Each element in the array will be a single
		// row from the database table, and will either be an array or objects.
		$statement = $self->prepared(
			$self->getPdoForSelect($useReadPdo)->prepare($query)
		);

		$self->bindValues($statement, $self->prepareBindings($bindings));

		$statement->execute();

		$result = $statement->fetchAll();

		if (is_array($result)) {
			printf('%-50.50s: $statement->fetchAll returned %d results' . PHP_EOL, __METHOD__ . '()', count($result));
		} else {
			printf('%-50.50s: $statement->fetchAll failed' . PHP_EOL, __METHOD__ . '()');
		}

		return $result;
	}
}
