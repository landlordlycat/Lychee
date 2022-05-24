<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Facades\AccessControl;
use App\Models\Configs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\TestCase;

class GeoDataTest extends TestCase
{
	protected PhotosUnitTest $photos_tests;
	protected AlbumsUnitTest $albums_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->photos_tests = new PhotosUnitTest($this);
		$this->albums_tests = new AlbumsUnitTest($this);

		AccessControl::log_as_id(0);

		// Assert that photo table is empty
		static::assertDatabaseCount('sym_links', 0);
		static::assertDatabaseCount('size_variants', 0);
		static::assertDatabaseCount('photos', 0);
	}

	public function tearDown(): void
	{
		// Clean up remaining stuff from tests
		DB::table('sym_links')->delete();
		DB::table('size_variants')->delete();
		DB::table('photos')->delete();
		self::cleanPublicFolders();

		AccessControl::logout();

		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testGeo(): void
	{
		// save initial value
		$map_display_value = Configs::get_value('map_display');

		try {
			$id = $this->photos_tests->upload(
				TestCase::createUploadedFile(TestCase::SAMPLE_FILE_MONGOLIA_IMAGE)
			);

			$response = $this->photos_tests->get($id);
			$this->photos_tests->see_in_unsorted($id);
			/*
			 * Check some Exif data
			 * The metadata extractor is unable to extract an explicit timezone
			 * for the test file.
			 * Hence, the attribute `taken_at` is relative to the default timezone
			 * of the application.
			 * Actually, the `exiftool` reports an attribute `Time Zone: +08:00`,
			 * if the tool is invoked from the command line, but the PHP wrapper
			 * \PHPExif\Exif does not use it.
			 */
			$taken_at = Carbon::create(
				2011, 8, 17, 16, 39, 37
			);
			$response->assertJson(
				[
					'id' => $id,
					'title' => 'mongolia',
					'type' => 'image/jpeg',
					'iso' => '200',
					'aperture' => 'f/13.0',
					'make' => 'NIKON CORPORATION',
					'model' => 'NIKON D5000',
					'shutter' => '1/640 s',
					'focal' => '44 mm',
					'altitude' => 1633,
					'license' => 'none',
					'taken_at' => $taken_at->format('Y-m-d\TH:i:s.uP'),
					'taken_at_orig_tz' => $taken_at->getTimezone()->getName(),
					'is_public' => 0,
					'is_downloadable' => true,
					'is_share_button_visible' => true,
					'size_variants' => [
						'thumb' => [
							'width' => 200,
							'height' => 200,
						],
						'small' => [
							'width' => 542,
							'height' => 360,
						],
						'medium' => null,
						'medium2x' => null,
						'original' => [
							'width' => 1280,
							'height' => 850,
							'filesize' => 201316,
						],
					],
				]
			);

			$albumID = $this->albums_tests->add(null, 'test_mongolia')->offsetGet('id');
			$this->photos_tests->set_album($albumID, [$id]);
			$this->photos_tests->dont_see_in_unsorted($id);
			$response = $this->albums_tests->get($albumID);
			$responseObj = static::convertJsonToObject($response);
			static::assertCount(1, $responseObj->photos);
			static::assertEquals($id, $responseObj->photos[0]->id);

			// now we test position Data

			// set to 0
			Configs::set('map_display', '0');
			static::assertEquals('0', Configs::get_value('map_display'));
			$this->albums_tests->AlbumsGetPositionDataFull(); // we need to fix this

			// set to 1
			Configs::set('map_display', '1');
			static::assertEquals('1', Configs::get_value('map_display'));
			$response = $this->albums_tests->AlbumsGetPositionDataFull();
			$responseObj = static::convertJsonToObject($response);
			static::assertObjectHasAttribute('photos', $responseObj);
			static::assertCount(1, $responseObj->photos);
			static::assertEquals($id, $responseObj->photos[0]->id);

			// set to 0
			Configs::set('map_display', '0');
			static::assertEquals('0', Configs::get_value('map_display'));
			$this->albums_tests->AlbumGetPositionDataFull($albumID); // we need to fix this

			// set to 1
			Configs::set('map_display', '1');
			static::assertEquals('1', Configs::get_value('map_display'));
			$response = $this->albums_tests->AlbumGetPositionDataFull($albumID);
			$responseObj = static::convertJsonToObject($response);
			static::assertObjectHasAttribute('photos', $responseObj);
			static::assertCount(1, $responseObj->photos);
			static::assertEquals($id, $responseObj->photos[0]->id);
		} finally {
			Configs::set('map_display', $map_display_value);
		}
	}
}
