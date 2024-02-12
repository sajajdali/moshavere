<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">ثبت عدم حضور</h1>
        </div>
    </div>
    @include('admin::layouts.components.alert')
    @if ($step == 1)
        {{-- select doctor --}}
        @include('appointmentsetting::components.abbsente.selectdoctor')
    @elseif($step == 2)
        @include('appointmentsetting::components.abbsente.selectdate')
    @endif

    {{-- modal --}}
    @include('appointmentsetting::components.abbsente.confirmmodal')
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
                                if (inp.data('dateType') == 'start') {
                                    @this.set('dates.' + inp.data('counter') + '.start', inp
                                        .val());
                                } else {
                                    @this.set('dates.' + inp.data('counter') + '.end', inp
                                        .val());

                                }
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
                });
            }
        });
    </script>
@endpush
