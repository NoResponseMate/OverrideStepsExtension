<?php

declare(strict_types=1);

namespace NoResponseMate\OverrideStepsExtension\ServiceContainer;

use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Testwork\Argument\ServiceContainer\ArgumentExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use NoResponseMate\OverrideStepsExtension\Definition\Cli\AvailableDefinitionController;
use NoResponseMate\OverrideStepsExtension\Definition\DefinitionWriter;
use NoResponseMate\OverrideStepsExtension\Definition\OverridingStepsDefinitionRepository;
use NoResponseMate\OverrideStepsExtension\Definition\Search\RepositorySearchEngine;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class OverrideStepsExtension implements Extension
{
    private const DEFINITION_REPOSITORY_ID = 'nrm.override_steps.definition.overriding_steps_repository';

    public function process(ContainerBuilder $container): void
    {
    }

    public function getConfigKey(): string
    {
        return 'nrm_override_steps';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->overwriteDefinitionRepository($container);
        $this->overwriteDefinitionWriter($container);
        $this->overwriteAvailableDefinitionsController($container);
        $this->overwriteRepositorySearchEngine($container);
    }

    private function overwriteDefinitionRepository(ContainerBuilder $container): void
    {
        $definition = new Definition(OverridingStepsDefinitionRepository::class, [
            new Reference(EnvironmentExtension::MANAGER_ID),
        ]);
        $container->setDefinition(self::DEFINITION_REPOSITORY_ID, $definition);
    }

    private function overwriteDefinitionWriter(ContainerBuilder $container): void
    {
        $definition = new Definition(DefinitionWriter::class, [
            new Reference(EnvironmentExtension::MANAGER_ID),
            new Reference(self::DEFINITION_REPOSITORY_ID),
        ]);
        $container->setDefinition(DefinitionExtension::WRITER_ID, $definition);
    }

    private function overwriteAvailableDefinitionsController(ContainerBuilder $container): void
    {
        $definition = new Definition(AvailableDefinitionController::class, [
            new Reference(SuiteExtension::REGISTRY_ID),
            new Reference(DefinitionExtension::WRITER_ID),
            new Reference('definition.list_printer'),
            new Reference('definition.information_printer'),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1000));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.available_definitions', $definition);
    }

    private function overwriteRepositorySearchEngine(ContainerBuilder $container): void
    {
        $definition = new Definition(RepositorySearchEngine::class, [
            new Reference(self::DEFINITION_REPOSITORY_ID),
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            new Reference(DefinitionExtension::DEFINITION_TRANSLATOR_ID),
            new Reference(ArgumentExtension::PREG_MATCH_ARGUMENT_ORGANISER_ID),
        ]);
        $definition->addTag(DefinitionExtension::SEARCH_ENGINE_TAG, array('priority' => 100));
        $container->setDefinition('definition.search_engine', $definition);
    }
}
