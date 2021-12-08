<?php

/*
 * Vesta
 */

namespace App\Repository;

use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * A repository for flat list stored in yml file
 */
class YamlRepository implements FlatRepository
{

    protected $data = null;
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function findAll(string $key): array
    {
        if (is_null($this->data)) {
            $this->data = Yaml::parseFile($this->path);
        }

        if (!array_key_exists($key, $this->data)) {
            throw new InvalidArgumentException("No data for the key '$key'");
        }
        $tmp = $this->data[$key];

        return array_combine($tmp, $tmp);
    }

}
