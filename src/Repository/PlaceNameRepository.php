<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use Symfony\Component\Yaml\Yaml;

/**
 * Repository for Place names
 */
class PlaceNameRepository extends YamlRepository
{

    public function getCategory(): array
    {
        if (is_null($this->data)) {
            $this->data = Yaml::parseFile($this->path);
        }

        $tmp = array_keys($this->data);

        return array_combine($tmp, $tmp);
    }

    public function getRandomName(string $key): string
    {
        $choices = array_values($this->findAll($key));

        return $choices[random_int(0, count($choices) - 1)];
    }

}
