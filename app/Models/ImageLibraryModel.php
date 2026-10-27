<?php

namespace App\Models;

use CodeIgniter\Model;

class ImageLibraryModel extends Model
{
    protected $table = 'images_library';
    protected $returnType = 'array';
    protected $allowedFields = ['public_id', 'storage_name', 'original_name', 'mime_type', 'file_size', 'width', 'height', 'owner_id'];
    protected $useTimestamps = true;
    protected $updatedField = '';
}
