<?php
namespace JvMTECH\SelectiveMixins\Eel\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;

class ArrayHelper implements ProtectedContextAwareInterface
{
    /**
     * @param array $array
     * @return string
     */
    public function toCamelCase(array $array)
    {
        $array = array_map(function ($item) {
            return ucfirst($item);
        }, $array);

        return lcfirst(implode('', $array));
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
