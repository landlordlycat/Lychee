<?php

namespace App\Database\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Builder extends BaseBuilder
{
	/**
	 * Execute the query as a "select" statement.
	 *
	 * @param array|string $columns
	 *
	 * @return Collection
	 */
	public function get($columns = ['*']): Collection
	{
		$result = $this->onceWithColumns(Arr::wrap($columns), function () {
			return $this->processor->processSelect($this, $this->runSelect());
		});

		printf('%-50.50s: returned %d results' . PHP_EOL, __METHOD__ . '()', count($result));

		return collect($result);
	}

	/**
	 * Run the query as a "select" statement against the connection.
	 *
	 * @return array
	 */
	protected function runSelect(): array
	{
		$result = $this->connection->select(
			$this->toSql(), $this->getBindings(), !$this->useWritePdo
		);

		printf('%-50.50s: returned %d results' . PHP_EOL, __METHOD__ . '()', count($result));

		return $result;
	}
}
