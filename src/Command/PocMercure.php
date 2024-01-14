<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsCommand(name: "mercure:publish")]
class PocMercure extends Command
{

    public function __construct(protected HubInterface $hub)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $update = new Update(
                'public',
                '<img src="data:image/png;base64,' . base64_encode(file_get_contents('/app/public/img/mire.png')) . '"/>',
                type: 'profile'
        );

        $this->hub->publish($update);

        return self::SUCCESS;
    }

}
