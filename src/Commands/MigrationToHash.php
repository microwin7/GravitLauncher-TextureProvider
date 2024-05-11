<?php

namespace Microwin7\TextureProvider\Commands;

use Microwin7\PHPUtils\Utils\GDUtils;
use Microwin7\PHPUtils\Utils\Texture;
use Microwin7\PHPUtils\Configs\MainConfig;
use Microwin7\PHPUtils\Helpers\FileSystem;
use Microwin7\PHPUtils\Services\InputOutput;
use Microwin7\PHPUtils\DB\SingletonConnector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use function Microwin7\PHPUtils\str_ends_with_slash;
use Symfony\Component\Console\Output\OutputInterface;
use Microwin7\PHPUtils\Exceptions\TextureSizeException;
use Microwin7\PHPUtils\Exceptions\TextureSizeHDException;
use Microwin7\PHPUtils\Contracts\User\UserStorageTypeEnum;
use Microwin7\PHPUtils\Contracts\Texture\Enum\ResponseTypeEnum;
use Microwin7\TextureProvider\Texture\Texture as ProviderTexture;

#[AsCommand(name: 'migration', description: 'Миграция текстур')]
class MigrationToHash extends Command
{
    protected FileSystem $fileSystem;
    protected ?InputOutput $io = null;
    /** @var null|ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE */
    protected ?ResponseTypeEnum $selectedMode = null;
    /** @var null|UserStorageTypeEnum::USERNAME|UserStorageTypeEnum::UUID|UserStorageTypeEnum::DB_USER_ID */
    protected ?UserStorageTypeEnum $selectedUserStorageTypeInput = null;
    /** Назначается только если $selectMode установлен и папка существует */
    protected ?string $currentInputPath = null;

    protected bool $titleShown = false;

    public function __construct()
    {
        $this->fileSystem = new FileSystem;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->io === null) $this->io = new InputOutput($input, $output);
        /** @var InputOutput $this-io */
        if (!$this->titleShown) {
            $this->io->title($this->getDescription());
            $this->titleShown = true;
        }
        if (!$this->selectedMode instanceof ResponseTypeEnum) $this->selectModeTexture($input, $output);
        /** @var ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE $this->selectedMode */
        if (!$this->selectedUserStorageTypeInput instanceof UserStorageTypeEnum) $this->selectUserStorageTypeInput($input, $output);
        /** @var UserStorageTypeEnum::USERNAME|UserStorageTypeEnum::UUID|UserStorageTypeEnum::DB_USER_ID $this->selectedUserStorageTypeInput */

        $this->selectCurrentInputFolder($input, $output);
        /** @var string $this->currentInputPath */

        $connect = SingletonConnector::get('TextureProvider');
        $MODULE_ARRAY_DATA = MainConfig::MODULES['TextureProvider'];
        $table_users = $MODULE_ARRAY_DATA['table_user']['TABLE_NAME'];

        $user_id_column = $MODULE_ARRAY_DATA['table_user']['id_column'];
        $user_username_column = $MODULE_ARRAY_DATA['table_user']['username_column'];
        $user_uuid_column = $MODULE_ARRAY_DATA['table_user']['uuid_column'];

        $not_valid_texture = 0;
        $textureSizeInvalidListMessage = [];

        /** @var list<array{user_id: int, username: string, uuid: string}> */
        $users = $connect->query(
            <<<SQL
            SELECT $user_id_column, $user_username_column, $user_uuid_column FROM $table_users ORDER BY $user_id_column asc
        SQL
        )->array();
        $this->io->success(sprintf('Обнаружено %u пользователей в Базе Данных', count($users)));

        $textureFiles = $this->fileSystem->findFiles($this->currentInputPath, Texture::EXTENSTION(), 0);
        // $textureFiles = $this->fileSystem->findFiles($this->currentInputPath, 0);

        $loginColumn = match ($this->selectedUserStorageTypeInput) {
            UserStorageTypeEnum::USERNAME => $user_username_column,
            UserStorageTypeEnum::UUID => $user_uuid_column,
            UserStorageTypeEnum::DB_USER_ID => $user_id_column,
        };

        $matchingTextureLogins = [];

        if (!empty($users)) {
            $this->io->info('Поиск совпадений');
            $progressBar = $this->createProgressBar($output);
            foreach ($progressBar->iterate($users) as $userRow) {
                $path = $this->currentInputPath . $userRow[$loginColumn] . Texture::EXTENSTION();
                if (in_array($path, $textureFiles))
                    $matchingTextureLogins[] = $userRow;
            }
            $this->io->success(sprintf('Обнаружено %u текстур в директории для изъятия', count($matchingTextureLogins)));
        }

