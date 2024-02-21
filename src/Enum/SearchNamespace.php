<?php

/*
 * eclipse-wiki
 */

namespace App\Enum;

/**
 * In which business (=local) namespace a search is executed
 */
enum SearchNamespace: string
{

    case Pages = 'page';
    case Images = 'picture';

    public function getTemplateName(): string {
        return 'search_' . $this->value . '.html.twig';
    }

}
