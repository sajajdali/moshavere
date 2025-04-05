<div>
   @include('front::components.search-page')
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.accordion__button', function() {
                var $button = $(this);
                var $item = $button.closest('.accordion__container--2');
                if (!$item.hasClass('open')) {
                    $item.find('.accordion__container--2.open').removeClass('open');
                }
            });

            $(document).on('click', '.applyFilter', function() {
                var name = $(this).data('name');
                var category = $(this).data('index');
                if (category == 'speciality') {
                    $('.specialityfilterActive').removeClass('active');
                } else {
                    $('.serviceFilterActive').removeClass('active');
                }
                $(this).addClass('active');
                @this.applyFilter(name, category);
            });
            Livewire.on('removeFilter', function($removedItem) {
                if ($removedItem == 'speciality') {
                    $('.specialityfilterActive').removeClass('active');
                    $('#specialityFilter').removeClass('open');
                } else {
                    $('.serviceFilterActive').removeClass('active');
                    $('#serviceFilter').removeClass('open');
                }

            });
            Livewire.on('removeFilterAll', function() {
                $('.specialityfilterActive').removeClass('active');
                $('.serviceFilterActive').removeClass('active');
                $('.accordion__container--2').removeClass('open');
            });
            $(window).scroll(function() {
                // Calculate the necessary variables
                var scrollTop = $(this).scrollTop();
                var windowHeight = $(this).height();
                var documentHeight = $(document).height();

                // Calculate the distance from the bottom of the page
                var bottomDistance = documentHeight - (scrollTop + windowHeight);

                // Define a threshold, e.g., when the user is 200px away from the bottom
                var loadMoreThreshold = 200;

                // Check if the user has scrolled to the bottom
                if (bottomDistance <= loadMoreThreshold) {
                    loadMoreContent(); // Load more content
                }
            });
            var $is_loaded = true;

            function loadMoreContent() {
                if ($is_loaded) {
                    $is_loaded = false;
                    $('#loadMoreContent').click();
                    setTimeout(() => {
                        $is_loaded = true;
                    }, 4500);
                }
            }

        });
    </script>
@endpush