        if (!empty($matchingTextureLogins)) {
            $this->io->info('Начата миграция текстур');
            $progressBar = $this->createProgressBar($output);
            foreach ($progressBar->iterate($matchingTextureLogins) as $user) {
                $user_id = $user[$user_id_column];
                $loginTexture = $user[$loginColumn];
                $dataSkin = file_get_contents($this->currentInputPath . $loginTexture . Texture::EXTENSTION());

                if ($dataSkin !== false) {
                    if (($mime = GDUtils::getImageMimeType($dataSkin)) !== IMAGETYPE_PNG) {
                        $textureSizeInvalidListMessage[] = sprintf(
                            'Текстура: %s не является форматом PNG.'
                                . (null === $mime ? '' : PHP_EOL . 'Обнаружен: ' . $mime),
                            $loginTexture . Texture::EXTENSTION()
                        );
                        continue;
                    }
                    [$image, $w, $h] = GDUtils::pre_calculation($dataSkin);
                    
                    try {
                        Texture::validateHDSize($w, $h, $this->selectedMode);
                    } catch (TextureSizeHDException $e) {
                        try {
                            Texture::validateSize($w, $h, $this->selectedMode);
                        } catch (TextureSizeException $e) {
                            $textureSizeInvalidListMessage[] =
                                sprintf('Текстура: %s не соответствует разрешённым размерам. Обнаружен размер: %ux%u', $loginTexture . Texture::EXTENSTION(), $w, $h);
                            $not_valid_texture++;
                            continue;
                        }
                    }
                    $dataHash = Texture::digest($dataSkin);
                    try {
                        $meta_texture = (string)(int)GDUtils::checkSkinSlimFromImage($image);
                    } catch (\TypeError) {
                        $not_valid_texture++;
                        continue;
                        // throw new \TypeError($e->getMessage());
                    }
                    if (copy($this->currentInputPath . $loginTexture . Texture::EXTENSTION(), Texture::PATH($this->selectedMode, $dataHash))) {
                        ProviderTexture::insertOrUpdateAssetDB((string)$user_id, $this->selectedMode->name, $dataHash, $meta_texture);
                    } else {
                        $this->io->error('Копирование файла не удалось, проверьте права доступа в директорию назначения!');
                        exit(Command::FAILURE);
                    }
                }
            }
        }

