<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use App\Entity\MediaWikiPage;
use App\Entity\Skill;

/**
 * Description of SkillProvider
 */
class SkillProvider extends MongoDbProvider
{

    protected function createFromPage(MediaWikiPage $page): Indexable
    {
        $param = $this->getParametersFromTemplate('SaWoCompétence', $page->content, ['core' => 0]);

        return new Skill($page->getTitle(), $param['attr'], $param['core']);
    }

    protected function getCategory(): string
    {
        return 'Compétence';
    }

}
