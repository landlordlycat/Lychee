<?php

namespace App\Console\Commands;

use App\Actions\Diagnostics\Checks\MySQL1000Check;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException
	 */
	public function handle(): int
	{
		// Assert that we are using the correct DB to avoid a ghost hunt
		if (
			DB::table('albums')->where('id', '=', MySQL1000Check::ALBUM_ID)->count() !== 1 ||
			DB::table('photos')->where('album_id', '=', MySQL1000Check::ALBUM_ID)->count() !== count(MySQL1000Check::PHOTO_IDS)
		) {
			$this->line('Error: Wrong DB dump for this diagnostic; skipping all remaining tests');

			return -1;
		}

		// Try to get size variants with low-level method call
		$sizeVariantsRaw = DB::table('size_variants')
			->whereIn('photo_id', MySQL1000Check::PHOTO_IDS)
			->get();

		if ($sizeVariantsRaw->count() !== MySQL1000Check::EXPECTED_NUMBER_OF_SIZE_VARIANTS) {
			$this->line(
				'Error: Incorrect number of directly hydrated size variants with DB facade; got ' .
				$sizeVariantsRaw->count() .
				', expected ' .
				MySQL1000Check::EXPECTED_NUMBER_OF_SIZE_VARIANTS
			);
		} else {
			$this->line('Everything seems fine :-(');
		}

		return 0;
	}
}
