<?php
/**
 * @see https://github.com/artesaos/seotools
 */

return [
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults'       => [
            'title'        => "", // set false to total remove
            'titleBefore'  => false, // Put defaults.title before page title, like 'It's Over 9000! - Dashboard'
            'description'  => 'پزشکم، سامانه نوبت‌دهی آنلاین پزشکان، کلینیک‌ها و بیمارستان‌ها. سریع و آسان پزشک موردنظر خود را جستجو کنید و نوبت بگیرید.', // set false to total remove
            'separator'    => ' - ',
            'keywords'     => [
                "نوبت دهی آنلاین",
                "نوبت دهی اینترنتی",
                "دکتر آنلاین",
                "رزرو نوبت پزشک",
                "سامانه نوبت دهی",
                "نوبت دهی بیمارستان",
                "پزشک متخصص",
                "کلینیک آنلاین",
                "مشاوره پزشکی",
                "ویزیت آنلاین",
                "نوبت دهی درمانگاه",
                "رزرو نوبت اینترنتی",
                "نوبت پزشک عمومی",
                "نوبت پزشک متخصص",
                "مشاوره تلفنی پزشک"
            ],
            'canonical'    => 'full', // Set to null or 'full' to use Url::full(), set to 'current' to use Url::current(), set false to total remove
            'robots'       => false, // Set to 'all', 'none' or any combination of index/noindex and follow/nofollow
        ],
        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            'title'        => "پزشکم", // set false to total remove
            'description'  => 'پزشکم، سامانه نوبت‌دهی آنلاین پزشکان، کلینیک‌ها و بیمارستان‌ها. سریع و آسان پزشک موردنظر خود را جستجو کنید و نوبت بگیرید.',
            'url'         => false, // Set null for using Url::current(), set false to total remove
            'type'        => false,
            'site_name'   => false,
            'images'      => [],
        ],
    ],
    'twitter' => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            //'card'        => 'summary',
            //'site'        => '@LuizVinicius73',
        ],
    ],
    'json-ld' => [
        /*
         * The default configurations to be used by the json-ld generator.
         */
        'defaults' => [
            'title'        => "پزشکم", // set false to total remove
            'description'  => 'پزشکم، سامانه نوبت‌دهی آنلاین پزشکان، کلینیک‌ها و بیمارستان‌ها. سریع و آسان پزشک موردنظر خود را جستجو کنید و نوبت بگیرید.',
            'url'         => 'full', // Set to null or 'full' to use Url::full(), set to 'current' to use Url::current(), set false to total remove
            'type'        => 'WebPage',
            'images'      => [
                "https://pezeshkam.com//storage/logo/TG6lA1S89XkTUL7kWE0hn3BIfnjg3SeaZAmgpiRb.png"
            ],
        ],
    ],
];
