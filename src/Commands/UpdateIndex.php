<?php

namespace Microwin7\TextureProvider\Commands;

use Microwin7\PHPUtils\Contracts\Texture\Enum\TextureStorageTypeEnum;
use Microwin7\TextureProvider\Config;
use Microwin7\TextureProvider\Utils\IndexSkinRandomCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Microwin7\PHPUtils\Services\InputOutput;
use Microwin7\PHPUtils\Utils\Texture;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @psalm-suppress UnusedClass */
#[AsCommand(name: 'update', description: 'Индексация файлов коллекции рандомных скинов')]
class UpdateIndex extends Command
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new InputOutput($input, $output);
        $io->title($this->getDescription());

        $io->info(sprintf('Папка коллекции определена как: %s', Texture::TEXTURE_STORAGE_FULL_PATH(TextureStorageTypeEnum::COLLECTION)));
        $io->info('Начата индексация файлов');
        $io->success([
            sprintf('Проиндексировано %u скинов', (new IndexSkinRandomCollection)->generateIndex()),
            'Скины будут присваиваться из коллекции, до установки игроком',
            sprintf(
                '%s' . PHP_EOL . '%s' . PHP_EOL . '%s',
                'Используется метод деления по модулю UUID на количество скинов',
                'для привязывания к файлу, без хранения данных о привязке',
                'конкретного пользователя к конкретному скину'
            )
        ]);

        exit(Command::SUCCESS);
    }
}
