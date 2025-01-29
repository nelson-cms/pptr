<?php

namespace NelsonCms\Pptr\VO;

use NelsonCms\Pptr\Exceptions\PptrFailedException;

final readonly class Result
{

	/** @param list<string> $command */
	public function __construct(
		private array $command,
		private string $console,
		private ?string $pdfPath = null,
		private ?string $imgPath = null,
	)
	{
		if ($this->console !== '') {
			throw new PptrFailedException($this);
		}
	}


	/** @return list<string> */
	public function getCommand(): array
	{
		return $this->command;
	}


	public function getConsole(): string
	{
		return $this->console;
	}


	public function getPdfPath(): ?string
	{
		return $this->pdfPath;
	}


	public function getImgPath(): ?string
	{
		return $this->imgPath;
	}

}
