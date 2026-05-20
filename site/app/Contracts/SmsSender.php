<?php

namespace App\Contracts;

interface SmsSender
{
    /**
     * @param  string  $e164WithoutPlus  Например 375XXXXXXXXX
     */
    public function send(string $e164WithoutPlus, string $message): void;
}
