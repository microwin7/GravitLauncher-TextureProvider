<?php

declare(strict_types=1);

namespace Microwin7\TextureProvider\Texture\Storage;

use Microwin7\PHPUtils\Exceptions\UserNotFoundException;
use Microwin7\PHPUtils\Request\Data;

class MojangType
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private readonly    string  $uuid;
    /**
     * @var object{
     *  SKIN: object{
     *    url: string,
     *    metadata?: object{model: 'slim'|string}
     *    },
     *  CAPE?: object{
     *    url: string
     *  }
     * }
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private readonly    object  $textures;
    public              ?string $skinData = null;
    /** @psalm-suppress PropertyNotSetInConstructor */
    public  readonly    string  $skinUrl;
    /** @psalm-suppress PropertyNotSetInConstructor */
    public  readonly    bool    $skinSlim;
    public              ?string $capeData = null;
    public              string  $capeUrl = '';

    function __construct(
        public readonly string  $username,
                        bool    $skinAlreadyDetected,
                        bool    $capeAlreadyDetected
    ) {
        try {
            if (
                !is_null($this->uuid = $this->getUUID()) &&
                !is_null($this->textures = $this->getTextures()) &&
                ($skinAlreadyDetected === false || $capeAlreadyDetected === false)
            ) {
                if ($skinAlreadyDetected === false && !is_null($this->skinUrl = $this->getSkinUrl())) {
                    $this->skinData = $this->getSkinData();
                    $this->skinSlim = $this->checkIsSlim();
                }
                if ($capeAlreadyDetected === false && !empty($this->capeUrl = $this->getCapeUrl())) {
                    $this->capeData = $this->getCapeData();
                }
            }
        } catch (UserNotFoundException | \RuntimeException) {
        }
    }
    /** 
     * @throws UserNotFoundException
     */
    private function getUUID(): string
    {
        if (($userProfile = Data::getDataFromUrl('https://api.mojang.com/users/profiles/minecraft/' . $this->username)) !== false) {
            /** @var object{id: string, name: string} */
            $decodeData = json_decode($userProfile);
            return $decodeData->id;
        }
        throw new UserNotFoundException;
    }
    /**
     * @return object{
     *  SKIN: object{
     *    url: string,
     *    metadata?: object{model: 'slim'|string}
     *    },
     *  CAPE?: object{
     *    url: string
     *  }
     * }
     * @throws \RuntimeException
     */
    private function getTextures(): object
    {
        if (($profile = Data::getDataFromUrl('https://sessionserver.mojang.com/session/minecraft/profile/' . $this->uuid)) !== false) {
            /** @var object{
             *  id: string,
             *  name: string,
             *  properties: 
             *    list<
             *      object{
             *        name: 'textures'|string,
             *        value: string, 
             *        signature?: string}
             *    >,
             *  profileActions: list[]
             * }
             */
            $decodeData = json_decode($profile);
            foreach ($decodeData->properties as $property) {
                if ($property->name === 'textures') {
                    /** @var object{
                     *  timestamp: int,
                     *  profileId: string,
                     *  profileName: string,
                     *  textures: object{
                     *    SKIN: object{
                     *      url: string,
                     *      metadata?: object{model: 'slim'|string}
                     *      },
                     *    CAPE?: object{
                     *      url: string
                     *      }
                     *  }
                     * }
                     * */
                    $textureProperty = json_decode(base64_decode($property->value));
                    return $textureProperty->textures;
                }
            }
        }
        throw new \RuntimeException;
    }
    private function getSkinUrl(): string
    {
        return $this->textures->SKIN->url;
    }
    private function getSkinData(): string
    {
        $skinData = Data::getDataFromUrl($this->skinUrl);
        if ($skinData !== false) return $skinData;
        throw new \RuntimeException;
    }
    private function checkIsSlim(): bool
    {
        $skin = $this->textures->SKIN;
        if (isset($skin->metadata)) {
            return $skin->metadata->model === 'slim' ? true : false;
        }
        return false;
    }
    private function getCapeUrl(): string
    {
        if (isset($this->textures->CAPE)) {
            return $this->textures->CAPE->url;
        }
        return '';
    }
    private function getCapeData(): string
    {
        $capeData = Data::getDataFromUrl($this->capeUrl);
        if ($capeData !== false) return $capeData;
        throw new \RuntimeException;
    }
}
