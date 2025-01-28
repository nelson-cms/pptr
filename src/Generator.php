<?php
declare(strict_types=1);

namespace NelsonCms\Pptr;

use NelsonCms\Pptr\VO\Result;
use NelsonCms\Ssh2\Exceptions\ProcessFailedException;
use NelsonCms\Ssh2\Process;
use Exception;
use NelsonCms\Pptr\Presets\Preset;
use Nette\Http\UrlScript;
use Nette\SmartObject;
use Nette\Utils\FileSystem;

final class Generator
{
	use SmartObject;

	public const int GENERATE_PDF = 1;
	public const int GENERATE_IMAGE = 2;
	public const int GENERATE_BOTH = 3;
	public const string SCRIPT_PATH = __DIR__ . '/assets/generator.js';

	/** @var array<string|null> */
	private array $options = [];

	private ?Preset $preset = null;
	private ?string $tempFileName = null;

	/** @var array<string|null> */
	private array $output;


	public function __construct(
		private readonly GeneratorConfig $generatorConfig
	)
	{
	}


	public function generateFromHtml(string $html, int $mode): Result
	{
		$htmlFilePath = $this->getTempFilePath('.html');
		FileSystem::write($htmlFilePath, $html);

		$this->setOption('--inputMode', 'file');
		$this->setOption('--input', $htmlFilePath);
		$output = $this->generate($mode);

		FileSystem::delete($htmlFilePath);

		return $output;
	}


	public function generateFromUrl(UrlScript $url, int $mode): Result
	{
		$this->setOption('--inputMode', 'url');
		$this->setOption('--input', (string) $url);
		return $this->generate($mode);
	}


	public function setPreset(?Preset $preset): void
	{
		$this->preset = $preset;
	}


	public function setOption(string $name, ?string $value = null): void
	{
		$this->options[$name] = $value;
	}


	/** @param array<string|null> $options */
	public function setOptions(array $options): void
	{
		foreach ($options as $name => $value) {
			$this->setOption($name, $value);
		}
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


	private function generate(int $mode): Result
	{
		if ($this->preset !== null) {
			$this->setOptions($this->preset->getOptions());
		}

		switch ($mode) {
			case self::GENERATE_PDF:
				$this->outputPdf();
				break;
			case self::GENERATE_IMAGE:
				$this->outputImage();
				break;
			case self::GENERATE_BOTH:
				$this->outputPdf();
				$this->outputImage();
				break;
			default:
				throw new Exception('Mode ' . $mode . ' is not defined.');
		}

		$this->setOption('--output', $this->getTempFilePath());

		$this->setOption('--httpUser', $this->generatorConfig->getHttpUser());
		$this->setOption('--httpPass', $this->generatorConfig->getHttpPass());
		$this->setOption('--timeout', (string) $this->generatorConfig->getTimeout());

		if ($this->generatorConfig->getSandbox() === false) {
			$this->setOption('--no-sandbox');
		}

		$process = new Process(
			$this->generatorConfig->getConnection(),
			$this->getCommand(),
			null,
		);
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}

		return new Result(
			$this->getCommand(),
			$process->getOutput(),
			$this->output['pdf'] ?? null,
			$this->output['image'] ?? null,
		);
	}


	private function outputPdf(): void
	{
		$this->setOption('--pdf');
		$this->output['pdf'] = $this->getTempFilePath() . '.pdf';
	}


	private function outputImage(): void
	{
		$this->setOption('--image');
		$this->output['image'] = $this->getTempFilePath() . '.png';
	}


	/** @return list<string> */
	private function getCommand(): array
	{
		$command = [];

		foreach ($this->options as $key => $value) {
			$command[] = match(true) {
				is_null($value), trim($value) === '' => (string) $key,
				default => $key . '=' . $value,
			};
		}

		array_unshift($command, $this->generatorConfig->getNodeCommand(), self::SCRIPT_PATH);

		return $command;
	}
}
