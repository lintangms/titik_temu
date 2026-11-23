<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'judul',
        'tgl_buat',
        'tgl_publikasi',
        'status',
        'penulis',
        'isi',
    ];

    public static function generateId(): string
    {
        $allIds = static::pluck('id')->toArray();
        $maxNum = 0;
        foreach ($allIds as $id) {
            if (preg_match('/^PGM-(\d+)$/', $id, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        return sprintf('PGM-%03d', $maxNum + 1);
    }
}
