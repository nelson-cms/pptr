<?php
declare(strict_types=1);

namespace NelsonCms\Pptr\DI;

use NelsonCms\Pptr\GeneratorConfig;
use NelsonCms\Pptr\Generator;
use NelsonCms\Pptr\GeneratorFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Statement;
use Nette\DI\Helpers;
use Nette\Schema\Elements\AnyOf;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class PptrExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		$parameters = $this->getContainerBuilder()->parameters;
		$tempDir = isset($parameters['tempDir']) ? $parameters['tempDir'] . '/puppeteer/' : null;
		$scriptPath = realpath(__DIR__ . '/../assets/generator.js');

		$expectService = Expect::anyOf(
			Expect::string()->required()->assert(fn ($input) => str_starts_with($input, '@') || class_exists($input) || interface_exists($input)),
			Expect::type(Statement::class)->required(),
		);

		return Expect::structure([
			'connection' => $expectService,
			'tempDir' => Expect::string()->default($tempDir),
			'timeout' => Expect::int()->default(30_000)->min(0)->max(999_999),
			'sandbox' => Expect::bool()->nullable()->default(false),
			'nodeCommand' => Expect::string()->default('node'),
			'scriptPath' => Expect::string()->default($scriptPath),
			'httpUser' => Expect::string()->nullable()->default(null),
			'httpPass' => Expect::string()->nullable()->default(null),
		])->castTo('array');
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('config'))
			->setFactory(GeneratorConfig::class)
			->setArguments((array) $this->getConfig());

		// Add factory
		$builder->addFactoryDefinition($this->prefix('factory'))
			->setImplement(GeneratorFactory::class)
			->getResultDefinition()
			->setFactory(Generator::class);
	}
}
