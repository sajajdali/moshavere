<?php
function getCurrentSeason()
{
    $month = \Carbon\Carbon::now()->format('n');

    switch ($month) {
        case 12:
        case 1:
        case 2:
            return 3;
        case 3:
        case 4:
        case 5:
            return 0;
        case 6:
        case 7:
        case 8:
            return 1;
        case 9:
        case 10:
        case 11:
            return 2;
        default:
            return 'Unknown';
    }
}

function generateUniqueCode($length = 4 ,$onlyNumber = false)
{
    if ($onlyNumber){
        $characters = '0123456789';
    } else {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    }

    $code = '';

    // Generate a random code
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}

function dateFormat($date)
{
    return verta($date)->format('d F Y');
}
function dateFormatSimlpe($date)
{
    return verta($date)->format('Y/m/d');
}
function dateFormatComplete($date)
{
    return verta($date)->format('Y/m/d ساعت H:i:s');
}
function appointmentUser()
{
    return app('appointmentUser');
}
function formatBytes($bytes, $precision = 2) {
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;

    if ($bytes < $kilobyte) {
        return $bytes . ' B';
    } elseif ($bytes < $megabyte) {
        return round($bytes / $kilobyte, $precision) . ' KB';
    } elseif ($bytes < $gigabyte) {
        return round($bytes / $megabyte, $precision) . ' MB';
    } else {
        return round($bytes / $gigabyte, $precision) . ' GB';
    }
}


