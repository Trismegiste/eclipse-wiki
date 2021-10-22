<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Hindrance;
use App\Entity\Indexable;
use App\Entity\MediaWikiPage;

/**
 * Provider for Hindrances
 */
class HindranceProvider extends MongoDbProvider
{

    const paramType = [1 => 'm', 2 => 'M', 3 => 'M/m'];

    protected function createFromPage(MediaWikiPage $page): Indexable
    {
        $param = $this->getNamedParametersFromTemplate('SaWoHandicap', $page->content, ['ego' => 0, 'bio' => 0, 'synth' => 0]);

        return new Hindrance($page->getTitle(), $param['ego'] == 1, $param['bio'] == 1, $param['synth'] == 1, array_search($param['type'], self::paramType));
    }

    protected function getCategory(): string
    {
        return 'Handicap';
    }

}
