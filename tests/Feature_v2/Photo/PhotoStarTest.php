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

namespace Tests\Feature_v2\Photo;

use Tests\Feature_v2\Base\BaseApiV2Test;

class PhotoStarTest extends BaseApiV2Test
{
	public function testSetStarPhotoUnauthorizedForbidden(): void
	{
		$response = $this->postJson('Photo::star', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::star', [
			'photo_ids' => [$this->photo1->id],
			'is_starred' => true,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::star', [
			'photo_ids' => [$this->photo1->id],
			'is_starred' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testSetStarPhotoAuthorizedOwner(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::star', []);
		$this->assertUnprocessable($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::star', [
			'photo_ids' => [$this->photo1->id],
			'is_starred' => true,
		]);
		$this->assertNoContent($response);

		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Album', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'config' => [],
			'resource' => [
				'photos' => [
					[
						'id' => $this->photo1->id,
						'is_starred' => true,
					],
				],
			],
		]);
	}
}