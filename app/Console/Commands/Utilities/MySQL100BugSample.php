<?php

namespace App\Console\Commands\Utilities;

interface MySQL100BugSample
{
	public function albumID(): string;

	public function photoIDs(): array;

	public function expectedNumberOfSizeVariants(): int;
}
