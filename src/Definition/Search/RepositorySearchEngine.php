<?php

declare(strict_types=1);

namespace NoResponseMate\OverrideStepsExtension\Definition\Search;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Exception\AmbiguousMatchException;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Search\SearchEngine;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Definition\Translator\DefinitionTranslator;
use Behat\Gherkin\Node\ArgumentInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Argument\ArgumentOrganiser;
use Behat\Testwork\Environment\Environment;
use NoResponseMate\OverrideStepsExtension\Definition\OverridingStepsDefinitionRepository;

final class RepositorySearchEngine implements SearchEngine
{
    /**
    * @var OverridingStepsDefinitionRepository
    */
    private $repository;

    /**
     * @var PatternTransformer
     */
    private $patternTransformer;

    /**
     * @var DefinitionTranslator
     */
    private $translator;

    /**
     * @var ArgumentOrganiser
     */
    private $argumentOrganiser;

    /**
     * @param OverridingStepsDefinitionRepository $repository
     * @param PatternTransformer   $patternTransformer
     * @param DefinitionTranslator $translator
     * @param ArgumentOrganiser    $argumentOrganiser
     */
    public function __construct(
        OverridingStepsDefinitionRepository $repository,
        PatternTransformer $patternTransformer,
        DefinitionTranslator $translator,
        ArgumentOrganiser $argumentOrganiser
    ) {
        $this->repository = $repository;
        $this->patternTransformer = $patternTransformer;
        $this->translator = $translator;
        $this->argumentOrganiser = $argumentOrganiser;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AmbiguousMatchException
     */
    public function searchDefinition(
        Environment $environment,
        FeatureNode $feature,
        StepNode $step
    ): ?SearchResult {
        $suite = $environment->getSuite();
        $language = $feature->getLanguage();
        $stepText = $step->getText();
        $multi = $step->getArguments();

        $definitions = array();
        $result = null;

        foreach ($this->repository->getEnvironmentDefinitions($environment) as $definition) {
            $definition = $this->translator->translateDefinition($suite, $definition, $language);

            if (null === ($newResult = $this->match($definition, $stepText, $multi))) {
                continue;
            }

            $result = $newResult;
            $definitions[] = $newResult->getMatchedDefinition();
        }

        if (count($definitions) > 1) {
            /** @phpstan-ignore-next-line */
            throw new AmbiguousMatchException($result->getMatchedText(), $definitions);
        }

        return $result;
    }

    /**
     * @param ArgumentInterface[] $multiline
     */
    private function match(Definition $definition, string $stepText, array $multiline): ?SearchResult
    {
        $regex = $this->patternTransformer->transformPatternToRegex($definition->getPattern());

        if (!preg_match($regex, $stepText, $match)) {
            return null;
        }

        $function = $definition->getReflection();
        $match = array_merge($match, array_values($multiline));
        $arguments = $this->argumentOrganiser->organiseArguments($function, $match);

        return new SearchResult($definition, $stepText, $arguments);
    }
}
