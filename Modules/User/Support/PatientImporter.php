<?php

namespace Modules\User\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\User\Entities\User;
use Modules\User\Entities\UserMeta;
use Modules\User\Enum\UserMetaEnum;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * ورود گروهی پرونده بیماران از فایل اکسل نرم افزار قبلی.
 * ستون ها از روی عنوان سطر اول شناخته می شوند: Fname, Lname, ParvandeCode, MelliCode, Mobile, Gender (و GenderDesc).
 * کاربر با همان شماره موبایل اگر وجود داشته باشد فقط فیلدهای خالی اش تکمیل می شود، در غیر این صورت ساخته می شود.
 */
class PatientImporter
{
    /** عنوان ستون های فایل (کلید) و توضیح فارسی آن ها. */
    public const COLUMNS = [
        'ParvandeCode' => 'شماره پرونده',
        'Fname' => 'نام',
        'Lname' => 'نام خانوادگی',
        'MelliCode' => 'کد ملی',
        'Mobile' => 'شماره موبایل',
        'Gender' => 'جنسیت',
    ];

    public const MAX_REPORTED_ISSUES = 200;

    /** فیلدهای ذخیره شده در user_metas؛ کد ملی ستون جدا و یکتای خودش را دارد. */
    private const META_FIELDS = [
        'first_name' => UserMetaEnum::FIRST_NAME,
        'last_name' => UserMetaEnum::LAST_NAME,
        'document_number' => UserMetaEnum::DOCUMENT_NUMBER,
        'gender' => UserMetaEnum::GENDER,
    ];

    private ?Role $defaultRole;

    private string $unusablePassword;

    public function __construct(private readonly ?User $admin = null)
    {
        // کاربرها با کد پیامکی وارد می شوند و رمز عبور ناشناخته است؛ هش bcrypt برای هر ردیف جداگانه (حدود ۸۰ میلی ثانیه) لازم نیست
        $this->unusablePassword = Hash::make(User::generatePassword());
        $this->defaultRole = Role::whereHas('permissions', fn ($q) => $q->whereName('USER_DEFAULT'))->first();
    }

    /**
     * @return array{total:int,created:int,updated:int,unchanged:int,skipped:int,issues:list<array{row:int,level:string,message:string}>,issues_truncated:bool}
     */
    public function import(string $path, ?callable $onProgress = null): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1G');

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $rows = $reader->load($path)->getActiveSheet()->toArray(null, true, false, false);

        $header = array_shift($rows) ?? [];
        $columns = $this->mapColumns($header);
        if (! isset($columns['mobile'])) {
            throw new \InvalidArgumentException('ستون Mobile در سطر اول فایل پیدا نشد.');
        }

        $result = ['total' => 0, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'skipped' => 0, 'issues' => [], 'issues_truncated' => false];
        $issue = function (int $row, string $level, string $message) use (&$result): void {
            if (count($result['issues']) >= self::MAX_REPORTED_ISSUES) {
                $result['issues_truncated'] = true;

                return;
            }
            $result['issues'][] = ['row' => $row, 'level' => $level, 'message' => $message];
        };

        $rowCount = count($rows);
        foreach ($rows as $index => $row) {
            if ($onProgress && $index % 50 === 0) {
                $onProgress($index, $rowCount);
            }
            $rowNumber = $index + 2; // سطر اول عنوان ستون ها است
            $cell = fn (string $key) => isset($columns[$key]) ? ($row[$columns[$key]] ?? null) : null;

            if ($this->isEmptyRow($row)) {
                continue;
            }
            $result['total']++;

            $mobile = $this->normalizeMobile($cell('mobile'));
            if ($mobile === null) {
                $result['skipped']++;
                $issue($rowNumber, 'error', 'شماره موبایل نامعتبر یا خالی است: '.trim((string) $cell('mobile')));

                continue;
            }

            $data = [
                'first_name' => $this->cleanText($cell('first_name')),
                'last_name' => $this->cleanText($cell('last_name')),
                'document_number' => $this->cleanText($cell('document_number')),
                'national_code' => $this->normalizeNationalCode($cell('national_code')),
                'gender' => $this->normalizeGender($cell('gender'), $cell('gender_desc')),
            ];
            if (blank($data['national_code']) && filled($cell('national_code'))) {
                $issue($rowNumber, 'warning', 'کد ملی نامعتبر نادیده گرفته شد: '.trim((string) $cell('national_code')));
            }

            try {
                $outcome = DB::transaction(fn () => $this->apply($mobile, $data, $rowNumber, $issue));
                $result[$outcome]++;
            } catch (Throwable $e) {
                $result['skipped']++;
                $issue($rowNumber, 'error', 'خطا در ذخیره اطلاعات: '.$e->getMessage());
            }
        }

