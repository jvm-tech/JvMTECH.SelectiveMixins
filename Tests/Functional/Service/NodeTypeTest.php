<?php
namespace JvMTECH\SelectiveMixins\Tests\Functional\Service;

use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Neos\Service\NodeTypeSchemaBuilder;

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
     * @var YamlSource
     */
    protected $yamlSource;

    public function setUp(): void
    {
        parent::setUp();
        $this->nodeTypeManager = $this->objectManager->get(NodeTypeManager::class);
        $this->yamlSource = $this->objectManager->get(YamlSource::class);
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

}
