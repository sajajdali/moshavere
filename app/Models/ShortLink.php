<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Setting\Enum\SettingKeyEnum;
use Modules\User\Entities\User;

class ShortLink extends Model
{
    use HasFactory;

    // لینک خدمت مشخص: پروفایل پزشک با بخش و مطب از پیش انتخاب شده
    public const TYPE_DOCTOR_SERVICE = 'doctor_service';

    // کد دلخواه: حروف کوچک لاتین، عدد و خط تیره
    public const CODE_PATTERN = '/^[a-z0-9](?:[a-z0-9-]{0,58}[a-z0-9])?$/';

    protected $guarded = ['id'];

    protected $casts = [
        'details' => 'array',
        'visited_at' => 'datetime',
    ];

    public function shortlinkable()
    {
        return $this->morphTo();
    }

    public function scopeForDoctorService(Builder $query, int $doctorId, int $serviceId, int $placeId): Builder
    {
        return $query->where('shortlinkable_type', User::class)
            ->where('shortlinkable_id', $doctorId)
            ->where('details->type', self::TYPE_DOCTOR_SERVICE)
            ->where('details->service_id', $serviceId)
            ->where('details->place_id', $placeId);
    }

    /**
     * آدرس مقصد. لینک خدمت مشخص هنگام باز شدن ساخته می شود تا با عوض شدن
     * قالب سایت (قدیم/جدید) یا نام پزشک، لینک های پخش شده خراب نشوند.
     */
    public function targetUrl(): string
    {
        if (data_get($this->details, 'type') === self::TYPE_DOCTOR_SERVICE) {
            $doctor = User::find($this->shortlinkable_id);
            $query = [
                'service_id' => data_get($this->details, 'service_id'),
                'place_id' => data_get($this->details, 'place_id'),
            ];

            if (! $doctor) {
                return url('/');
            }

            if (filter_var(setting(SettingKeyEnum::USE_NEW_TEMPLATE), FILTER_VALIDATE_BOOL)) {
                return route('front.doctor.profile', [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => str_replace(' ', '-', trim((string) $doctor->full_name)) ?: null,
                ]).'?'.http_build_query($query);
            }

            // قالب قدیم صفحه انتخاب روز را مستقیم با بخش و مطب باز می کند
            return route('front.setAppointment.days', ['doctor_id' => $doctor->id, ...$query]);
        }

        return $this->link_url;
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

    /**
     * پیشنهاد کد معنی دار، مثل dr-ghfori-msrt یا dr-ghfori-msrt-vnk
     */
    public static function suggestDoctorServiceCode(string $doctorName, string $serviceTitle, ?string $placeTitle = null): string
    {
        $parts = array_filter([
            'dr-'.self::latinSlug(self::stripTitle($doctorName)),
            self::latinSlug($serviceTitle),
            $placeTitle ? self::latinSlug($placeTitle) : null,
        ], fn ($part) => $part !== '' && $part !== 'dr-');

        $code = Str::limit(implode('-', $parts), 50, '');

        return self::uniqueCode(trim($code, '-') ?: 'dr');
    }

    /**
     * اگر کد گرفته شده باشد، -2، -3 و ... به آن اضافه می شود
     */
    public static function uniqueCode(string $code, ?int $ignoreId = null): string
    {
        $candidate = $code;
        $i = 2;
        while (static::where('link_code', $candidate)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $candidate = $code.'-'.$i++;
        }

        return $candidate;
    }

    private static function stripTitle(string $name): string
    {
        return trim(preg_replace('/^\s*(دکتر|دکترِ|dr\.?)\s+/iu', '', $name));
    }

    /**
     * تبدیل ساده فارسی به حروف لاتین برای آدرس؛ کد پیشنهادی در مدال قابل ویرایش است.
     */
    private static function latinSlug(string $text): string
    {
        $map = [
            'آ' => 'a', 'ا' => 'a', 'أ' => 'a', 'إ' => 'e', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 's',
            'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'z', 'ر' => 'r', 'ز' => 'z',
            'ژ' => 'zh', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'z', 'ط' => 't', 'ظ' => 'z', 'ع' => '',
            'غ' => 'gh', 'ف' => 'f', 'ق' => 'gh', 'ک' => 'k', 'ك' => 'k', 'گ' => 'g', 'ل' => 'l', 'م' => 'm',
            'ن' => 'n', 'ه' => 'h', 'ة' => 'h', 'ء' => '', 'ئ' => 'i', 'ؤ' => 'o',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ];

        $out = '';
        $chars = mb_str_split($text);
        foreach ($chars as $i => $char) {
            $atWordStart = $i === 0 || preg_match('/\s/u', $chars[$i - 1]);
            $out .= match ($char) {
                // و و ی در ابتدای کلمه صامت اند، در میانه معمولاً مصوت
                'و' => $atWordStart ? 'v' : 'o',
                'ی', 'ي' => $atWordStart ? 'y' : 'i',
                default => $map[$char] ?? $char,
            };
        }

        return Str::slug($out);
    }
}
