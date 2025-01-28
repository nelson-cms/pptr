<?php
declare(strict_types=1);

namespace NelsonCms\Pptr\Presets;

abstract class DefaultPreset implements Preset
{
	/** @var array<string|null> */
	protected array $defaultOptions = [];

	/** @var array<string|null> */
	protected array $options = [];


	public function getOptions(): array
	{
		return array_merge($this->defaultOptions, $this->options);
	}


	public function getShowPrintMarks(): bool
	{
		return false;
	}
}
