<?php

/*
 * eclipse-wiki
 */

use App\Tests\Controller\PictureFixture;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppendPictureUploadTest extends KernelTestCase
{

    use PictureFixture;

    protected Symfony\Component\Form\FormFactoryInterface $factory;

    public function getInvalidTitle()
    {
        return [
            ['Avec\\Backslash'],
            ['Avec_Underscore'],
            ['Avec[Bracket'],
            ['Avec]Bracket'],
            ['Avec|Pipe'],
            ['Avec/Slash']
        ];
    }

    protected function setUp(): void
    {
        $this->factory = static::getContainer()->get('form.factory');
    }

    /** @dataProvider getInvalidTitle */
    public function testInvalidCharacter(string $title)
    {
        $form = $this->factory->create(App\Form\AppendPictureUpload::class, null, ['ajax_search' => 'http://yolo']);
        $form->submit(['filename' => $title]);
        $this->assertFalse($form->isValid());
        foreach ($form->get('filename')->getErrors() as $error) {
            /** @var \Symfony\Component\Form\FormError $error */
            $this->assertEquals('This value is not valid.', $error->getMessageTemplate());
        }
    }

}
