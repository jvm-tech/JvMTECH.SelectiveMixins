<?php
namespace JvMTECH\SelectiveMixins\NodeTypePostprocessor;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\ContentRepository\NodeTypePostprocessor\NodeTypePostprocessorInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Utility\Arrays;

/**
 * Apply namespaced mixins
 */
class NamespacedMixinsPostprocessor implements NodeTypePostprocessorInterface
{
    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array|null
     */
    protected $completeNodeTypeConfiguration;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    public function initializeObject() {
        $this->completeNodeTypeConfiguration = $this->configurationManager->getConfiguration('NodeTypes');
    }

    /**
     * @param NodeType $nodeType (uninitialized) The node type to process
     * @param array $configuration input configuration
     * @param array $options The processor options
     * @return void
     */
    public function process(NodeType $nodeType, array &$configuration, array $options): void
    {
        if (!isset($configuration['options']['superTypes'])) {
            return;
        }

        $originalGroups = $configuration['ui']['inspector']['groups'] ?? [];
        $namespacedGroups = [];
        $originalProperties = $configuration['properties'] ?? [];
        $namespacedProperties = [];
        $propertyMapping = [];

        $getGroup = function ($mixinNamespace, $group, $propertyName) use ($configuration) {
            if (isset($configuration['options']['mergeGroups'])) {
                $mergeGroups = $configuration['options']['mergeGroups'];
                foreach ($mergeGroups as $newGroupName => $mergeGroup) {
                    if (isset($mergeGroup[$mixinNamespace]) && $mergeGroup[$mixinNamespace] === true) {
                        return $newGroupName;
                    } else if (isset($mergeGroup[$mixinNamespace]) && isset($mergeGroup[$mixinNamespace][$group]) && $mergeGroup[$mixinNamespace][$group] === true) {
                        return $newGroupName;
                    } else if (isset($mergeGroup[$mixinNamespace]) && isset($mergeGroup[$mixinNamespace][$group]) && isset($mergeGroup[$mixinNamespace][$group][$propertyName]) && $mergeGroup[$mixinNamespace][$group][$propertyName] === true) {
                        return $newGroupName;
                    }
                }
            }

            return $mixinNamespace ? (lcfirst($mixinNamespace) . ucfirst($group)) : $group;
        };

        foreach ($configuration['options']['superTypes'] as $mixinNodeType => $mixinOptions) {
            $mixinFullConfiguration = $this->nodeTypeManager->getNodeType($mixinNodeType)->getFullConfiguration();

            if ($mixinOptions === false) {
                continue;
            }
            if ($mixinOptions === true) {
                $mixinOptions = ['' => true];
            }

            foreach ($mixinOptions as $mixinNamespace => $mixinPropertiesOrStatus) {
                foreach ($mixinFullConfiguration['properties'] as $propertyName => $property) {
                    $namespacedPropertyName = $mixinNamespace ? (lcfirst($mixinNamespace) . ucfirst($propertyName)) : $propertyName;
                    if ($mixinPropertiesOrStatus === true || is_string($mixinPropertiesOrStatus) || (is_array($mixinPropertiesOrStatus) && (isset($mixinPropertiesOrStatus['*']) || in_array($propertyName, array_keys($mixinPropertiesOrStatus))))) {
                        if (isset($property['ui']['inspector']['group'])) {
                            $group = $property['ui']['inspector']['group'];
                            $namespacedGroup = $getGroup($mixinNamespace, $group, $propertyName);
                            $property['ui']['inspector']['group'] = $namespacedGroup;
                            $namespacedGroups[$namespacedGroup] = $mixinFullConfiguration['ui']['inspector']['groups'][$group];
                        }

                        // options.superTypes.'Vendor:Props.Component'.namespace: 'Extend group %s labels of these props'
                        if (is_string($mixinPropertiesOrStatus) && strpos($mixinPropertiesOrStatus, '%s') !== false && isset($property['ui']['label'])) {
                            if (isset($namespacedGroup) && isset($group) && isset($namespacedGroups[$namespacedGroup]['label'])) {
                                $namespacedGroups[$namespacedGroup]['label'] = str_replace('%s', $namespacedGroups[$namespacedGroup]['label'], $mixinPropertiesOrStatus);
                            }

                        // options.superTypes.'Vendor:Props.Component'.namespace.'*': 'Extend props %s labels of this namespace'
                        } else if (is_array($mixinPropertiesOrStatus) && isset($mixinPropertiesOrStatus['*']) && is_string($mixinPropertiesOrStatus['*'])) {
                            if (strpos($mixinPropertiesOrStatus['*'], '%s') !== false) {
                                $property['ui']['label'] = str_replace('%s', $property['ui']['label'], $mixinPropertiesOrStatus['*']);
                            } else {
                                $property['ui']['label'] = $mixinPropertiesOrStatus['*'];
                            }

                        // options.superTypes.'Vendor:Props.Component'.namespace.propertyXY: 'Extend props %s label'
                        } else if (is_array($mixinPropertiesOrStatus) && is_string($mixinPropertiesOrStatus[$propertyName])) {
                            if (strpos($mixinPropertiesOrStatus[$propertyName], '%s') !== false) {
                                $property['ui']['label'] = str_replace('%s', $property['ui']['label'], $mixinPropertiesOrStatus[$propertyName]);
                            } else {
                                $property['ui']['label'] = $mixinPropertiesOrStatus[$propertyName];
                            }
                        }

                        $namespacedProperties[$namespacedPropertyName] = $property;
                        $propertyMapping[$propertyName] = $namespacedPropertyName;
                    }
                }
            }
        }

        foreach ($namespacedProperties as $namespacedPropertyName => $namespacedProperty) {
            if (isset($namespacedProperty['ui']['inspector']['hidden'])) {
                foreach ($propertyMapping as $originalPropertyName => $newPropertyName) {
                    if (str_contains($namespacedProperty['ui']['inspector']['hidden'], $originalPropertyName)) {
                        $namespacedProperties[$namespacedPropertyName]['ui']['inspector']['hidden'] = preg_replace(
                            "/\b$originalPropertyName\b/",
                            $newPropertyName,
                            $namespacedProperty['ui']['inspector']['hidden']
                        );
                    }
                }
            }

            if (isset($namespacedProperty['ui']['inspector']['position'])) {
                foreach ($propertyMapping as $originalPropertyName => $newPropertyName) {
                    if (str_contains($namespacedProperty['ui']['inspector']['position'], $originalPropertyName)) {
                        $namespacedProperties[$namespacedPropertyName]['ui']['inspector']['position'] = preg_replace(
                            "/\b$originalPropertyName\b/",
                            $newPropertyName,
                            $namespacedProperty['ui']['inspector']['position']
                        );
                    }
                }
            }
        }

        $configuration['ui']['inspector']['groups'] = Arrays::arrayMergeRecursiveOverrule(
            $namespacedGroups,
            $originalGroups
        );

        $configuration['properties'] = Arrays::arrayMergeRecursiveOverrule(
            $namespacedProperties,
            $originalProperties
        );

    }

}
