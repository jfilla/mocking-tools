<?php

namespace MockingTools;


use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';


class ServiceDependencyMockerTest extends TestCase
{

	public function testEdgeCases()
	{
		Assert::exception(
			function () {
				new ServiceDependencyMocker('nonExistingClass');
			},
			ServiceDependencyMockerException::class,
			NULL,
			ServiceDependencyMockerException::SERVICE_CLASS_NOT_FOUND
		);
		$this->assertCreateWithNoDependencies(DependencyA::class);


	}

	public function testWithDependencies()
	{
		$ServiceDependencyMocker = ServiceDependencyMocker::createFromClassName(Service::class);
		$service = $ServiceDependencyMocker->getService();
		Assert::type(Service::class, $service);
		Assert::type(DependencyA::class, $ServiceDependencyMocker->getDependencyMock(DependencyA::class));
		Assert::type(MockInterface::class, $ServiceDependencyMocker->getDependencyMock(DependencyA::class));
		Assert::exception(
			function () use ($ServiceDependencyMocker) {
				$ServiceDependencyMocker->getDependencyMock('notDependency');
			},
			ServiceDependencyMockerException::class,
			NULL,
			ServiceDependencyMockerException::DEPENDENCY_NOT_FOUND
		);
		$dependencyAMock = $ServiceDependencyMocker->getDependencyMock(DependencyA::class);
		$dependencyAMock->shouldReceive('getMeaning')
			->andReturn(42);
		$ServiceDependencyMocker->callOnAllDependenciesWithNoExpectation(
			function ($mock) {
				$mock->shouldReceive('getMeaning')
					->andReturn(0);
			}
		);
		Assert::equal(42, $service->getMeaning());
		$ServiceDependencyMocker = ServiceDependencyMocker::createFromClassName(Service::class);
		$service = $ServiceDependencyMocker->getService();
		$ServiceDependencyMocker->callOnAllDependencies(
			function ($mock) {
				$mock->shouldReceive('getMeaning')
					->andReturn(0);
			}
		);
		Assert::equal(0, $service->getMeaning());
	}

	private function assertCreateWithNoDependencies($className)
	{
		$ServiceDependencyMocker = ServiceDependencyMocker::createFromClassName($className);
		Assert::type($className, $ServiceDependencyMocker->getService());
		Assert::equal([], $ServiceDependencyMocker->getDependencies());
	}


}

class Service
{
	/**
	 * @var DependencyA
	 */
	private $dependencyA;

	/**
	 * @var DependencyB
	 */
	private $dependencyB;

	/**
	 * @var DependencyC
	 */
	private $dependencyC;

	public function __construct(DependencyA $dependencyA, DependencyB $dependencyB, DependencyC $dependencyC)
	{
		$this->dependencyA = $dependencyA;
		$this->dependencyB = $dependencyB;
		$this->dependencyC = $dependencyC;
	}

	public function getMeaning()
	{
		return $this->dependencyA->getMeaning() + $this->dependencyB->getMeaning() + $this->dependencyC->getMeaning();
	}


}


class DependencyA
{

	public function getMeaning()
	{
	}

}


class DependencyB
{

	public function getMeaning()
	{
	}

}


class DependencyC
{

	public function getMeaning()
	{
	}

}


$test = new ServiceDependencyMockerTest();
$test->run();