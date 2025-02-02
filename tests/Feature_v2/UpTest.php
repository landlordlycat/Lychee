<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use Tests\AbstractTestCase;
use Tests\Feature_v2\Base\BaseApiV2Test;

class UpTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = AbstractTestCase::get('up');
		$this->assertOk($response);
	}
}