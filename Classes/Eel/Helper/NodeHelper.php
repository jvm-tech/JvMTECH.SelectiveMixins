<?php
namespace JvMTECH\SelectiveMixins\Eel\Helper;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\Factory\NodeTypeManager\NodeTypeManagerFactoryInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;

class NodeHelper implements ProtectedContextAwareInterface
{
    #[Flow\Inject]
    protected NodeTypeManagerFactoryInterface $nodeTypeManagerFactory;


    public function hasProperty(Node $node, string $propertyName): bool
    {
        $nodeTypeManager = $this->nodeTypeManagerFactory->build($node->contentRepositoryId, []);
        $nodePropertyNames = $nodeTypeManager->getNodeType($node->nodeTypeName)->getProperties();
        return array_key_exists($propertyName, $nodePropertyNames);
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
