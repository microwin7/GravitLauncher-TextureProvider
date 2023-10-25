<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use Microwin7\PHPUtils\Request\Data;

class MojangType
{
    private readonly    ?string $uuid;
    private readonly    ?object $textures;
    public              ?string $skinData = null;
    public  readonly    ?string $skinUrl;
    public  readonly    bool    $skinSlim;
    public              ?string $capeData = null;
    public  readonly    ?string $capeUrl;

    function __construct(
        public readonly string  $username,
                        bool    $skinAlreadyDetected,
                        bool    $capeAlreadyDetected
    ) {
        if (
            !is_null($this->uuid = $this->getUUID()) &&
            !is_null($this->textures = $this->getTextures()) &&
            ($skinAlreadyDetected === false || $capeAlreadyDetected === false)
        ) {
            if (!is_null($this->skinUrl = $this->getSkinUrl()) && $skinAlreadyDetected === false) {
                $this->skinData = $this->getSkinData();
                $this->skinSlim = $this->checkIsSlim();
            }
            if (!is_null($this->capeUrl = $this->getCapeUrl()) && $capeAlreadyDetected === false) {
                $this->capeData = $this->getCapeData();
            }
        }
    }
    private function getUUID(): ?string
    {
        return json_decode(Data::getDataFromUrl('https://api.mojang.com/users/profiles/minecraft/' . $this->username))?->id;
    }
    private function getTextures(): ?object
    {
        $profile = Data::getDataFromUrl('https://sessionserver.mojang.com/session/minecraft/profile/' . $this->uuid);
        $properties = @json_decode($profile)?->properties;
        if ($properties !== null) {
            foreach ($properties as $property) {
                if ($property?->name === 'textures' && is_string($property?->value))
                    return json_decode(base64_decode($property->value))?->textures;
            }
        }
        return null;
    }
    private function getSkinUrl(): ?string
    {
        return @$this->textures?->SKIN?->url;
    }
    private function getSkinData(): ?string
    {
        return Data::getDataFromUrl($this->skinUrl) ?: null;
    }
    private function checkIsSlim(): bool
    {
        return @$this->textures?->SKIN?->metadata?->model === 'slim' ? true : false;
    }
    private function getCapeUrl(): ?string
    {
        return @$this->textures?->CAPE?->url;
    }
    private function getCapeData(): ?string
    {
        return Data::getDataFromUrl($this->capeUrl) ?: null;
    }
}
