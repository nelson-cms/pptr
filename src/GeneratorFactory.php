<?php
declare(strict_types=1);

namespace NelsonCms\Pptr;

interface GeneratorFactory
{
	public function create(): Generator;
}
