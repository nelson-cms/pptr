<?php
declare(strict_types=1);

namespace NelsonCms\Pptr\Presets;

interface Preset
{
	/** @return array<string|null> */
	public function getOptions(): array;

	public function getShowPrintMarks(): bool;
}
