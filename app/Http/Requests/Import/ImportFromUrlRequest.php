<?php

namespace App\Http\Requests\Import;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Rules\AlbumIDRule;

class ImportFromUrlRequest extends BaseApiRequest implements HasAlbumID
{
	use HasAlbumIDTrait;

	public const URL_ATTRIBUTE = 'url';

	/**
	 * @var string[]
	 */
	protected array $urls = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['present', new AlbumIDRule(true)],
			self::URL_ATTRIBUTE => 'required|array|min:1',
			self::URL_ATTRIBUTE . '.*' => 'required|string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		if (empty($this->albumID)) {
			$this->albumID = null;
		}
		$this->urls = str_replace(' ', '%20', $values[self::URL_ATTRIBUTE]);
	}

	/**
	 * @return string[]
	 */
	public function urls(): array
	{
		return $this->urls;
	}
}
