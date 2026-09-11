@if ($paginator->hasPages())
    <div class="oc-pagination">
        <p class="oc-pagination-summary">
            نمایش
            <strong>{{ number_format((int) ($paginator->firstItem() ?? 0)) }}</strong>
            تا
            <strong>{{ number_format((int) ($paginator->lastItem() ?? 0)) }}</strong>
            از
            <strong>{{ number_format($paginator->total()) }}</strong>
            مورد
        </p>

        <nav class="oc-pager" role="navigation" aria-label="صفحه‌بندی نتایج">
            @if ($paginator->onFirstPage())
                <span class="oc-page-nav is-disabled" aria-disabled="true">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    <span>قبلی</span>
                </span>
            @else
                <a class="oc-page-nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="صفحه قبلی">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    <span>قبلی</span>
                </a>
            @endif

            <span class="oc-page-list">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="oc-page-gap" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="oc-page-current" aria-current="page" aria-label="صفحه {{ $page }}">{{ $page }}</span>
                            @else
                                <a class="oc-page-link" href="{{ $url }}" aria-label="رفتن به صفحه {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </span>

            @if ($paginator->hasMorePages())
                <a class="oc-page-nav" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="صفحه بعدی">
                    <span>بعدی</span>
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </a>
            @else
                <span class="oc-page-nav is-disabled" aria-disabled="true">
                    <span>بعدی</span>
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </span>
            @endif
        </nav>
    </div>
@endif
