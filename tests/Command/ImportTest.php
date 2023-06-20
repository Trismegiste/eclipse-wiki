<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportTest extends KernelTestCase
{

    public function getExportZip()
    {
        $filename = 'export-tmp.zip';
        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $timeline = new App\Entity\Timeline('scenar');
        $timeline->elevatorPitch = 'For export';
        $timeline->setTree(new App\Entity\PlotNode('scene 1'));
        $export = \MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP([$timeline]));
        $zip->addFromString(App\Command\Export::vertexFilename, $export);

        $img = imagecreatetruecolor(256, 256);
        $tmpfname = tempnam(__DIR__, 'export');
        imagejpeg($img, $tmpfname);
        $zip->addFile($tmpfname, 'img.jpg');

        $zip->close();
        unlink($tmpfname);

        return [[$filename]];
    }

    /** @dataProvider getExportZip */
    public function testExecute(string $archive)
    {
        $this->assertFileExists($archive);
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('db:import');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['source' => $archive]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Import succeed', $output);

        unlink($archive);
    }

}
