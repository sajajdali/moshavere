<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">ثبت عدم حضور</h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    @if ($step == 1)
        {{-- select doctor --}}
        @include('absence::components.selectdoctor')
    @elseif($step == 2)
        @include('absence::components.selectdate')
    @endif
    {{-- modal --}}
    @include('absence::components.confirmmodal')
    {{-- modal --}}
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            function js() {
                $('.datePicker').each(function() {
                    if (!$(this).data('persianDatepickerInitialized')) {
                        var inp = $(this);
                        $(this).persianDatepicker({
                            initialValue: false,
                            format: 'L',
                            autoClose: true,
                            onSelect: function(unix) {
                                @this.set('form.' + inp.data('counter'), inp.val());
                            }
                        });
                        $(this).data('persianDatepickerInitialized', true); // Mark initialization
                    }
                });
            }
            js();
            selectcheckboxes();
            Livewire.on('jsloader', function() {
                setInterval(() => {
                    js();
                    selectcheckboxes()
                }, 1000);
            })

            var confirmSectionModal = new bootstrap.Modal(document.getElementById('confirmabsenteeModal_1'));
            //listen on lunch modal event
            Livewire.on('lunchSelectSectionMdal', function() {
                confirmSectionModal.show();
            })

            function selectcheckboxes() {
                $('#checkAllButton').on('click', function() {
                    $('input[type="checkbox"]').prop('checked', true);
                    $(this).addClass('d-none');
                    $('#uncheckAllButton').removeClass('d-none');
                    checkcheckboxLength();
                });
                $('#uncheckAllButton').on('click', function() {
                    $('input[type="checkbox"]').prop('checked', false);
                    $(this).addClass('d-none');
                    $('#checkAllButton').removeClass('d-none');
                    checkcheckboxLength();

                });
                $('.custom-control-input').click(function() {
                    checkcheckboxLength();
                });
            }

            function checkcheckboxLength() {
                var numChecked = $('.custom-control-input:checked').length;
                if (numChecked > 1) {
                    $('.alert-info').fadeIn();
                    $('.alert-info').removeClass('d-none');
                } else {
                    if (numChecked <= 1) {
                        $('.alert-info').fadeOut();
                        $('.alert-info').addClass('d-none');
                    }
                }
            }
        });
    </script>
@endpush