        if (!empty($textureSizeInvalidListMessage)) {
            if ($not_valid_texture !== 0) $textureSizeInvalidListMessage[] = 'Необработанных текстур: ' . $not_valid_texture;
            $this->io->warning($textureSizeInvalidListMessage);
        }
        $countSuccess = count($matchingTextureLogins) - $not_valid_texture;
        if ($countSuccess > 0) {
            $this->io->success('Обработано текстур: ' . $countSuccess);
        }
        exit(Command::SUCCESS);
    }

    protected function configure(): void
    {
        $this->addOption(
            'input_folder',
            null,
            InputOption::VALUE_OPTIONAL,
            'Путь к папке текстур для изъятия',
        );
    }
    private function selectModeTexture(InputInterface $input, OutputInterface $output): void
    {
        /**
         * @var string
         * @var InputOutput $this->io
         */
        $selectedMode = $this->io->question(sprintf(
            'Выберите тип текстуры для миграции:'
                . PHP_EOL . '%u. %s'
                . PHP_EOL . '%u. %s',
            ResponseTypeEnum::SKIN->value,
            ResponseTypeEnum::SKIN->name,
            ResponseTypeEnum::CAPE->value,
            ResponseTypeEnum::CAPE->name
        ));
        /** @var null|ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE */
        $selectedMode = ResponseTypeEnum::tryFrom((int)$selectedMode);
        if (!in_array($selectedMode, [ResponseTypeEnum::SKIN, ResponseTypeEnum::CAPE])) {
            $this->io->wrong('Тип выбран неверно, попробуйте ещё раз. Выход: Ctrl + C');
            $this->execute($input, $output);
        }
        $this->selectedMode = $selectedMode;
    }
    private function selectUserStorageTypeInput(InputInterface $input, OutputInterface $output): void
    {
        /**
         * @var string
         * @var InputOutput $this->io
         */
        $selectedUserStorageTypeInput = $this->io->question(sprintf(
            'Выберите тип хранения текстур для изъятия:'
                . PHP_EOL . '%u. %s'
                . PHP_EOL . '%u. %s'
                . PHP_EOL . '%u. %s',
            UserStorageTypeEnum::USERNAME->value,
            UserStorageTypeEnum::USERNAME->name,
            UserStorageTypeEnum::UUID->value,
            UserStorageTypeEnum::UUID->name,
            UserStorageTypeEnum::DB_USER_ID->value,
            UserStorageTypeEnum::DB_USER_ID->name
        ));
        /** @var null|UserStorageTypeEnum::USERNAME|UserStorageTypeEnum::UUID|UserStorageTypeEnum::DB_USER_ID */
        $selectedUserStorageTypeInput = UserStorageTypeEnum::tryFrom((int)$selectedUserStorageTypeInput);
        if (!in_array($selectedUserStorageTypeInput, [UserStorageTypeEnum::USERNAME, UserStorageTypeEnum::UUID, UserStorageTypeEnum::DB_USER_ID])) {
            $this->io->wrong('Тип выбран неверно, попробуйте ещё раз. Выход: Ctrl + C');
            $this->execute($input, $output);
        }
        $this->selectedUserStorageTypeInput = $selectedUserStorageTypeInput;
    }
    private function selectCurrentInputFolder(InputInterface $input, OutputInterface $output): void
    {
        /**
         * @var null|string
         * @var InputOutput $this->io
         * @var ResponseTypeEnum::SKIN|ResponseTypeEnum::CAPE $this->selectedMode
         * @var UserStorageTypeEnum::USERNAME|UserStorageTypeEnum::UUID|UserStorageTypeEnum::DB_USER_ID $this->selectedUserStorageTypeInput
         */
        $input_folder = $input->getOption('input_folder');

        if (is_string($input_folder)) {
            if (!$this->fileSystem->is_dir($input_folder)) {
                $this->io->error([
                    sprintf('Указанная папка: "%s", через параметр: "--input_folder[=INPUT_FOLDER]"', $input_folder),
                    'Не существует или PHP не может получить доступ к ней.'
                ]);
                exit(Command::FAILURE);
            }
            $this->currentInputPath = str_ends_with_slash($input_folder);
            $this->io->info(sprintf(
                'Папка для изъятия текстур зафиксирована: %s' . PHP_EOL . '(Выбранный тип текстур: %s | Выбранный тип хранения: %s)',
                $this->currentInputPath,
                $this->selectedMode->name,
                $this->selectedUserStorageTypeInput->name
            ));
            return;
        }

        /**
         * @var null|string
         * @psalm-suppress ReferenceConstraintViolation
         */
        $input_folder = $this->io->question(sprintf(
            'Введите путь к папке для изъятия текстур'
                . PHP_EOL . '(Выбранный тип текстур: %s | Выбранный тип хранения: %s): ',
            $this->selectedMode->name,
            $this->selectedUserStorageTypeInput->name
        ));
        if (is_string($input_folder)) {
            if (!$this->fileSystem->is_dir($input_folder)) {
                $this->io->error([
                    sprintf('Указанная папка: "%s"', $input_folder),
                    'Не существует или PHP не может получить доступ к ней.',
                    'Попробуйте ещё раз!'
                ]);
                $this->execute($input, $output);
            }
            $this->currentInputPath = str_ends_with_slash($input_folder);
            /** @var InputOutput $this->io */
            $this->io->info(sprintf(
                'Папка для изъятия текстур зафиксирована: %s' . PHP_EOL . '(Выбранный тип текстур: %s | Выбранный тип хранения: %s)',
                $this->currentInputPath,
                $this->selectedMode->name,
                $this->selectedUserStorageTypeInput->name
            ));
            return;
        }
        $this->io->error([
            'Вводимые данные не могут быть обработаны.',
            'Попробуйте ещё раз!'
        ]);
        $this->execute($input, $output);
    }
    private function createProgressBar(OutputInterface $output): ProgressBar
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setBarCharacter('<fg=green>•</>');
        $progressBar->setProgressCharacter('<fg=green>➤</>');
        $progressBar->setEmptyBarCharacter('<fg=default;bg=default>⚬</>');
        $progressBar->setFormat("<fg=green>%current%</>/<fg=yellow>%max%</> <fg=yellow>[</>%bar%<fg=yellow>]</> <fg=green>%percent:3s%%</>\n <fg=green>%elapsed:6s%</>/<fg=yellow>%estimated:-6s%</> <fg=green>%memory:6s%</>\n");
        return $progressBar;
    }
}
