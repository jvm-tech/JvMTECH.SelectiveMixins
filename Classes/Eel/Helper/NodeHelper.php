<?php
namespace JvMTECH\SelectiveMixins\Eel\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Eel\ProtectedContextAwareInterface;

class NodeHelper implements ProtectedContextAwareInterface
{
    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    public function hasProperty(Node $node, string $propertyName): bool
    {
        $nodeTypeManager = $this->contentRepositoryRegistry->get($node->contentRepositoryId)->getNodeTypeManager();
        return $nodeTypeManager->getNodeType($node->nodeTypeName)->hasProperty($propertyName);
    }
    public function hasReference(Node $node, string $referenceName): bool
    {
        $nodeTypeManager = $this->contentRepositoryRegistry->get($node->contentRepositoryId)->getNodeTypeManager();
        return $nodeTypeManager->getNodeType($node->nodeTypeName)->hasReference($referenceName);
    }

    public function hasPropertyOrReference(Node $node, string $name): bool
    {
        return $this->hasProperty($node, $name) || $this->hasReference($node, $name);
    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
