<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerahTerima extends Model
{
    use HasFactory;

    protected $table = 'serah_terima';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'barang_id',
        'barang_nama',
        'penerima',
        'tanggal',
        'petugas',
        'status',
        'catatan',
    ];

    public static function generateId(): string
    {
        $allIds = static::pluck('id')->toArray();
        $maxNum = 0;
        foreach ($allIds as $id) {
            if (preg_match('/^STR-(\d+)$/', $id, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        return sprintf('STR-%03d', $maxNum + 1);
    }
}
