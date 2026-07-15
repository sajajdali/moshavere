@php
    $title = setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_TITLE1);
    $image = setting(\Modules\Setting\Enum\SettingKeyEnum::HEADER1_IMAGE);
@endphp

<main class="min-h-[70vh] bg-secondary-100">
    <section class="container mx-auto flex min-h-[70vh] flex-col items-center justify-center gap-8 px-5 py-12 text-center">
        @if ($title)
            <h1 class="text-2xl font-bold text-gray-900 md:text-4xl">{{ $title }}</h1>
        @endif

        @if ($image)
            <img
                src="{{ assetStorage($image) }}"
                alt="{{ $title ?? setting(\Modules\Setting\Enum\SettingKeyEnum::SITE_TITLE) }}"
                class="h-auto max-h-[520px] w-auto max-w-full object-contain"
            >
        @endif
    </section>
</main>
