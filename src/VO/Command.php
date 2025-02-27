<?php

namespace NelsonCms\Pptr\VO;

final class Command
{
	/** @var array<string, string|null> */
	private $options = [];

	/** @var string */
	private $nodeCommand;

	/** @var string */
	private $scriptPath;


	/** @param array<string, string|null> $options */
	public function __construct(
		string $nodeCommand,
		string $scriptPath,
		array $options = []
	)
	{
		$this->scriptPath = $scriptPath;
		$this->nodeCommand = $nodeCommand;
		$this->options = $options;
	}


	/** @return list<string> */
	public function getCommand(): array
	{
		$command = [];

		foreach ($this->options as $key => $value) {
			switch (true) {
				case is_null($value):
				case trim($value) === '': // @phpstan-ignore-line
					$command[] = $key;
					break;
				default:
					$command[] = $key . '=' . $value;
					break;
			}
		}

		array_unshift($command, $this->nodeCommand, $this->scriptPath);

		return $command;
	}


	public function setOption(string $name, ?string $value = null): void
	{
		$this->options[$name] = $value;
	}


	/** @param array<string, string|null> $options */
	public function setOptions(array $options): void
	{
		foreach ($options as $name => $value) {
			$this->setOption($name, $value);
		}
	}

}
