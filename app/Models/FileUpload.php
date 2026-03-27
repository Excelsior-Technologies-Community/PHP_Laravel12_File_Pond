<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    use HasFactory;

    // Aa table nu naam che jo migration ma banavyu hatu
    protected $table = 'file_uploads';

    // Aa columns ma data 'Mass Assign' (ek sathe insert) thai shakse
    protected $fillable = [
        'filename',
        'folder',
    ];
}