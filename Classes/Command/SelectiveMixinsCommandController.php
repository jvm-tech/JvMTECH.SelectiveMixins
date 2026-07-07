<?php

namespace JvMTECH\SelectiveMixins\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;

class SelectiveMixinsCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * Show the full configuration of a NodeType.
     */
    public function nodeTypeFullConfigurationCommand(string $nodeTypeName): void
    {
        $nodeType = $this->nodeTypeManager->getNodeType($nodeTypeName);
        if (!$nodeType) {
            $this->outputLine('NodeType not found: ' . $nodeTypeName);
            return;
        }
        $this->outputLine(json_encode($nodeType->getFullConfiguration(), JSON_PRETTY_PRINT));
    }
}
