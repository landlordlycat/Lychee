<?php

namespace App\Database\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Builder extends BaseBuilder
{
	public static bool $isDebugEnabled = false;

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

		if (self::$isDebugEnabled) {
			printf('%s::%s: returned %d results', __CLASS__, __METHOD__, count($result));
		}

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

		if (self::$isDebugEnabled) {
			printf('%s::%s: returned %d results', __CLASS__, __METHOD__, count($result));
		}

		return $result;
	}
}
