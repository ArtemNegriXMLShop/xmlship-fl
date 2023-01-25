<?php

namespace App\Foundation\Interfaces;

interface PresentationInterface
{
    public function beautify(array $data): array;
}