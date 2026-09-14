<!doctype html>
<html lang="fa" data-theme="{{ $config->renderer()->get('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="color-scheme" content="{{ $config->renderer()->get('theme', 'light') }}">
    <title>{{ $config->get('ui.title') ?? config('app.name').' - API Docs' }}</title>

    <script src="https://unpkg.com/@stoplight/elements@8.4.2/web-components.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements@8.4.2/styles.min.css">

    @include('scramble::dev-tools', ['renderer' => 'elements'])

    <script>
        const originalFetch = window.fetch;

        window.fetch = (url, options) => {
            const cookie = document.cookie
                .split(';')
                .find((item) => item.trim().startsWith('XSRF-TOKEN'));

            if (!cookie) {
                return originalFetch(url, options);
            }

            const headers = new Headers(options?.headers || {});
            headers.set('X-XSRF-TOKEN', decodeURIComponent(cookie.split('=')[1]));

            return originalFetch(url, {...options, headers});
        };
    </script>

    <style>
        @font-face {
            font-family: 'Vazirmatn Practitioner Docs';
            src: url('/assets/admin/fonts/woff2/Vazirmatn-Regular.woff2') format('woff2');
            font-style: normal;
            font-weight: 400;
            font-display: swap;
        }

        @font-face {
            font-family: 'Vazirmatn Practitioner Docs';
            src: url('/assets/admin/fonts/woff2/Vazirmatn-Medium.woff2') format('woff2');
            font-style: normal;
            font-weight: 500 600;
            font-display: swap;
        }

        @font-face {
            font-family: 'Vazirmatn Practitioner Docs';
            src: url('/assets/admin/fonts/woff2/Vazirmatn-Bold.woff2') format('woff2');
            font-style: normal;
            font-weight: 700 900;
            font-display: swap;
        }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            background-color: var(--color-canvas);
        }

        /* فقط متن‌های توضیحی فارسی RTL هستند؛ ساختار، routeها و کنسول Try It چپ‌چین می‌مانند. */
        .sl-markdown-viewer,
        .sl-markdown-viewer p,
        .sl-markdown-viewer li,
        .sl-markdown-viewer h1,
        .sl-markdown-viewer h2,
        .sl-markdown-viewer h3,
        .sl-markdown-viewer h4,
        .sl-markdown-viewer h5,
        .sl-markdown-viewer h6,
        [data-testid="description"] {
            font-family: 'Vazirmatn Practitioner Docs', Tahoma, sans-serif !important;
            direction: rtl;
            text-align: right;
            unicode-bidi: plaintext;
            line-height: 2;
        }

        /* عبارت‌های فنی وسط متن فارسی ترتیب اصلی خود را حفظ می‌کنند. */
        .sl-markdown-viewer code,
        .sl-markdown-viewer pre,
        .sl-markdown-viewer kbd,
        [data-testid="description"] code {
            direction: ltr;
            text-align: left;
            unicode-bidi: isolate;
        }

        .sl-markdown-viewer :not(pre) > code,
        [data-testid="description"] :not(pre) > code {
            display: inline-block;
        }

        [data-theme="dark"] .token.property { color: rgb(128, 203, 196) !important; }
        [data-theme="dark"] .token.operator { color: rgb(255, 123, 114) !important; }
        [data-theme="dark"] .token.number { color: rgb(247, 140, 108) !important; }
        [data-theme="dark"] .token.string { color: rgb(165, 214, 255) !important; }
        [data-theme="dark"] .token.boolean { color: rgb(121, 192, 255) !important; }
        [data-theme="dark"] .token.punctuation { color: #dbdbdb !important; }
    </style>
</head>
<body style="height: 100vh; overflow-y: hidden">
<elements-api
    id="docs"
    @foreach($config->renderer()->all(except: ['theme']) as $key => $value)
        @continue(! $value || $key === 'view')
        {{ $key }}="{{ $value === true ? 'true' : ($value === false ? 'false' : $value) }}"
    @endforeach
/>
<script>
    (async () => {
        const docs = document.getElementById('docs');
        docs.apiDescriptionDocument = @json($spec);
    })();
</script>
</body>
</html>
