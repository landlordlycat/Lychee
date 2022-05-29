<?php

namespace App\Database;

use Illuminate\Database\Connection as BaseConnection;

class Connection extends BaseConnection
{
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
}
