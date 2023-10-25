<?php

namespace Microwin7\TextureProvider\Commands;

use Microwin7\TextureProvider\Configs\Config;
use Microwin7\TextureProvider\Utils\IndexSkinRandomCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Microwin7\TextureProvider\Services\InputOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'update', description: 'Индексация файлов коллекции рандомных скинов')]
class UpdateIndex extends Command
{
    protected static $titleCommand = 'Индексация файлов коллекции рандомных скинов';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new InputOutput($input, $output);
        $io->title(self::$titleCommand);
        $io->info(sprintf('Папка коллекции определена как: %s', Config::SKIN_RANDOM_COLLECTION_PATH));
        $io->info('Начата индексация файлов');
        $io->success([
            sprintf('Проиндексировано %s скинов', (new IndexSkinRandomCollection)->generateIndex()),
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
