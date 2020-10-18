<?php

declare(strict_types=1);

namespace QuillStack\Storage\StorageType;

use QuillStack\Http\Response\Response;
use QuillStack\Storage\Exceptions\FileNotDeletedException;
use QuillStack\Storage\Exceptions\FileNotExistsException;
use QuillStack\Storage\Exceptions\FileNotSavedException;
use QuillStack\Storage\StorageInterface;
use Throwable;

final class LocalStorage implements StorageInterface
{
    /**
     * {@inheritDoc}
     */
    public function get(string $path)
    {
        if (!is_file($path)) {
            throw new FileNotExistsException("File doesn't exist: {$path}");
        }

        return file_get_contents($path);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $path): bool
    {
        return is_file($path);
    }

    /**
     * {@inheritDoc}
     */
    public function missing(string $path): bool
    {
        return !is_file($path);
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $path, $contents): bool
    {
        $message = "File not saved: {$path}";

        try {
            $savedBytes = file_put_contents($path, $contents);
        } catch (Throwable $exception) {
            throw new FileNotSavedException($message, Response::CODE_INTERNAL_SERVER_ERROR, $exception);
        }

        return $savedBytes > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $path, string ...$more): bool
    {
        $this->deleteOne($path);

        foreach ($more as $path) {
            $this->deleteOne($path);
        }

        return true;
    }

    /**
     * Delete one file.
     *
     * @param string $path
     *
     * @return bool
     */
    private function deleteOne(string $path): bool
    {
        $message = "File not deleted: {$path}";

        try {
            $deleted = unlink($path);
        } catch (Throwable $exception) {
            throw new FileNotDeletedException($message, Response::CODE_INTERNAL_SERVER_ERROR, $exception);
        }

        return $deleted;
    }
}
