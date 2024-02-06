<?php

namespace NW\WebService\References\Operations\Notification;

abstract class ReferencesOperation
{
    abstract public function doOperation(): array;

    public function getRequest(string $pName)
    {
        return $_REQUEST[$pName];
    }
}