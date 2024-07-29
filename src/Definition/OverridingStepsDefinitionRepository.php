<?php

declare(strict_types=1);

namespace NoResponseMate\OverrideStepsExtension\Definition;

use Behat\Behat\Definition\Definition;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;

/**
 * @see \Behat\Behat\Definition\DefinitionRepository
 */
final class OverridingStepsDefinitionRepository
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->environmentManager = $environmentManager;
    }

    /**
     * @param Environment $environment
     *
     * @return Definition[]
     */
    public function getEnvironmentDefinitions(Environment $environment): array
    {
        $definitions = [];

        foreach ($this->environmentManager->readEnvironmentCallees($environment) as $callee) {
            if (!$callee instanceof Definition) {
                continue;
            }

            $pattern = $callee->getPattern();
            $definitions[$pattern] = $callee;
        }

        return array_values($definitions);
    }
}
