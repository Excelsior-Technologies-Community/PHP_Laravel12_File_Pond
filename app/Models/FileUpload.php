<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    use HasFactory;

    protected $table = 'file_uploads';

    protected $fillable = [
        'filename',
        'original_filename',
        'folder',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getExtensionAttribute(): string
    {
        return strtolower(
            pathinfo(
                $this->original_filename ?: $this->filename,
                PATHINFO_EXTENSION
            )
        ) ?: 'unknown';
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = [
            'B',
            'KB',
            'MB',
            'GB',
        ];

        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return number_format($bytes, 2) . ' ' . $units[$index];
    }
}