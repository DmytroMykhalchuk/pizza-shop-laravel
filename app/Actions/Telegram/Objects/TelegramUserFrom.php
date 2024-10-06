<?php

namespace App\Actions\Telegram\Objects;

class TelegramUserFrom
{
    private ?string $chatId;
    private ?string $userId;
    private ?string $firstMame;
    private ?string $lastName;
    private ?string $userName;
    private ?string $locale;

    public function __construct(?string $chatId, ?string $userId, ?string $firstMame, ?string $lastName, ?string $userName, ?string $locale)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->firstMame = $firstMame;
        $this->lastName = $lastName;
        $this->userName = $userName;
        $this->locale = $locale;
    }

    public function getChatId(): ?string
    {
        return $this->chatId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getFirstMame(): ?string
    {
        return $this->firstMame;
    }

    
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
