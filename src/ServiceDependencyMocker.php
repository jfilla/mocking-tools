<?php

namespace MockingTools;

use Closure;
use Mockery as m;
use ReflectionClass;
use ReflectionException;

class ServiceDependencyMocker
{


	private $service;

	/**
	 * @var m\MockInterface[]
	 */
	private $dependencies = [];


	public function __construct($className)
	{
		try {
			$reflection = new ReflectionClass($className);
		} catch (ReflectionException $e) {
			throw new ServiceDependencyMockerException(
				"Service class with className '$className' not found.",
				ServiceDependencyMockerException::SERVICE_CLASS_NOT_FOUND,
				$e
			);
		}
		foreach ($this->getConstructorParameters($reflection) as $constructorParameter) {
			$dependencyClassName = $constructorParameter->getClass()->getName();
			$this->dependencies[$dependencyClassName] = m::mock($dependencyClassName);
		}
		$this->service = $reflection->newInstanceArgs($this->dependencies);
	}


	public static function createFromClassName($className)
	{
		return new static($className);
	}

	public function getService()
	{
		return $this->service;
	}

	/**
	 * @return m\MockInterface[]
	 */
	public function getDependencies()
	{
		return $this->dependencies;
	}

	/**
	 * @return m\MockInterface
	 */
	public function getDependencyMock($className)
	{
		if (isset($this->dependencies[$className])) {
			return $this->dependencies[$className];
		}
		throw new ServiceDependencyMockerException(
			"Dependency with className '$className' not found.",
			ServiceDependencyMockerException::DEPENDENCY_NOT_FOUND
		);
	}

	public function callOnAllDependenciesWithNoExpectation(Closure $closure)
	{
		foreach ($this->dependencies as $dependency) {
			if ($dependency->mockery_getExpectationCount() === 0) {
				call_user_func($closure, $dependency);
			}
		}
	}

	public function callOnAllDependencies(Closure $closure)
	{
		foreach ($this->dependencies as $dependency) {
			call_user_func($closure, $dependency);
		}
	}

	private function getConstructorParameters(ReflectionClass $reflection)
	{
		$constructor = $reflection->getConstructor();
		if ($constructor === NULL) {
			return [];
		} else {
			return $constructor->getParameters();
		}
	}

}

