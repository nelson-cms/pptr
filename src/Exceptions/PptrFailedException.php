<?php
declare(strict_types=1);

namespace NelsonCms\Pptr\Exceptions;

use NelsonCms\Pptr\VO\Result;
use RuntimeException;

class PptrFailedException extends RuntimeException
{
	public function __construct(Result $output)
	{
		$error = sprintf('The command "%s" failed.'."\nError Message: %s",
			implode(' ', $output->getCommand()),
			$output->getConsole(),
		);

		parent::__construct($error);
	}
}
