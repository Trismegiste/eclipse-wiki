<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

use App\Entity\Vertex;

/**
 * Algebraic vertex, decoupled from Eclipse Wiki domain
 */
class GraphVertex
{

    public string $pk;
    public string $title;
    public string $category;
    public int $distance;

    public function __construct(array $vertex)
    {
        $this->pk = $vertex['_id'];
        $this->title = $vertex['title'];
        $this->category = Vertex::getCategoryForVertex($vertex['__pclass']);
    }

}
