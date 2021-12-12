<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\RandomIDRule;

class GetPhotoRequest extends BaseApiRequest implements HasPhotoID
{
	use HasPhotoIDTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoVisible($this->photoID);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoID::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoID = $values[HasPhotoID::PHOTO_ID_ATTRIBUTE];
	}
}
