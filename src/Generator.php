<?php
declare(strict_types=1);

namespace NelsonCms\Pptr;

use NelsonCms\Pptr\Enums\OutputMode;
use NelsonCms\Pptr\VO\Command;
use NelsonCms\Pptr\VO\Result;
use NelsonCms\Ssh2\Exceptions\ProcessFailedException;
use NelsonCms\Ssh2\Process;
use NelsonCms\Pptr\Presets\Preset;
use Nette\Http\UrlScript;
use Nette\SmartObject;
use Nette\Utils\FileSystem;

final class Generator
{
	use SmartObject;

	private ?Preset $preset = null;
	private ?string $tempFileName = null;

	/** @var array<string|null> */
	private array $output;

	private Command $command;

	public function __construct(
		private readonly GeneratorConfig $generatorConfig
	)
	{
		$this->command = new Command(
			$this->generatorConfig->getNodeCommand(),
			$this->generatorConfig->getScriptPath(),
		);
	}


	public function generateFromHtml(string $html, OutputMode $mode): Result
	{
		$htmlFilePath = $this->getTempFilePath('.html');
		FileSystem::write($htmlFilePath, $html);

		$this->command->setOption('--inputMode', 'file');
		$this->command->setOption('--input', $htmlFilePath);
		$output = $this->generate($mode);

		FileSystem::delete($htmlFilePath);

		return $output;
	}


	public function generateFromUrl(UrlScript $url, OutputMode $mode): Result
	{
		$this->command->setOption('--inputMode', 'url');
		$this->command->setOption('--input', (string) $url);
		return $this->generate($mode);
	}


	public function setPreset(?Preset $preset): void
	{
		$this->preset = $preset;
	}


	private function getTempFileName(): string
	{
		if ($this->tempFileName === null || trim($this->tempFileName) === '') {
			$this->tempFileName = time() . '_-_' . md5((string) mt_rand());
		}

		return $this->tempFileName;
	}


	private function getTempFilePath(?string $suffix = null): string
	{
		$suffix = match(true) {
			is_null($suffix), trim($suffix) === '' => '',
			default => $suffix,
		};

		$parts = [
			$this->generatorConfig->getTempDir(),
			$this->getTempFileName(),
			$suffix,
		];

		return implode('', $parts);
	}


	private function generate(OutputMode $mode): Result
	{
		if ($this->preset !== null) {
			$this->command->setOptions($this->preset->getOptions());
		}

		switch ($mode) {
			case OutputMode::PDF:
				$this->outputPdf();
				break;
			case OutputMode::IMG:
				$this->outputImage();
				break;
			case OutputMode::BOTH:
				$this->outputPdf();
				$this->outputImage();
				break;
		}

		$this->command->setOption('--output', $this->getTempFilePath());

		$this->command->setOption('--httpUser', $this->generatorConfig->getHttpUser());
		$this->command->setOption('--httpPass', $this->generatorConfig->getHttpPass());
		$this->command->setOption('--timeout', (string) $this->generatorConfig->getTimeout());

		if ($this->generatorConfig->getSandbox() === false) {
			$this->command->setOption('--no-sandbox');
		}

		if ($this->generatorConfig->getOutline() === true) {
			$this->command->setOption('--outline');
		}

		$process = new Process(
			$this->generatorConfig->getConnection(),
			$this->command->getCommand(),
			null,
		);
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}

		dumpe($this->command->getCommand());

		return new Result(
			$this->command->getCommand(),
			$process->getOutput(),
			$this->output['pdf'] ?? null,
			$this->output['image'] ?? null,
		);
	}


	private function outputPdf(): void
	{
		$this->command->setOption('--pdf');
		$this->output['pdf'] = $this->getTempFilePath() . '.pdf';
	}


	private function outputImage(): void
	{
		$this->command->setOption('--image');
		$this->output['image'] = $this->getTempFilePath() . '.png';
	}
}
