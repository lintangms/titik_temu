<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nama',
        'kategori',
        'status',
        'lokasi',
        'tanggal',
        'deskripsi',
        'pelapor',
        'kontak',
    ];

    public static function generateId(): string
    {
        $allIds = static::pluck('id')->toArray();
        $maxNum = 0;
        foreach ($allIds as $id) {
            if (preg_match('/^BRG-(\d+)$/', $id, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        return sprintf('BRG-%03d', $maxNum + 1);
    }
}
