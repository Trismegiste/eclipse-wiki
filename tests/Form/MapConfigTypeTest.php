<?php

/*
 * eclipse-wiki
 */

use App\Entity\MapConfig;
use App\Form\MapConfigType;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Form;

class MapConfigTypeTest extends KernelTestCase
{

    protected Form $sut;
    protected VertexRepository $repo;

    protected function setUp(): void
    {
        $factory = static::getContainer()->get('form.factory');
        $this->sut = $factory->create(MapConfigType::class);
        $this->repo = static::getContainer()->get(VertexRepository::class);
    }

    public function testClean()
    {
        $this->repo->delete(iterator_to_array($this->repo->search()));
        $this->assertCount(0, iterator_to_array($this->repo->search()));
    }

    public function getIncompleteData(): array
    {
        return [
            [['side' => 25]]
        ];
    }

    public function getMinimalData(): array
    {
        return [[
        [
            'side' => 25,
            'seed' => 123,
            'horizontalLines' => 0,
            'verticalLines' => 0,
            'avgTilePerRoom' => 12
        ]
        ]];
    }

    /** @dataProvider getIncompleteData */
    public function testIncomplete(array $inputData)
    {
        $this->sut->submit($inputData);
        $this->assertTrue($this->sut->isSynchronized());
        $checkError = $this->sut->getErrors(true, true);
        $this->assertCount(4, $checkError, (string) $checkError);
    }

    /** @dataProvider getMinimalData */
    public function testMinimal(array $inputData)
    {
        $this->sut->submit($inputData);
        $this->assertTrue($this->sut->isSynchronized());
        $checkError = $this->sut->getErrors(true, true);
        $this->assertCount(0, $checkError, (string) $checkError);
        $this->assertTrue($this->sut->isValid());

        $model = $this->sut->getData();
        $this->assertInstanceOf(MapConfig::class, $model);
        $this->assertEquals(25, $model->side);
    }

    /** @dataProvider getMinimalData */
    public function testErodingRooms(array $inputData)
    {
        $inputData['erosionForHallway'] = true;
        $this->sut->submit($inputData);
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertFalse($this->sut->isValid());
        $checkError = $this->sut->getErrors(true, true);
        $this->assertCount(2, $checkError);
        $this->assertStringContainsString('erosion', $checkError[0]->getMessage());
        $this->assertStringContainsString('erosion', $checkError[1]->getMessage());
    }

}
