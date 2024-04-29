<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortLink extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function shortlinkable()
    {
        return $this->morphTo();
    }
    protected static function generateUniqueCode()
    {
        $characters = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        // Generate a random code
        for ($i = 0; $i < 4; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    public static function generateShortLinkCode(): string
    {
        do {
            $uniqueCode = static::generateUniqueCode();
        } while (static::where('link_code', $uniqueCode)->exists());

        // Insert the unique code into the "transaction" table
        return $uniqueCode;
    }
}
