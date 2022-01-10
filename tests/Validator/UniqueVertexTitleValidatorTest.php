<?php

/*
 * eclipse-wiki
 */

use App\Repository\VertexRepository;
use App\Validator\UniqueVertexTitle;
use App\Validator\UniqueVertexTitleValidator;
use PHPUnit\Framework\TestCase;

class UniqueVertexTitleValidatorTest extends TestCase
{

    protected $sut;
    protected $repository;
    protected $constraint;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VertexRepository::class);
        $this->sut = new UniqueVertexTitleValidator($this->repository);
        $this->constraint = new UniqueVertexTitle();
    }

    public function testNull()
    {
        $this->repository->expects($this->never())
                ->method('findByTitle');

        $this->assertNull($this->sut->validate(null, $this->constraint));
    }

    public function testBadConstraint()
    {
        $this->repository->expects($this->never())
                ->method('findByTitle');
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->sut->validate(null, new Symfony\Component\Validator\Constraints\NotBlank());
    }

    public function testBadType()
    {
        $this->repository->expects($this->never())
                ->method('findByTitle');
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedValueException::class);
        $this->sut->validate(new DateTime(), $this->constraint);
    }

}
