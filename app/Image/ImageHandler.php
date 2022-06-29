<?php

namespace App\Image;

use App\DTO\ImageDimension;
use App\Exceptions\Handler;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Models\Configs;

class ImageHandler extends BaseImageHandler
{
	public const NO_HANDLER_EXCEPTION_MSG = 'No suitable image handler found';

	/**
	 * The class names of the engines to use.
	 *
	 * @var string[]
	 */
	protected array $engineClasses = [];

	/**
	 * The selected image handler.
	 *
	 * @var ImageHandlerInterface|null
	 */
	protected ?ImageHandlerInterface $engine = null;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(int $compressionQuality = BaseImageHandler::USER_DEFINED_COMPRESSION_QUALITY)
	{
		parent::__construct($compressionQuality);
		if (Configs::hasImagick()) {
			$this->engineClasses[] = ImagickHandler::class;
		}
		$this->engineClasses[] = GdHandler::class;
	}

	public function __clone()
	{
		if ($this->engine !== null) {
			$this->engine = clone $this->engine;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(MediaFile $file): void
	{
		$this->reset();
		$lastException = null;

		foreach ($this->engineClasses as $engineClass) {
			try {
				$engine = new $engineClass($this->compressionQuality);
				if ($engine instanceof ImageHandlerInterface) {
					$this->engine = $engine;
					$this->engine->load($file);

					return;
				} else {
					throw new LycheeLogicException('$engine is not an instance of ImageHandlerInterface');
				}
			} catch (\Throwable $e) {
				// Report the error to the log, but don't fail yet.
				Handler::reportSafely($e);
				$lastException = $e;
				$this->engine = null;
			}
		}

		throw new MediaFileOperationException(self::NO_HANDLER_EXCEPTION_MSG, $lastException);
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(MediaFile $file, bool $collectStatistics = false): ?StreamStat
	{
		return $this->engine->save($file, $collectStatistics);
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->engine?->reset();
		$this->engine = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function scale(ImageDimension $dstDim): ImageDimension
	{
		return $this->engine->scale($dstDim);
	}

	/**
	 * {@inheritDoc}
	 */
	public function crop(ImageDimension $dstDim): void
	{
		$this->engine->crop($dstDim);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rotate(int $angle): ImageDimension
	{
		return $this->engine->rotate($angle);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDimensions(): ImageDimension
	{
		return $this->engine->getDimensions();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLoaded(): bool
	{
		return $this->engine !== null && $this->engine->isLoaded();
	}
}
