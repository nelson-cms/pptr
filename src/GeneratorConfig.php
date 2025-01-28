<?php
declare(strict_types=1);

namespace NelsonCms\Pptr;

use NelsonCms\Ssh2\Connection;
use Nette\SmartObject;

final class GeneratorConfig
{
	use SmartObject;

	public function __construct(
		private readonly Connection $connection,
		private readonly string $tempDir,
		private readonly int $timeout,
		private readonly string $nodeCommand,
		private readonly ?bool $sandbox,
		private readonly ?string $httpUser,
		private readonly ?string $httpPass,
	)
	{
	}


	public function getConnection(): Connection
	{
		return $this->connection;
	}


	public function getTempDir(): string
	{
		return $this->tempDir;
	}


	public function getTimeout(): int
	{
		return $this->timeout;
	}


	public function getNodeCommand(): string
	{
		return $this->nodeCommand;
	}


	public function getSandbox(): ?bool
	{
		return $this->sandbox;
	}


	public function getHttpUser(): ?string
	{
		return $this->httpUser;
	}


	public function getHttpPass(): ?string
	{
		return $this->httpPass;
	}
}
