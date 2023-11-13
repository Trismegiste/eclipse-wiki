<?php

/*
 * eclipse-wiki
 */

use App\Entity\CreationTree\Graph;
use App\Entity\CreationTree\Node;
use PHPUnit\Framework\TestCase;

class GraphTest extends TestCase
{

    protected Graph $sut;

    protected function setUp(): void
    {
        $this->sut = new Graph();
    }

    public function testNodeAccess()
    {
        $this->sut->node[] = new Node('yolo');
        $this->assertInstanceOf(Node::class, $this->sut->getNodeByName('yolo'));
    }

    public function testInvalidNodeName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sut->getNodeByName('fubar');
    }

    public function testJson()
    {
        $this->sut->node[] = new Node('root');

        $whatJsWillSee = json_decode(json_encode($this->sut), true);
        $this->assertCount(1, $whatJsWillSee);
        $this->assertArrayHasKey('name', $whatJsWillSee[0]);
        $this->assertArrayHasKey('children', $whatJsWillSee[0]);
        $this->assertEquals('root', $whatJsWillSee[0]['name']);
        $this->assertCount(0, $whatJsWillSee[0]['children']);
    }

    public function testGetParent()
    {
        $p1 = new Node('Anakin');
        $p1->children[] = 'Luke';
        $p2 = new Node('Padme');
        $p2->children[] = 'Luke';

        $child = new Node('Luke');

        $this->sut->node[] = $p1;
        $this->sut->node[] = $p2;
        $this->sut->node[] = $child;

        $found = $this->sut->getParentNode($child);
        $this->assertCount(2, $found);
        $this->assertInstanceOf(Node::class, $found[0]);
        $this->assertInstanceOf(Node::class, $found[1]);
    }

    public function testDistanceToSelf()
    {
        $p1 = new Node('Anakin');
        $this->sut->node[] = $p1;

        $this->assertEquals(0, $this->sut->getShortestDistanceFromAncestor($p1, $p1));
    }

    public function testDistanceToParent()
    {
        $p1 = new Node('Anakin');
        $p1->children[] = 'Luke';
        $child = new Node('Luke');
        $this->sut->node[] = $p1;
        $this->sut->node[] = $child;

        $this->assertEquals(1, $this->sut->getShortestDistanceFromAncestor($child, $p1));
    }

    public function testShortestDistance()
    {
        $this->fillGraphWithLinearTree();
        $root = $this->sut->node[0];
        $leaf = $this->sut->node[3];

        $this->assertEquals(3, $this->sut->getShortestDistanceFromAncestor($leaf, $root));

        $this->sut->node[1]->children[] = 'leaf';  // a leaf could spawn from the trunk
        $this->assertNotEquals(3, $this->sut->getShortestDistanceFromAncestor($leaf, $root));
        $this->assertEquals(2, $this->sut->getShortestDistanceFromAncestor($leaf, $root));
    }

    protected function fillGraphWithLinearTree()
    {
        $this->sut->node[0] = $root = new Node('root');
        $this->sut->node[1] = new Node('trunk');
        $this->sut->node[2] = new Node('branch');
        $this->sut->node[3] = $leaf = new Node('leaf');
        $this->sut->node[0]->children[] = 'trunk';
        $this->sut->node[1]->children[] = 'branch';
        $this->sut->node[2]->children[] = 'leaf';
    }

    public function testAccumulateKeywordsWithDepth()
    {
        $this->fillGraphWithLinearTree();
        $root = $this->sut->node[0];
        $this->sut->node[0]->text2img = ['cyberpunk'];
        $this->sut->node[1]->text2img = ['male'];
        $this->sut->node[2]->text2img = ['fashion'];
        $this->sut->node[3]->text2img = ['model'];

        $this->assertEquals([['cyberpunk'], ['male'], ['fashion'], ['model']], $this->sut->accumulatePromptKeywordPerDistance($root));
    }

    public function testAccumulateKeywordsMerge()
    {
        $this->sut->node[0] = $root = new Node('root');
        $root->children = ['child1', 'child2'];

        $this->sut->node[1] = new Node('child1');
        $this->sut->node[2] = new Node('child2');
        $this->sut->node[1]->text2img = ['male', 'model'];
        $this->sut->node[2]->text2img = ['model', 'fashion'];

        $dump = $this->sut->accumulatePromptKeywordPerDistance($root);
        $this->assertCount(0, $dump[0]);
        $this->assertCount(3, $dump[1]);
        $this->assertContains('male', $dump[1]);
        $this->assertContains('fashion', $dump[1]);
        $this->assertContains('model', $dump[1]);
    }

}
