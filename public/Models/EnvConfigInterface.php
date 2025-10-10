<?php

namespace Model;

interface EnvConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;
}