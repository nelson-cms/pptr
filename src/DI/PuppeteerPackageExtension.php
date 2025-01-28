<?php
declare(strict_types=1);

namespace NelsonCms\Pptr\DI;

use NelsonCms\Pptr\GeneratorConfig;
use NelsonCms\Pptr\Generator;
use NelsonCms\Pptr\GeneratorFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class PuppeteerPackageExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'tempDir' => Expect::string()->default('%tempDir%/puppeteer/'),
			'timeout' => Expect::int()->default(120)->min(10)->max(999),
			'sandbox' => Expect::bool()->nullable()->default(false),
			'nodeCommand' => Expect::string()->default('node'),
			'httpUser' => Expect::string()->nullable()->default(null),
			'httpPass' => Expect::string()->nullable()->default(null),
		]);
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$params = $this->getContainerBuilder()->parameters;
		$config = Helpers::expand((array) $this->getConfig(), $params);
		$config['connection'] = '@puppeteerSsh.connection';

		$builder->addDefinition(null)
			->setFactory(GeneratorConfig::class)
			->setArguments($config);

		// Add factory
		$builder->addFactoryDefinition(null)
			->setImplement(GeneratorFactory::class)
			->getResultDefinition()
			->setFactory(Generator::class);
	}
}
