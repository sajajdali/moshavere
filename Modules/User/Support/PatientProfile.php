<?php

namespace Modules\User\Support;

use Hekmatinasser\Verta\Facades\Verta;
use Modules\User\Entities\User;
use Modules\User\Enum\UserMetaEnum;

/**
 * فیلدهای پرونده مراجع (کد ملی، تاریخ تولد، نام پدر، وضعیت تأهل و نحوه آشنایی) که هم هنگام ثبت نوبت
 * و هم در صفحه «افزودن مراجع» جمع آوری می شوند.
 */
class PatientProfile
{
    public const FIELDS = ['national_code', 'birth_date', 'father_name', 'marital_status', 'acquaintance'];

    public const MARITAL_STATUSES = [
        'single' => 'مجرد',
        'married' => 'متأهل',
        'divorced' => 'مطلقه / جدا شده',
        'widowed' => 'همسر فوت شده',
    ];

    public const ACQUAINTANCE_OPTIONS = [
        'اینستاگرام',
        'جستجوی گوگل',
        'معرفی دوستان و آشنایان',
        'معرفی پزشک یا مرکز دیگر',
        'تبلیغات محیطی',
        'سایر',
    ];

    /**
     * @return array{year:int,month:int,day:int}|null
     */
    public static function parseBirthDate(string $value): ?array
    {
        $value = (string) convert2english(trim($value));
        if (! preg_match('#^(\d{4})[/-](\d{1,2})[/-](\d{1,2})$#', $value, $m)) {
            return null;
        }
        [$year, $month, $day] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        $today = Verta::now();
        if ($year < 1300 || $month < 1 || $month > 12 || $day < 1 || $day > ($month <= 6 ? 31 : 30)) {
            return null;
        }
        if ($year > $today->year || ($year === $today->year && ($month > $today->month || ($month === $today->month && $day > $today->day)))) {
            return null;
        }

        return ['year' => $year, 'month' => $month, 'day' => $day];
    }

    public static function age(string $birthDate): ?int
    {
        $birth = self::parseBirthDate($birthDate);
        if ($birth === null) {
            return null;
        }
        $today = Verta::now();
        $age = $today->year - $birth['year'];
        if ($today->month < $birth['month'] || ($today->month === $birth['month'] && $today->day < $birth['day'])) {
            $age--;
        }

        return max($age, 0);
    }

    /** ارقام فارسی را انگلیسی می کند و فاصله های اضافه را می گیرد. */
    public static function normalize(array $form): array
    {
        foreach (['national_code', 'birth_date'] as $field) {
            if (filled($form[$field] ?? null)) {
                $form[$field] = (string) convert2english(trim((string) $form[$field]));
            }
        }
        if (filled($form['father_name'] ?? null)) {
            $form['father_name'] = trim((string) $form['father_name']);
        }

        return $form;
    }

    /** مقدارهای ذخیره شده پرونده یک کاربر، به شکل مقدارهای فرم. */
    public static function load(User $user): array
    {
        $meta = fn (UserMetaEnum $key) => $user->metas->where('meta_key', $key)->last()?->meta_value;
        $birth = json_decode((string) $meta(UserMetaEnum::BIRTHDAY), true);

        return [
            'national_code' => $user->national_code,
            'birth_date' => is_array($birth) && isset($birth['year'], $birth['month'], $birth['day'])
                ? sprintf('%04d/%02d/%02d', $birth['year'], $birth['month'], $birth['day'])
                : null,
            'father_name' => $meta(UserMetaEnum::FATHER_NAME),
            'marital_status' => $meta(UserMetaEnum::MARITAL_STATUS),
            'acquaintance' => $meta(UserMetaEnum::ACQUAINTANCE),
        ];
    }

    /** فیلد خالی مقدار قبلی پرونده را پاک نمی کند. */
    public static function save(User $user, array $form): void
    {
        if (filled($form['national_code'] ?? null)) {
            $user->national_code = $form['national_code'];
        }
        if ($birth = self::parseBirthDate((string) ($form['birth_date'] ?? ''))) {
            $user->birthday = json_encode(['year' => $birth['year'], 'month' => $birth['month'], 'day' => $birth['day']]);
        }
        if (filled($form['father_name'] ?? null)) {
            $user->father_name = trim((string) $form['father_name']);
        }
        if (filled($form['marital_status'] ?? null)) {
            $user->marital_status = $form['marital_status'];
        }
        if (filled($form['acquaintance'] ?? null)) {
            $user->acquaintance = $form['acquaintance'];
        }
    }
}