function chatQuestions(){
    return [
        [
            'id' => 1,
            'title' => 'مشکل در نوبتدهی حضوری ',
            'answer' => null,
            'answer_detail' => null,
            'children' => [
                [
                    'id' => 11,
                    'title' => 'نوبت ها پر است ',
                    'children' => [
                        [
                            'id' => 12,
                            'title' => null,
                            'children' => [],
                            'answer' => 'مراجعه کننده گرامی به علت محدودیت در نوبت ها لطفا در روز دیگری تلاش به دریافت نوبت کنید و یا برای تسریع در روند شروع درمان ، نوبت ویزیت آنلاین دریافت کنید',
                            'answer_detail' => [
                                'title_link' => 'دریافت نوبت آنلاین',
                                'link' => 'online_appointment',
                                'image' => null,
                                'video' => null,
                                'show_chat_button' => true,
                            ]
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 13,
                    'title' => 'برای روز دیگری نوبت می خواهم ',
                    'children' => [
                        [
                            'id' => 14,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی نوبت به شما نشان داده شده اولین نوبت خالی است ، برای روزهای دیگر گزینه ( نوبت های دیگر ) را بزنید و اگر روز مدنظر در جدول نیست در روزهای آینده تلاش کنید',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => url('storage/help/chat/help.png'),
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => [],
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 15,
                    'title' => 'بیشتر از یک نوبت می خواهم ',
                    'children' => [
                        [
                            'id' => 16,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی هر شماره همراه / کدملی مجاز به دریافت یک نوبت می باشد. برای دریافت نوبت به نام شخص دیگر لطفا اطلاعات آن شخص را وارد کنید. ',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => url('storage/help/chat/help.png'),
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => [],
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 17,
                    'title' => 'ثبت نشدن نوبت دریافت شده  ',
                    'children' => [
                        [
                            'id' => 18,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی اگر پس از ثبت دریافت نوبت به شما کد رهگیری اختصاص داده نشد ، لطفا مجدد تلاش کنید و اگر پیغام پر شدن نوبت ها نمایش داده شد در روزهای آینده اقدام به دریافت نوبت کنید',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => null,
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => [],
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
            ],
        ],
        [
            'id' => 2,
            'title' => 'مشکل در نوبتدهی ویزیت آنلاین ( غیر حضوری )  ',
            'children' => [
                [
                    'id' => 19,
                    'title' => 'پس از درخواست چه زمانی نوبت ثبت می شود؟   ',
                    'children' => [
                        [
                            'id' => 20,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی از زمانی که هزینه ویزیت آنلاین را پرداخت می کنید ، صفحه چت برای شما باز می شود . پیام ها و مدارک خود را بارگذاری کنید و منتظر پاسخ پزشک باشید ',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => null,
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 21,
                    'title' => 'برای روز دیگری نوبت آنلاین می خواهم',
                    'children' => [
                        [
                            'id' => 22,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی درخواست ویزیت  آنلاین شما برای همان روز می باشد ، اگر قصد ویزیت در روز دیگری را دارید در همان روز اقدام به دریافت ویزیت آنلاین بفرمایید  ',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => null,
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],

                [
                    'id' => 23,
                    'title' => 'ویزیت آنلاین به چه صورت انجام می شود؟',
                    'children' => [
                        [
                            'id' => 24,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی ویزیت آنلاین به صورت متنی می باشد ، پس از ثبت درخواست و پرداخت هزینه صفحه چت برای شما باز می شود ، پیام ها و مدارک خود را بارگذاری کرده و منتظر پاسخ پزشک طی 24 ساعت آینده باشید.',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => null,
                                'video' => url('storage/help/chat/sample.mp4'),
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 25,
                    'title' => 'پیغام پر شدن نوبت یا غیر فعال بودن نوبت ویزیت آنلاین نمایش داده می شود.',
                    'children' => [
                        [
                            'id' => 26,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی اگر هر کدام از پیغام های فوق را مشاهده کردید ، در روز دیگری اقدام به دریافت ویزیت آنلاین بفرمایید.',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => null,
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 27,
                    'title' => 'آیا در ویزیت آنلاین می توانم عکس مدارک بفرستم و نسخه الکترونیکی دریافت کنم؟',
                    'children' => [
                        [
                            'id' => 28,
                            'title' => null,
                            'answer' => 'بله – از گزینه بغل صفحه (طبق عکس زیر) عکس مدارک را بارگذاری نمایید ، در صورت نیاز به دارو و یا آزمایش پزشک ، هم به صورت عکس نسخه کاغذی  و هم ثبت الکترونیک برای شما نسخه صادر می کند.',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => url('storage/help/chat/help.png'),
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 29,
                    'title' => 'مشکل در پرداخت دارم',
                    'children' => [
                        [
                            'id' => 30,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی پرداخت هزینه ویزیت آنلاین فقط و فقط از دزگاه بانکی انجام می شود و نیازمند رمز پویا می باشد',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => null,
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
                [
                    'id' => 31,
                    'title' => 'در حین ویزیت صفحه چت بسته شده ',
                    'children' => [
                        [
                            'id' => 31,
                            'title' => null,
                            'answer' => 'مراجعه کننده گرامی اگر ویزیت شما به اتمام رسیده و پزشک صفحه چت را بسته لطفا برای ارسال مدارک
 بعدی مجددا اقدام به دریافت نوبت آنلاین بفرمایید.
اگر حین صحبت ، چت بسته شده است و هنوز جواب کامل دریافت نکردید از طریق کادر زیر درخواست خود
را ثبت نمایید تا همکاران ما در اسرع وقت پاسخ شما را بدهند .',
                            'answer_detail' => [
                                'title_link' => null,
                                'link' => null,
                                'image' => null,
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],

            ],
            'answer' => null,
            'answer_detail' => null
        ],
        [
            'id' => 3,
            'title' => 'سایر مشکلات ',
            'children' => [
                [
                    'id' => 33,
                    'title' => 'چگونه وضعیت نوبت را پیگیری کنم؟',
                    'children' => [
                        [
                            'id' => 34,
                            'title' => null,
                            'answer' => 'برای پیگیری وضعیت نوبت به بخش نوبت های من مراجعه کنید',
                            'answer_detail' => [
                                'title_link' => 'ورود به نوبت های من',
                                'link' => 'appointment_lists',
                                'image' => url('storage/help/chat/help.png'),
                                'video' => null,
                                'show_chat_button' => true,
                            ],
                            'children' => []
                        ]
                    ],
                    'answer' => null,
                    'answer_detail' => null
                ],
            ],
            'answer' => null,
            'answer_detail' => null
        ]

    ];
}
