<?php


namespace MockingTools;


class ServiceDependencyMockerException extends \Exception
{
	const SERVICE_CLASS_NOT_FOUND = 1;
	const DEPENDENCY_NOT_FOUND = 2;
}
