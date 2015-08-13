<?php

namespace PersonBundle;


abstract class Entity
{
    /**
     * @return string
     */
    abstract function getOrientClass();
}