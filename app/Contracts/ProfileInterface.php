<?php

namespace App\Contracts;

interface ProfileInterface
{
    public function getTypeProfile(): string;
    public function getValidationRules(): array;
    public function getPermissions(): array;
}