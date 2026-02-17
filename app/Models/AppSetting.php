<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Get an encrypted setting value, decrypted on read.
     */
    public static function getEncrypted(string $key, mixed $default = null): mixed
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $default;
        }
    }

    /**
     * Store a setting value encrypted at rest.
     */
    public static function setEncrypted(string $key, ?string $value): void
    {
        if ($value === null || $value === '') {
            static::set($key, '');

            return;
        }

        static::set($key, Crypt::encryptString($value));
    }
}
