<?php
declare(strict_types=1);

namespace JvMTECH\SelectiveMixins\Tests\Functional\Factory\NodeTypeManager;

use Neos\ContentRepository\Core\NodeType\NodeTypeManager;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\Configuration\NodeTypeEnrichmentService;
use Neos\ContentRepositoryRegistry\Factory\NodeTypeManager\NodeTypeManagerFactoryInterface;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Package\PackageManager;
use Symfony\Component\Yaml\Yaml;

class TestingNodeTypeManagerFactory implements NodeTypeManagerFactoryInterface
{
    public function __construct(
        private readonly ConfigurationManager $configurationManager,
        private readonly NodeTypeEnrichmentService $nodeTypeEnrichmentService,
        private readonly PackageManager $packageManager,

    ) {
    }

    public function build(
        ContentRepositoryId $contentRepositoryId,
        array $options
    ): NodeTypeManager {
        return NodeTypeManager::createFromArrayConfigurationLoader(
            function () {
                $configuration = array_merge(
                    $this->configurationManager->getConfiguration('NodeTypes'),
                    Yaml::parseFile($this->packageManager->getPackage('JvMTECH.SelectiveMixins')->getPackagePath() . 'Tests/Functional/Fixtures/NodeTypes.yaml'),
                );
                return $this->nodeTypeEnrichmentService->enrichNodeTypeLabelsConfiguration($configuration);
            }
        );
    }
}