        return $result;
    }

    /**
     * @return 'created'|'updated'|'unchanged'|'skipped'
     */
    private function apply(string $mobile, array $data, int $rowNumber, callable $issue): string
    {
        $user = User::where('mobile', $mobile)->first();
        $isNew = $user === null;

        if ($isNew) {
            $user = User::create(['mobile' => $mobile, 'password' => $this->unusablePassword]);
            $user->setRelation('metas', new Collection());
            if ($this->defaultRole) {
                $user->syncRoles([$this->defaultRole]);
            }
        } elseif ($this->admin && $this->admin->cannot('update', $user)) {
            $issue($rowNumber, 'error', "کاربر با موبایل $mobile وجود دارد و شما اجازه ویرایش او را ندارید.");

            return 'skipped';
        }

        // فقط فیلد خالی کاربر موجود تکمیل می شود؛ متاهای جدید یک جا و با یک کوئری ذخیره می شوند
        $now = now();
        $newMetas = [];
        if ($isNew && $this->admin) {
            $newMetas[] = $this->metaRow($user, UserMetaEnum::CREATOR, $this->admin->id, $now);
        }
        foreach (self::META_FIELDS as $field => $metaKey) {
            if (blank($data[$field]) || filled($user->metas->where('meta_key', $metaKey)->last()?->meta_value)) {
                continue;
            }
            $newMetas[] = $this->metaRow($user, $metaKey, $data[$field], $now);
        }
        if ($newMetas) {
            UserMeta::insert($newMetas);
        }

        $changed = $isNew || $newMetas !== [];
        if (filled($data['national_code']) && blank($user->national_code)) {
            try {
                $user->national_code = $data['national_code'];
                $changed = true;
            } catch (ValidationException $e) {
                $issue($rowNumber, 'warning', collect($e->errors())->flatten()->first().' ('.$data['national_code'].')');
            }
        }

        return $isNew ? 'created' : ($changed ? 'updated' : 'unchanged');
    }

    private function metaRow(User $user, UserMetaEnum $key, mixed $value, Carbon $now): array
    {
        return ['user_id' => $user->id, 'meta_key' => $key->value, 'meta_value' => (string) $value, 'created_at' => $now, 'updated_at' => $now];
    }

    /** @return array<string,int> نام داخلی فیلد => شماره ستون */
    private function mapColumns(array $header): array
    {
        $aliases = [
            'parvandecode' => 'document_number',
            'fname' => 'first_name',
            'lname' => 'last_name',
            'melicode' => 'national_code',
            'mellicode' => 'national_code',
            'mobile' => 'mobile',
            'gender' => 'gender',
            'genderdesc' => 'gender_desc',
        ];
        $columns = [];
        foreach ($header as $position => $title) {
            $key = $aliases[strtolower(preg_replace('/[^a-z]/i', '', (string) $title))] ?? null;
            if ($key !== null && ! isset($columns[$key])) {
                $columns[$key] = $position;
            }
        }

        return $columns;
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)->every(fn ($value) => blank(is_string($value) ? trim($value) : $value));
    }

    /** ارقام فارسی را انگلیسی می کند، فاصله ها را می گیرد و حروف عربی را فارسی می کند. */
    private function cleanText(mixed $value): ?string
    {
        if ($value === null || is_bool($value)) {
            return null;
        }
        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }
        $text = trim(preg_replace('/\s+/u', ' ', str_replace(['ي', 'ك'], ['ی', 'ک'], (string) $value)));

        return $text === '' ? null : $text;
    }

    private function digits(mixed $value): string
    {
        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        return preg_replace('/\D/', '', (string) convert2english(trim((string) $value))) ?? '';
    }

    private function normalizeMobile(mixed $value): ?string
    {
        $digits = $this->digits($value);
        if (str_starts_with($digits, '0098')) {
            $digits = '0'.substr($digits, 4);
        } elseif (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        } elseif (strlen($digits) === 10 && $digits[0] === '9') {
            $digits = '0'.$digits;
        }

        return preg_match('/^09\d{9}$/', $digits) ? $digits : null;
    }

    /** اکسل صفرهای ابتدای کد ملی را می اندازد؛ کدهای ۸ یا ۹ رقمی با صفر تکمیل می شوند. */
    private function normalizeNationalCode(mixed $value): ?string
    {
        $digits = $this->digits($value);
        if (in_array(strlen($digits), [8, 9], true)) {
            $digits = str_pad($digits, 10, '0', STR_PAD_LEFT);
        }

        return strlen($digits) === 10 ? $digits : null;
    }

    private function normalizeGender(mixed $code, mixed $description): ?string
    {
        $text = mb_strtolower((string) $this->cleanText($description));
        if (in_array($text, ['زن', 'خانم', 'female', 'f'], true)) {
            return User::USER_GENDER_FEMALE;
        }
        if (in_array($text, ['مرد', 'آقا', 'male', 'm'], true)) {
            return User::USER_GENDER_MALE;
        }

        // کد جنسیت در نرم افزار قبلی: ۲۱ زن، ۲۰ مرد
        return match ($this->digits($code)) {
            '21' => User::USER_GENDER_FEMALE,
            '20' => User::USER_GENDER_MALE,
            default => null,
        };
    }
}
