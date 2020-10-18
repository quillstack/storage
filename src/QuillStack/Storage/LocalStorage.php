<?php

declare(strict_types=1);

namespace QuillStack\Storage;

use QuillStack\Storage\Exceptions\FileNotDeletedException;
use QuillStack\Storage\Exceptions\FileNotExistsException;
use QuillStack\Storage\Exceptions\FileNotSavedException;

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
        $savedBytes = file_put_contents($path, $contents);

        if (!$savedBytes) {
            throw new FileNotSavedException("File not saved: {$path}");
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
        $deleted = unlink($path);

        if (!$deleted) {
            throw new FileNotDeletedException("File not deleted: {$path}");
        }

        return $deleted;
    }
}
