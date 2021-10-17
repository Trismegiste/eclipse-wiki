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
        $match = [];
        $param['core'] = 0; // default
        preg_match('#\{\{SaWoCompétence\|([^\}]+)\}\}#', $page->content, $match);
        $paramStr = explode('|', $match[1]);
        foreach ($paramStr as $assoc) {
            preg_match('#([^=]+)=([^=]+)#', $assoc, $kv);
            $param[$kv[1]] = $kv[2];
        }

        return new Skill($page->getTitle(), $param['attr'], $param['core']);
    }

    protected function getCategory(): string
    {
        return 'Compétence';
    }

}
