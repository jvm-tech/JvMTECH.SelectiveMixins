<?php
namespace JvMTECH\SelectiveMixins\NodeTypePostprocessor;

use Neos\ContentRepository\Core\NodeType\NodeType;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Core\NodeType\NodeTypePostprocessorInterface;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Utility\Arrays;

/**
 * Apply namespaced mixins
 */
class NamespacedMixinsPostprocessor implements NodeTypePostprocessorInterface
{
    #[Flow\Inject]
    protected ConfigurationManager $configurationManager;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    protected ?array $completeNodeTypeConfiguration;

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
        $originalReferences = $configuration['references'] ?? [];
        $namespacedProperties = [];
        $namespacedReferences = [];
        $propertyMapping = [];

        $nodeTypeManager = $this->contentRepositoryRegistry->get(ContentRepositoryId::fromString('default'))->getNodeTypeManager();

        foreach ($configuration['options']['superTypes'] as $mixinNodeType => $mixinOptions) {

            $mixinFullConfiguration = $nodeTypeManager->getNodeType($mixinNodeType)?->getFullConfiguration();

            if ($mixinOptions === false) {
                continue;
            }
            if ($mixinOptions === true) {
                $mixinOptions = ['' => true];
            }

            $mixinGroups = $mixinFullConfiguration['ui']['inspector']['groups'] ?? [];

            foreach ($mixinOptions as $mixinNamespace => $mixinPropertiesOrStatus) {
                // Process properties
                if (isset($mixinFullConfiguration['properties'])) {
                    $processedProperties = $this->processConfigurationItems(
                        $mixinFullConfiguration['properties'],
                        $mixinNamespace,
                        $mixinPropertiesOrStatus,
                        $mixinGroups,
                        $configuration,
                        $namespacedGroups,
                        $propertyMapping
                    );
                    $namespacedProperties = array_merge($namespacedProperties, $processedProperties);
                }

                // Process references (Neos v9)
                if (isset($mixinFullConfiguration['references'])) {
                    $processedReferences = $this->processConfigurationItems(
                        $mixinFullConfiguration['references'],
                        $mixinNamespace,
                        $mixinPropertiesOrStatus,
                        $mixinGroups,
                        $configuration,
                        $namespacedGroups,
                        $propertyMapping
                    );
                    $namespacedReferences = array_merge($namespacedReferences, $processedReferences);
                }
            }
        }

        $this->updatePropertyReferences($namespacedProperties, $propertyMapping);
        $this->updatePropertyReferences($namespacedReferences, $propertyMapping);

        $configuration['ui']['inspector']['groups'] = Arrays::arrayMergeRecursiveOverrule(
            $namespacedGroups,
            $originalGroups
        );

        $configuration['properties'] = Arrays::arrayMergeRecursiveOverrule(
            $namespacedProperties,
            $originalProperties
        );

        $configuration['references'] = Arrays::arrayMergeRecursiveOverrule(
            $namespacedReferences,
            $originalReferences
        );

    }

    /**
     * Determine the namespaced group name for a property
     */
    private function getGroup(string $mixinNamespace, string $group, string $propertyName, array $configuration): string
    {
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
    }

    /**
     * Process configuration items (properties or references) and apply namespacing
     */
    private function processConfigurationItems(
        array $items,
        string $mixinNamespace,
        $mixinPropertiesOrStatus,
        array $mixinGroups,
        array $configuration,
        array &$namespacedGroups,
        array &$propertyMapping
    ): array {
        $namespacedItems = [];

        foreach ($items as $itemName => $item) {
            $namespacedItemName = $mixinNamespace ? (lcfirst($mixinNamespace) . ucfirst($itemName)) : $itemName;

            if ($mixinPropertiesOrStatus === true || is_string($mixinPropertiesOrStatus) || (is_array($mixinPropertiesOrStatus) && (isset($mixinPropertiesOrStatus['*']) || in_array($itemName, array_keys($mixinPropertiesOrStatus))))) {
                if (isset($item['ui']['inspector']['group'])) {
                    $group = $item['ui']['inspector']['group'];
                    $namespacedGroup = $this->getGroup($mixinNamespace, $group, $itemName, $configuration);
                    $item['ui']['inspector']['group'] = $namespacedGroup;
                    $namespacedGroups[$namespacedGroup] = $mixinGroups[$group];
                }

                if (is_string($mixinPropertiesOrStatus) && strpos($mixinPropertiesOrStatus, '%s') !== false && isset($item['ui']['label'])) {
                    if (isset($namespacedGroup) && isset($group) && isset($namespacedGroups[$namespacedGroup]['label'])) {
                        $namespacedGroups[$namespacedGroup]['label'] = str_replace('%s', $namespacedGroups[$namespacedGroup]['label'], $mixinPropertiesOrStatus);
                    }
                } else if (is_array($mixinPropertiesOrStatus) && isset($mixinPropertiesOrStatus['*']) && is_string($mixinPropertiesOrStatus['*'])) {
                    if (strpos($mixinPropertiesOrStatus['*'], '%s') !== false) {
                        $item['ui']['label'] = str_replace('%s', $item['ui']['label'], $mixinPropertiesOrStatus['*']);
                    } else {
                        $item['ui']['label'] = $mixinPropertiesOrStatus['*'];
                    }
                } else if (is_array($mixinPropertiesOrStatus) && is_string($mixinPropertiesOrStatus[$itemName])) {
                    if (strpos($mixinPropertiesOrStatus[$itemName], '%s') !== false) {
                        $item['ui']['label'] = str_replace('%s', $item['ui']['label'], $mixinPropertiesOrStatus[$itemName]);
                    } else {
                        $item['ui']['label'] = $mixinPropertiesOrStatus[$itemName];
                    }
                }

                $namespacedItems[$namespacedItemName] = $item;
                $propertyMapping[$itemName] = $namespacedItemName;
            }
        }

        return $namespacedItems;
    }

    /**
     * Update property references in hidden and position configurations
     */
    private function updatePropertyReferences(array &$items, array $propertyMapping): void
    {
        foreach ($items as $itemName => &$item) {
            if (isset($item['ui']['inspector']['hidden'])) {
                foreach ($propertyMapping as $originalPropertyName => $newPropertyName) {
                    if (str_contains($item['ui']['inspector']['hidden'], $originalPropertyName)) {
                        $items[$itemName]['ui']['inspector']['hidden'] = preg_replace(
                            "/\b$originalPropertyName\b/",
                            $newPropertyName,
                            $item['ui']['inspector']['hidden']
                        );
                    }
                }
            }

            if (isset($item['ui']['inspector']['position'])) {
                foreach ($propertyMapping as $originalPropertyName => $newPropertyName) {
                    if (str_contains($item['ui']['inspector']['position'], $originalPropertyName)) {
                        $items[$itemName]['ui']['inspector']['position'] = preg_replace(
                            "/\b$originalPropertyName\b/",
                            $newPropertyName,
                            $item['ui']['inspector']['position']
                        );
                    }
                }
            }
        }
    }
}
