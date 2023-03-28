<?php
namespace JvMTECH\SelectiveMixins\Eel\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\ProtectedContextAwareInterface;

/**
 * @Flow\Proxy(false)
 */
class NodeHelper implements ProtectedContextAwareInterface
{
    /**
     * @param NodeInterface $node
     * @param string $propertyName
     * @return bool
     */
    public function hasProperty(NodeInterface $node, string $propertyName): bool
    {
        $nodePropertyNames = array_keys($node->getNodeType()->getProperties());
        return in_array($propertyName, $nodePropertyNames);
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
