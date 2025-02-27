<?php

namespace NelsonCms\Pptr\VO;

use NelsonCms\Pptr\Exceptions\PptrFailedException;

final class Result
{
	/** @var list<string> */
	private $command;

	/** @var string */
	private $console;

	/** @var string|null */
	private $pdfPath = null;

	/** @var string|null */
	private $imgPath = null;


	/** @param list<string> $command */
	public function __construct(
		array $command,
		string $console,
		?string $pdfPath = null,
		?string $imgPath = null
	)
	{
		$this->imgPath = $imgPath;
		$this->pdfPath = $pdfPath;
		$this->console = $console;
		$this->command = $command;
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
