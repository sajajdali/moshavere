<div>
    <div class="page-header mb-5">
        <div>
            <h1 class="page-title">ثبت عدم حضور</h1>
        </div>
    </div>
    @if ($step == 1)
        <div id="checkForSpecialSectoion" class="alert alert-info d-none" role="alert" wire:ignore> در نظر
            داشته باشید
            که در صورتی که مایل هستید در یک بخش خاص تنظیمات اعمال شوند ، فقط باید یک شخص را انتخاب کنید!!
        </div>
    @endif
    @include('admin::layouts.components.alert')
    @if ($step == 1)
        {{-- select doctor --}}
        @include('absence::components.selectdoctor')
        {{-- operators --}}
        @if (\Modules\User\Entities\User::operators() != null && \Modules\User\Entities\User::operators()->isNotEmpty())
            @include('absence::components.operatorlist')
        @endif
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
                    $('input[type="checkbox"][data-for="doctor"]').prop('checked', true).each(function() {
                        @this.ChangeCheckBoxesStatus($(this).attr('data-id'), true);
                    });
                    $(this).addClass('d-none');
                    $('#uncheckAllButton').removeClass('d-none');
                    checkcheckboxLength();
                });

                $('#uncheckAllButton').on('click', function() {
                    $('input[type="checkbox"][data-for="doctor"]').prop('checked', false).each(function() {
                        @this.ChangeCheckBoxesStatus($(this).attr('data-id'), false);
                    });
                    $(this).addClass('d-none');
                    $('#checkAllButton').removeClass('d-none');
                    checkcheckboxLength();
                });

                $('.custom-control-input[data-for="doctor"]').click(function() {
                    checkcheckboxLength();
                });
                $('input[type="checkbox"]').on('click', function() {
                    var docCheckBoxes = $('input[type="checkbox"][data-for="doctor"]:checked').length > 0;
                    var operatorsCheckBoxes = $('input[type="checkbox"][data-for="operator"]:checked')
                        .length > 0;
                    console.log(docCheckBoxes);
                    if (docCheckBoxes) {
                        $('#operatorCard').addClass('opacity-25');
                        $('#doctorCard').removeClass('opacity-25');
                    }else{
                        $('#operatorCard').removeClass('opacity-25');
                    }
                    if (operatorsCheckBoxes) {
                        $('#operatorCard').removeClass('opacity-25');
                        $('#doctorCard').addClass('opacity-25');
                    }else{
                        $('#doctorCard').removeClass('opacity-25');
                    }
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
