<?php

namespace NelsonCms\Pptr\Tests\VO;

use NelsonCms\Pptr\VO\Command;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type CommandOptions array<string, string|int|null>
 */
class CommandTest extends TestCase
{
	private static string $nodeCommand = 'node';
	private static string $scriptPath = 'generator.js';
	private static Command $command;


	public static function setUpBeforeClass(): void
	{
		self::$command = new Command(self::$nodeCommand, self::$scriptPath);
	}


	/** @return list<CommandOptions> */
	public static function getCommandProvider(): array
	{
		return [
			[null, []],
			['--outline', ['--outline' => null]],
			['--inputMode=file', ['--inputMode' => 'file']],
			['--viewportHeight=1122', ['--viewportHeight' => 1122]],
		];
	}


	/** @param CommandOptions $options */
	#[DataProvider('getCommandProvider')]
	public function testGetCommand(?string $expected, array $options): void
	{
		$cmd = clone self::$command;
		$cmd->setOptions($options);

		$parts = [
			self::$nodeCommand . ' ' . self::$scriptPath,
			$expected,
		];

		$parts = array_filter($parts);

		$this->assertSame(
			implode(' ', $parts),
			implode(' ', $cmd->getCommand())
		);
	}
}
