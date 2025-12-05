<?php
namespace JvMTECH\SelectiveMixins\Tests\Functional\Service;

use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Core\NodeType\NodeTypeManager;
use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Neos\Service\NodeTypeSchemaBuilder;
use Neos\Flow\Configuration\ConfigurationManager;

/**
 * Testcase for the Selective NodeType generation
 */
class NodeTypeTest extends FunctionalTestCase
{
    /**
     * @var NodeTypeSchemaBuilder
     */
    protected $nodeTypeSchemaBuilder;

    /**
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    public function setUp(): void
    {
        parent::setUp();
        $contentRepositoryRegistry = $this->objectManager->get(ContentRepositoryRegistry::class);
        $this->nodeTypeManager = $contentRepositoryRegistry->get(ContentRepositoryId::fromString('default'))->getNodeTypeManager();
        $this->configurationManager = $this->objectManager->get(ConfigurationManager::class);
    }

    /**
     * @test
     */
    public function getContentA()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.A')->getFullConfiguration();

        self::assertEquals('Component A Group', $nodeTypeResult['ui']['inspector']['groups']['oneComponentGroup']['label']);
        self::assertEquals('Component A Group', $nodeTypeResult['ui']['inspector']['groups']['twoComponentGroup']['label']);

        self::assertEquals('Component A Text', $nodeTypeResult['properties']['oneText']['ui']['label']);
        self::assertEquals('oneComponentGroup', $nodeTypeResult['properties']['oneText']['ui']['inspector']['group']);

        self::assertEquals('Component A Another Text', $nodeTypeResult['properties']['oneAnotherText']['ui']['label']);
        self::assertEquals('oneComponentGroup', $nodeTypeResult['properties']['oneAnotherText']['ui']['inspector']['group']);

        self::assertEquals('Component A Text', $nodeTypeResult['properties']['twoText']['ui']['label']);
        self::assertEquals('twoComponentGroup', $nodeTypeResult['properties']['twoText']['ui']['inspector']['group']);

