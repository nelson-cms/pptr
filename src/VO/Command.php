<?php

namespace NelsonCms\Pptr\VO;

final class Command
{

	/** @param array<string, string|null> $options */
	public function __construct(
		private readonly string $nodeCommand,
		private readonly string $scriptPath,
		private array $options = [],
	)
	{
	}


	/** @return list<string> */
	public function getCommand(): array
	{
		$command = [];

		foreach ($this->options as $key => $value) {
			$command[] = match(true) {
				is_null($value), trim($value) === '' => $key,
				default => $key . '=' . $value,
			};
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
