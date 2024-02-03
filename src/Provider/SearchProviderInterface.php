<?php

namespace App\Provider;

interface SearchProviderInterface
{
    public function searchAndCalculateScore(string $query): float;
}