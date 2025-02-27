<?php
declare(strict_types=1);

namespace NelsonCms\Pptr;

use NelsonCms\Ssh2\Connection;
use Nette\SmartObject;

final class GeneratorConfig
{
	use SmartObject;

	/** @var Connection */
	private $connection;

	/** @var string */
	private $tempDir;

	/** @var int */
	private $timeout;

	/** @var string */
	private $nodeCommand;

	/** @var string */
	private $scriptPath;

	/** @var bool|null */
	private $sandbox;

	/** @var bool|null */
	private $outline;

	/** @var string|null */
	private $httpUser;

	/** @var string|null */
	private $httpPass;


	public function __construct(
		Connection $connection,
		string $tempDir,
		int $timeout,
		string $nodeCommand,
		string $scriptPath,
		?bool $sandbox,
		?bool $outline,
		?string $httpUser,
		?string $httpPass
	)
	{
		$this->httpPass = $httpPass;
		$this->httpUser = $httpUser;
		$this->outline = $outline;
		$this->sandbox = $sandbox;
		$this->scriptPath = $scriptPath;
		$this->nodeCommand = $nodeCommand;
		$this->timeout = $timeout;
		$this->tempDir = $tempDir;
		$this->connection = $connection;
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


	public function getScriptPath(): string
	{
		return $this->scriptPath;
	}


	public function getSandbox(): ?bool
	{
		return $this->sandbox;
	}


	public function getOutline(): ?bool
	{
		return $this->outline;
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
