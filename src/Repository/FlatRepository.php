<?php

/*
 * Vesta
 */

namespace App\Repository;

/**
 * Repository for flat full text choices
 */
interface FlatRepository
{

    public function findAll(string $key): array;
}
