<?php

namespace Microwin7\TextureProvider\Utils;

use UnexpectedValueException;
use Microwin7\TextureProvider\Configs\Config;
use Microwin7\TextureProvider\Helpers\FileSystem;
use function Microwin7\PHPUtils\str_ends_with_slash;
use Microwin7\PHPUtils\Exceptions\FileSystemException;
use Microwin7\PHPUtils\Response\Response;

class IndexSkinRandomCollection
{
    private ?string $data = null;
    private string $hash;
    private object $index;
    private array $indexArray = [];
    private string $indexPath = __DIR__ . '/../../cache/index.json';
    /**
     * Генерация index файла коллекции
     * Только для вызова командой
     *
     * @return count integer
     */
    public function generateIndex(): int
    {
        $fileSystem = new FileSystem;
        $files = $fileSystem->findFiles(Config::SKIN_RANDOM_COLLECTION_PATH);
        foreach ($files as $file) {
            $data = file_get_contents($file);
            $this->indexArray[] = [
                'file' => str_replace(str_ends_with_slash(Config::SKIN_RANDOM_COLLECTION_PATH), '', $file),
                'hash' => $this->hash($data),
            ];
        }
        $directory = dirname($this->indexPath);
        if (!$fileSystem->is_dir($directory))
            mkdir($directory, 0755, true);
        file_put_contents(
            $this->indexPath,
            Response::json_encode($this->indexArray)
        );
        return count($files);
    }
    public function getDataFromUUID(string $uuid): ?string
    {
        $uuiddec = hexdec(substr($uuid, -12));
        try {
            if (($count = count($this->indexArray = json_decode(file_get_contents($this->indexPath)))) > 0) {
                $modulo = $uuiddec % $count;
                $this->index = $this->indexArray[$modulo];
                $this->data = file_get_contents(str_ends_with_slash(Config::SKIN_RANDOM_COLLECTION_PATH) . $this->index->file);
                $this->hash = $this->hash($this->data);
                if (!$this->hash === $this->index->hash) throw new UnexpectedValueException;
            }
        } catch (FileSystemException | UnexpectedValueException $ignored) {
        }
        return $this->data;
    }
    private function hash(string $data): string
    {
        return hash('sha256', $data);
    }
}
