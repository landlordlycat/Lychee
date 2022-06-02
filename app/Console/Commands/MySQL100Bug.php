<?php

/**
 * @noinspection SqlNoDataSourceInspection
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace App\Console\Commands;

use App\Console\Commands\Utilities\MySQL100BugSample;
use App\Console\Commands\Utilities\MySQL100BugSample1;
use App\Console\Commands\Utilities\MySQL100BugSample2;
use App\Database\Connection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @noinspection PhpUnused
 */
class MySQL100Bug extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:mysql_1000_bug';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Preliminary quick-and-dirty hack to track down the MySQL 1000 Bug';

	protected ?MySQL100BugSample $sample = null;

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$this->determineSample();

		// As a sanity check make a manually constructed SQL query which seems to work everywhere
		$this->line('');
		$this->line('');
		$this->line('Test #1 - Low-level query without bindings');
		$dbResult = DB::select(
			'SELECT * from size_variants WHERE photo_id IN (' .
			implode(',', array_map(fn (string $id) => '"' . $id . '"', $this->sample->photoIDs())) .
			')');
		$this->checkDbResult($dbResult);

		Connection::$beVerbose = true;

		// Make a low-level DB query which already fails
		$this->line('');
		$this->line('');
		$this->line('Test #2 - Low-level query with bindings');
		$dbResult = DB::select(
			'SELECT * from size_variants WHERE photo_id IN (' .
			implode(',', array_fill(0, count($this->sample->photoIDs()), '?')) .
			')',
			$this->sample->photoIDs()
		);
		$this->checkDbResult($dbResult);

		// See https://stackoverflow.com/questions/14416601/pdo-php-bindvalue-doesnt-work

		return 0;
	}

	protected function checkDbResult(array $dbResult): void
	{
		if (count($dbResult) !== $this->sample->expectedNumberOfSizeVariants()) {
			$this->line(
				'Error: Incorrect number of directly hydrated size variants with DB facade; got ' .
				count($dbResult) .
				', expected ' .
				$this->sample->expectedNumberOfSizeVariants()
			);
		} else {
			$this->line('Everything seems fine :-(');
		}
	}

	protected function determineSample(): void
	{
		/** @var MySQL100BugSample[] $samples */
		$samples = [new MySQL100BugSample1(), new MySQL100BugSample2()];
		foreach ($samples as $sample) {
			if (
				DB::table('base_albums')->where('id', '=', $sample->albumID())->count() === 1 ||
				DB::table('photos')->where('album_id', '=', $sample->albumID())->count() === count($sample->photoIDs())
			) {
				$this->sample = $sample;

				return;
			}
		}

		throw new \RuntimeException('Error: Wrong DB dump for this test command; skipping all remaining tests');
	}
}
