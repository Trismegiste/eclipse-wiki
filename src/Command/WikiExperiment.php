<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Service\MediaWiki;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mediawiki:exp')]
class WikiExperiment extends Command
{

    protected $mediaWiki;

    public function __construct(MediaWiki $mw)
    {
        $this->mediaWiki = $mw;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Parsed tree experiments');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dump = $this->mediaWiki->getTreeAndHtmlByName('Furie');

        $doc = new \DOMDocument();
        $doc->loadXML($dump['tree']);

        $bonusList = [];

        $skillBonus = new \DOMXPath($doc);
        $iter = $skillBonus->query('//h[@level=2][contains(text(), "Avantage")]/following-sibling::template/title[normalize-space()="RaceBonusCompétence"]/parent::template');
        $templateParam = new \DOMXPath($doc);
        foreach ($iter as $bonus) {
            $paramIter = $templateParam->query('part/name[@index="2"]/following-sibling::value', $bonus);
            $skill = $paramIter->item(0)->nodeValue;
            $paramIter = $templateParam->query('part/name[@index="1"]/following-sibling::value', $bonus);
            $bonus = $paramIter->item(0)->nodeValue;
            $bonusList[$skill] = new \App\Entity\TraitBonus($bonus);
        }

        var_dump($bonusList);

        return Command::SUCCESS;
    }

}
