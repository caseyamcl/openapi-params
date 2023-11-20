<?php

namespace OpenApiParams\Contract;

interface ParameterInterface
{
    public function getName(): string;

    public function getTypeName(): string;

    public function isRequired(): bool;
}
