<?php

namespace JvMTECH\SelectiveMixins\Command;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Cli\CommandController;

class SelectiveMixinsCommandController extends CommandController
{

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    public function nodeTypeFullConfigurationCommand(string $nodeTypeName, string $contentRepositoryId = 'default'): void
    {
        $nodeType = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString($contentRepositoryId))->getNodeTypeManager()->getNodeType($nodeTypeName);
        if (!$nodeType) {
            $this->outputLine('NodeType not found: ' . $nodeTypeName);
            return;
        }
        $this->outputLine(json_encode($nodeType->getFullConfiguration(), JSON_PRETTY_PRINT));
    }
}
