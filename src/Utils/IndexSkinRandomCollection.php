<?php

namespace Microwin7\TextureProvider\Utils;

use Microwin7\TextureProvider\Config;
use Microwin7\PHPUtils\Response\JsonResponse;
use Microwin7\PHPUtils\Helpers\FileSystem;
use function Microwin7\PHPUtils\str_ends_with_slash;
use Microwin7\PHPUtils\Exceptions\NeedRe_GenerateCache;

class IndexSkinRandomCollection
{
    /** @var list<array{file: string, hash: string}> */
    private array $indexArray = [];
    private string $indexPath = __DIR__ . '/../../cache/index.json';
    private int $countRe_Generate = 0;
    /**
     * Генерация index файла коллекции
     * Только для вызова командой
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
            JsonResponse::json_encode($this->indexArray)
        );
        return count($files);
    }
    /** 
     * @throw NeedRe_GenerateCache;
     */
    public function getDataFromUUID(string $uuid): ?string
    {
        try {
            $uuiddec = hexdec(substr($uuid, -12));
            if (($file = file_get_contents($this->indexPath)) !== false) {
                /** @var list<object{file: string, hash: string}> */
                $fileData = json_decode($file);
                if (($count = count($fileData)) > 0) {
                    $modulo = $uuiddec % $count;
                    $index = $fileData[$modulo];
                    $data = file_get_contents(str_ends_with_slash(Config::SKIN_RANDOM_COLLECTION_PATH) . $index->file) ?: throw new NeedRe_GenerateCache;
                    $hash = $this->hash($data);
                    if ($index->hash !== $hash) throw new NeedRe_GenerateCache;
                    return $data;
                }
            }
        } catch (NeedRe_GenerateCache $e) {
            if (Config::MAX_RE_GENERATE_CACHE_COUNT > $this->countRe_Generate) {
                $this->countRe_Generate++;
                if ($this->generateIndex() > 0) $this->getDataFromUUID($uuid);
            } else throw new NeedRe_GenerateCache;
        }
        return null;
    }
    private function hash(string $data): string
    {
        return hash('sha256', $data);
    }
}
