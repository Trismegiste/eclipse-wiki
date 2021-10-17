<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Edge;
use App\Entity\Indexable;
use App\Entity\MediaWikiPage;

/**
 * Provider for Edges
 */
class EdgeProvider extends MongoDbProvider
{

    protected function createFromPage(MediaWikiPage $page): Indexable
    {
        $param = $this->getNamedParametersFromTemplate('SaWoAtout', $page->content, ['ego' => 0, 'bio' => 0, 'synth' => 0]);
        $req = $this->getOrderedParametersFromTemplate('PrérequisAtout', $page->content);

        return new Edge($page->getTitle(), $param['rang'], $param['type'], $param['ego'] == 1, $param['bio'] == 1, $param['synth'] == 1, $req[0]);
    }

    protected function getCategory(): string
    {
        return 'Atout';
    }

}
