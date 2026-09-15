<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookAutoreply extends Model
{
    use HasFactory;

    protected $table = 'tbl_webhook_autoreplies';

    protected $fillable = [
        'tipe',
        'keyword',
        'pesan',
        'media_path',
        'status_aktif',
    ];
}
