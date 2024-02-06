<?php

namespace NW\WebService\References\Operations\Notification;

class ErrorCodes
{
    const CLIENT_NOT_FOUND = 'сlient not found!';
    const SELLER_NOT_FOUND = 'Seller not found!';
    const CREATOR_NOT_FOUND = 'Creator not found!';
    const EMPTY_NOTIFICATIONTYPE = 'Empty notificationType';
    const EXPERT_NOT_FOUND = 'Expert not found!';

    public static function throwError400(string $text): void
    {
        throw new \Exception($text, 400);
    }

    public static function throwError(string $text, int $code): void
    {
        throw new \Exception($text, $code);
    }
}