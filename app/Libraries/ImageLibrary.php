<?php

namespace App\Libraries;

use App\Models\ImageLibraryModel;
use CodeIgniter\Database\BaseConnection;

class ImageLibrary
{
    public const MAX_FILE_BYTES = 5 * 1024 * 1024;
    private BaseConnection $db;
    private string $directory;
    private array $createdFiles = [];

    public function __construct(?BaseConnection $db = null, ?string $directory = null)
    {
        $this->db = $db ?? db_connect();
        $this->directory = $directory ?? WRITEPATH . 'images-library';
    }

    public function __destruct()
    {
        // Also clean pending files on a graceful early request exit.
        $this->rollbackFiles();
    }

    public static function validate(string $bytes, string $name): array
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $types = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        if (!isset($types[$extension])) {
            throw new PackageValidationException(["{$name}: use PNG, JPG, JPEG, WebP or GIF images."]);
        }
        if (strlen($bytes) === 0 || strlen($bytes) > self::MAX_FILE_BYTES) {
            throw new PackageValidationException(["{$name}: each image must be between 1 byte and 5 MB."]);
        }
        $info = @getimagesizefromstring($bytes);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        if (!$info || $mime !== $types[$extension] || $info['mime'] !== $mime) {
            throw new PackageValidationException(["{$name}: the image contents do not match its file type."]);
        }
        if ($info[0] > 10000 || $info[1] > 10000 || $info[0] * $info[1] > 25000000) {
            throw new PackageValidationException(["{$name}: maximum dimensions are 10,000 pixels per side and 25 megapixels."]);
        }
        return ['bytes' => $bytes, 'original_name' => $name, 'extension' => $extension,
            'mime_type' => $mime, 'file_size' => strlen($bytes), 'width' => $info[0], 'height' => $info[1]];
    }

    // Called within the same transaction as the questions that reference these images.
    public function persist(array $image, int $ownerId): array
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new \RuntimeException('Could not create image storage.');
        }
        $publicId = bin2hex(random_bytes(16));
        $name = $publicId . '.' . $image['extension'];
        $path = $this->directory . '/' . $name;
        $this->createdFiles[] = $path;
        if (file_put_contents($path, $image['bytes'], LOCK_EX) !== $image['file_size']) {
            throw new \RuntimeException('Could not save image.');
        }
        chmod($path, 0600);
        $data = array_intersect_key($image, array_flip(['original_name', 'mime_type', 'file_size', 'width', 'height']));
        $data += ['public_id' => $publicId, 'storage_name' => $name, 'owner_id' => $ownerId];
        if (!(new ImageLibraryModel($this->db))->insert($data)) {
            throw new \RuntimeException('Could not save image metadata.');
        }
        return $data + ['url' => site_url('images/' . $publicId)];
    }

    public function commitFiles(): void
    {
        $this->createdFiles = [];
    }

    public function rollbackFiles(): void
    {
        foreach ($this->createdFiles as $path) {
            if (is_file($path) && !unlink($path)) {
                log_message('critical', 'Failed to remove rolled-back library image: {path}', ['path' => $path]);
            }
        }
        $this->createdFiles = [];
    }

    public function path(array $image): string
    {
        if (!preg_match('/\A[a-f0-9]{32}\.(png|jpe?g|webp|gif)\z/', $image['storage_name'])) {
            throw new \RuntimeException('Invalid stored image path.');
        }
        return $this->directory . '/' . $image['storage_name'];
    }

    public static function requireTransactionalTables(BaseConnection $db, array $tables): void
    {
        if ($db->DBDriver !== 'MySQLi') {
            return;
        }
        foreach ($tables as $table) {
            $row = $db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$db->prefixTable($table)])->getRowArray();
            if (!$row || strtoupper($row['ENGINE']) !== 'INNODB') {
                throw new PackageValidationException(['Run the Problem Package Importer migration first; all import tables must use InnoDB.']);
            }
        }
    }
}
