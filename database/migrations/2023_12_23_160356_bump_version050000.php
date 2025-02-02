<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'version')->update(['value' => '050000']);
		DB::table('configs')->where('value', '=', 'Lychee v4')->update(['value' => 'Lychee v5']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'version')->update(['value' => '041300']);
		DB::table('configs')->where('value', '=', 'Lychee v5')->update(['value' => 'Lychee v4']);
	}
};
