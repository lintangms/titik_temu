<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasi';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nama',
        'area',
        'keterangan',
    ];

    public static function generateId(): string
    {
        $allIds = static::pluck('id')->toArray();
        $maxNum = 0;
        foreach ($allIds as $id) {
            if (preg_match('/^LOK-(\d+)$/', $id, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        return sprintf('LOK-%03d', $maxNum + 1);
    }
}
