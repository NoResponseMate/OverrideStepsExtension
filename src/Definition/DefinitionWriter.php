<?php

declare(strict_types=1);

namespace NoResponseMate\OverrideStepsExtension\Definition;

use Behat\Behat\Definition\Printer\DefinitionPrinter;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Suite\Suite;

/**
 * @see \Behat\Behat\Definition\DefinitionWriter
 */
final class DefinitionWriter
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;
    /**
     * @var OverridingStepsDefinitionRepository
     */
    private $repository;

    /**
     * @param EnvironmentManager   $environmentManager
     * @param OverridingStepsDefinitionRepository $repository
     */
    public function __construct(
        EnvironmentManager $environmentManager,
        OverridingStepsDefinitionRepository $repository)
    {
        $this->environmentManager = $environmentManager;
        $this->repository = $repository;
    }

    /**
     * Prints definitions for provided suite using printer.
     *
     * @param DefinitionPrinter $printer
     * @param Suite $suite
     */
    public function printSuiteDefinitions(DefinitionPrinter $printer, Suite $suite): void
    {
        $environment = $this->environmentManager->buildEnvironment($suite);
        $definitions = $this->repository->getEnvironmentDefinitions($environment);

        $printer->printDefinitions($suite, $definitions);
    }
}
