<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $setting->value;
    }

    /**
     * Set nilai pengaturan berdasarkan key
     */
    public static function set(string $key, mixed $value): void
    {
        $val = is_array($value) ? json_encode($value) : (string) $value;
        static::updateOrCreate(['key' => $key], ['value' => $val]);
    }
}