        self::assertArrayNotHasKey('twoAnotherText', $nodeTypeResult['properties']);
    }

    /**
     * @test
     */
    public function getContentB()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.B')->getFullConfiguration();

        self::assertEquals('Renamed Component A Group One', $nodeTypeResult['ui']['inspector']['groups']['oneComponentGroup']['label']);
        self::assertEquals('Component A Group', $nodeTypeResult['ui']['inspector']['groups']['twoComponentGroup']['label']);

        self::assertEquals('Component A Text', $nodeTypeResult['properties']['oneText']['ui']['label']);
        self::assertEquals('oneComponentGroup', $nodeTypeResult['properties']['oneText']['ui']['inspector']['group']);

        self::assertEquals('Component A Another Text', $nodeTypeResult['properties']['oneAnotherText']['ui']['label']);
        self::assertEquals('oneComponentGroup', $nodeTypeResult['properties']['oneAnotherText']['ui']['inspector']['group']);

        self::assertEquals('Renamed Component A Text Two', $nodeTypeResult['properties']['twoText']['ui']['label']);
        self::assertEquals('twoComponentGroup', $nodeTypeResult['properties']['twoText']['ui']['inspector']['group']);

        self::assertArrayNotHasKey('twoAnotherText', $nodeTypeResult['properties']);
    }

    /**
     * @test
     */
    public function getContentC()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.C')->getFullConfiguration();

        self::assertEquals('New Group A', $nodeTypeResult['ui']['inspector']['groups']['newGroupA']['label']);
        self::assertEquals('New Group B', $nodeTypeResult['ui']['inspector']['groups']['newGroupB']['label']);

        self::assertEquals('Component A Text', $nodeTypeResult['properties']['oneText']['ui']['label']);
        self::assertEquals('newGroupA', $nodeTypeResult['properties']['oneText']['ui']['inspector']['group']);

        self::assertEquals('Component A Another Text', $nodeTypeResult['properties']['oneAnotherText']['ui']['label']);
        self::assertEquals('newGroupB', $nodeTypeResult['properties']['oneAnotherText']['ui']['inspector']['group']);

        self::assertEquals('Component A Text', $nodeTypeResult['properties']['twoText']['ui']['label']);
        self::assertEquals('newGroupA', $nodeTypeResult['properties']['twoText']['ui']['inspector']['group']);

        self::assertArrayNotHasKey('twoAnotherText', $nodeTypeResult['properties']);
    }

    /**
     * @test
     */
    public function getContentD()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.D')->getFullConfiguration();

        self::assertEquals('Component A Text One', $nodeTypeResult['properties']['oneText']['ui']['label']);
        self::assertEquals('Component A Another Text One', $nodeTypeResult['properties']['oneAnotherText']['ui']['label']);

        self::assertEquals('Component A Text Two', $nodeTypeResult['properties']['twoText']['ui']['label']);
        self::assertEquals('Component A Another Text Two', $nodeTypeResult['properties']['twoAnotherText']['ui']['label']);
    }

    /**
     * @test
     */
    public function getContentE()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.E')->getFullConfiguration();

        self::assertEquals('Component A Group', $nodeTypeResult['ui']['inspector']['groups']['testFirstComponentGroup']['label']);

        self::assertEquals('Component A Text', $nodeTypeResult['properties']['testFirstText']['ui']['label']);
        self::assertEquals('testFirstComponentGroup', $nodeTypeResult['properties']['testFirstText']['ui']['inspector']['group']);

        self::assertEquals('Component A Another Text', $nodeTypeResult['properties']['testFirstAnotherText']['ui']['label']);
        self::assertEquals('testFirstComponentGroup', $nodeTypeResult['properties']['testFirstAnotherText']['ui']['inspector']['group']);

        self::assertEquals('Component B Group', $nodeTypeResult['ui']['inspector']['groups']['testSecondComponentGroupB']['label']);

        self::assertEquals('Component B Text', $nodeTypeResult['properties']['testSecondText']['ui']['label']);
        self::assertEquals('testSecondComponentGroupB', $nodeTypeResult['properties']['testSecondText']['ui']['inspector']['group']);

        self::assertEquals('Component B Another Text', $nodeTypeResult['properties']['testSecondAnotherText']['ui']['label']);
        self::assertEquals('testSecondComponentGroupB', $nodeTypeResult['properties']['testSecondAnotherText']['ui']['inspector']['group']);
    }

    /**
     * @test
     */
    public function contentWithReferencesHasNamespacedReferences()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.WithReferences')->getFullConfiguration();

        // Check that references are namespaced correctly
        self::assertArrayHasKey('primaryRelatedNodes', $nodeTypeResult['references']);
        self::assertArrayHasKey('primaryTags', $nodeTypeResult['references']);
        self::assertArrayHasKey('secondaryRelatedNodes', $nodeTypeResult['references']);

        // Check properties are also namespaced
        self::assertArrayHasKey('primaryTitle', $nodeTypeResult['properties']);

        // Check that non-selected references are not present
        self::assertArrayNotHasKey('secondaryTags', $nodeTypeResult['references']);
    }

    /**
     * @test
     */
    public function contentWithReferencesHasCorrectGroupsAndLabels()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.WithReferences')->getFullConfiguration();

        // Check groups are namespaced
        self::assertEquals('Reference Group', $nodeTypeResult['ui']['inspector']['groups']['primaryReferenceGroup']['label']);
        self::assertEquals('Reference Group', $nodeTypeResult['ui']['inspector']['groups']['secondaryReferenceGroup']['label']);

        // Check reference labels
        self::assertEquals('Related Nodes', $nodeTypeResult['references']['primaryRelatedNodes']['ui']['label']);
        self::assertEquals('Tags', $nodeTypeResult['references']['primaryTags']['ui']['label']);
        self::assertEquals('Related Nodes', $nodeTypeResult['references']['secondaryRelatedNodes']['ui']['label']);

        // Check reference groups
        self::assertEquals('primaryReferenceGroup', $nodeTypeResult['references']['primaryRelatedNodes']['ui']['inspector']['group']);
        self::assertEquals('primaryReferenceGroup', $nodeTypeResult['references']['primaryTags']['ui']['inspector']['group']);
        self::assertEquals('secondaryReferenceGroup', $nodeTypeResult['references']['secondaryRelatedNodes']['ui']['inspector']['group']);

        // Check property labels and groups
        self::assertEquals('Title', $nodeTypeResult['properties']['primaryTitle']['ui']['label']);
        self::assertEquals('primaryReferenceGroup', $nodeTypeResult['properties']['primaryTitle']['ui']['inspector']['group']);
    }

    /**
     * @test
     */
    public function contentWithReferencesRenamedHasExtendedLabels()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.WithReferencesRenamed')->getFullConfiguration();

        // Check that group labels are extended
        // 'main: "Main %s"' extends the group label
        self::assertEquals('Main Reference Group', $nodeTypeResult['ui']['inspector']['groups']['mainReferenceGroup']['label']);
        self::assertEquals('Reference Group', $nodeTypeResult['ui']['inspector']['groups']['additionalReferenceGroup']['label']);

        // Check property label - stays unchanged when using namespace string pattern (only group is extended)
        self::assertEquals('Title', $nodeTypeResult['properties']['mainTitle']['ui']['label']);

        // Check reference labels with wildcard pattern - these ARE extended because of '*': 'Additional %s'
        self::assertEquals('Additional Related Nodes', $nodeTypeResult['references']['additionalRelatedNodes']['ui']['label']);
        self::assertEquals('Additional Tags', $nodeTypeResult['references']['additionalTags']['ui']['label']);
    }

    /**
     * @test
     */
    public function contentMixedPropsAndRefsHasBothNamespaced()
    {
        $nodeTypeResult = $this->nodeTypeManager->getNodeType('Vendor:Content.MixedPropsAndRefs')->getFullConfiguration();

        // Check that both properties and references are present and namespaced
        self::assertArrayHasKey('contentHeading', $nodeTypeResult['properties']);
        self::assertArrayHasKey('contentItems', $nodeTypeResult['references']);

        // Check they share the same namespaced group
        self::assertEquals('contentMixedGroup', $nodeTypeResult['properties']['contentHeading']['ui']['inspector']['group']);
        self::assertEquals('contentMixedGroup', $nodeTypeResult['references']['contentItems']['ui']['inspector']['group']);

        // Check labels are preserved
        self::assertEquals('Heading', $nodeTypeResult['properties']['contentHeading']['ui']['label']);
        self::assertEquals('Items', $nodeTypeResult['references']['contentItems']['ui']['label']);
    }

}
