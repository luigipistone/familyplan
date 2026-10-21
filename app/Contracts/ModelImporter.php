<?php

namespace App\Contracts;

interface ModelImporter
{
    public function import(string $url): array;
}
