<div>

    <div>
        <div class="page-header mb-5">
            <div>
                <h1 class="page-title">
                    <span>پیام های آماده</span>
                </h1>
            </div>
        </div>
        @include('admin::layouts.components.alert')
        @if (isset($msg))
            <div class="col-md-12 alert alert-success fade show" role="alert">
                <i class="fa fa-remove me-2" aria-hidden="true"></i>
                {{ $msg }}
            </div>
        @endif
        <div class="card" wire:loading.class='opacity-50'>
            <div class="card-header border-bottom">
                <a data-bs-toggle="collapse" href="#createTempColl" role="button" aria-expanded="false"
                    aria-controls="createTempColl">
                    <h5>افزودن پیام جدید</h5>
                </a>
            </div>
            <div class="card-body collapse" id="createTempColl" wire:ignore.self>
                <div class="row mb-5">
                    <div class="col-md-12">
                        <label for="tempMessageText" class="form-label ms-3 mb-2">عنوان پیام</label>
                        <input wire:model='form.title' class="form-control  @error('form.title') is-invalid @enderror"
                            id="tempMessageText" type="text">
                    </div>
                    @error('form.title')
                        <div class="text-danger mt-2">
                            <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="row mb-5">
                    <div class="mb-3">
                        <label for="messagetemplatebody" class="form-label  ms-3 mb-2">متن پیام</label>
                        <textarea rows="5" wire:model='form.body' class="form-control    @error('form.body') is-invalid @enderror"
                            id="messagetemplatebody" placeholder="متن پیام را اینجا وارد کنید"></textarea>
                        @error('form.body')
                            <div class="text-danger mt-2">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    @if (isset($form['file']) || isset($form['voice']))
                        <div class="col-2">
                            <a class="text-primary" wire:click='removeFiles'>
                                حذف فایل ها
                            </a>
                        </div>
                        <div class="col-10">
                            <hr class="w-75">
                        </div>
                    @else
                        <div class="col-12">
                            <hr>
                        </div>
                    @endif
                </div>
                <div class="row mb-5">
                    <div class="mb-3 col-md-6 d-flex align-items-center">
                        <label for="messagetemplatebody" class="form-label mb-2">اضافه کردن ویس به پیام آماده</label>
                        <button type="button"
                            class="btn @if (isset($form['voice'])) btn-success @else btn-secondary @endif ms-5 w-25"
                            data-bs-toggle="modal" data-bs-target="#soundRecorderModal">
                            @if (isset($this->form['voice']))
                                <i class="fa fa-check fa-2x mt-1" aria-hidden="true"></i>
                            @else
                                <i class="fa fa-microphone fa-2x mt-1" aria-hidden="true"></i>
                            @endif
                        </button>
                        @error('form.voice')
                            <div class="text-danger mt-2">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-6  d-flex align-items-center">
                        <label for="messagetemplatebody" class="form-label mb-2">اضافه کردن تصویر به پیام آماده</label>
                        <button data-bs-target="#file-selector-modal" data-bs-toggle="modal"
                            class="btn  @if (isset($form['file'])) btn-success @else btn-light @endif mx-3 d-flex justify-content-center p-1 py-2  w-25"
                            href="javascript:void(0)">
                            @if (isset($form['file']))
                                <i class="fa fa-check fa-2x mt-1" aria-hidden="true"></i>
                            @else
                                <i class="fa fa-file fa-2x mt-1" aria-hidden="true"></i>
                            @endif
                        </button>
                        @error('form.body')
                            <div class="text-danger mt-2">
                                <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-12 mb-5">
                    <div class="form-group">
                        <label class="form-label">محدودییت نمایش این پیام برای دکتر</label>
                        <select wire:model='form.limitToDoctor' wire:igonre.self multiple
                            class="form-control select2-show-search form-select" data-id="Doc_id"
                            data-placeholder="انتخاب کنید..">
                            <option label="انتخاب کنید.."></option>
                            <option @if( isset($form['limitToDoctor']) && in_array(null,$form['limitToDoctor'])) selected @endif value="null">نمایش برای تمام پزشکان</option>
                            @if (isset($fetchData['doctors']))
                                @foreach ($fetchData['doctors'] as $doctor)
                                    <option @if(  isset($form['limitToDoctor']) && in_array($doctor->id,$form['limitToDoctor'])) selected @endif  value="{{ $doctor->id }}">
                                        {{ $doctor->fullName }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('form.limitToDoctor')
                            <span class="text-danger">
                                لطفا یک گزینه را انتخاب کنید
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-12 mb-5">
                    <label for="priority" class="form-label ms-3 mb-2">اولویت نمایش</label>
                    <input wire:model='form.priority' class="form-control  @error('form.priority') is-invalid @enderror"
                        id="priority" type="text">
                    @error('form.priority')
                        <div class="text-danger mt-2">
                            <i class="fa fa-exclamation-triangle ms-1 mt-1" aria-hidden="true"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-9">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="material-switch ms-5">
                                <input wire:model='form.active' id="active" name="siwtch04" type="checkbox"
                                    checked />
                                <label for="active" class="label-secondary"></label>
                            </div>
                            <p class="card-sub-title ms-3">فعال بودن</p>
                        </div>
                    </div>
                    <div class="col-3 d-flex justify-content-center">
                        <button wire:loading.class='btn-loading'
                            class="btn @if ($isEdited) btn-primary @else btn-success @endif"
                            wire:click='createOrUpdateMessageTemplate'>
                            @if ($isEdited)
                                ویرایش
                            @else
                                افزودن
                            @endif
                        </button>
                        @if ($isEdited)
                            <button class="btn btn-secondary ms-1" wire:click='ignoreSearch'>
                                بیخیال
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between">
                <a data-bs-toggle="collapse" href="#templist" role="button" aria-expanded="false"
                    aria-controls="templist">
                    <h5>لیست پیام های وارد شده</h5>
                </a>
                <div>
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#advanceSearch" aria-expanded="false" aria-controls="advanceSearch">
                        جست و جوی پیشرفته
                    </button>
                    @if (isset($search) && !empty($search))
                        <button class="btn btn-secondary ms-2" type="button" wire:click="resetProperties"
                            data-bs-toggle="collapse" data-bs-target="#advanceSearch" aria-expanded="false"
                            aria-controls="advanceSearch" wire:loading.class="bg-gray btn-loading disabled">نمایش
                            همه
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body collapse show" id="templist">
                <div class="mb-5 collapse @if (!empty($search)) show @endif" id="advanceSearch"
                    wire:ignore.self>
                    <form class="form-horizontal example" autocomplete="off">
                        <div class="row mb-4">
                            <label for="search-id" class="col-md-2 form-label">ایدی</label>
                            <div class="col-md-10">
                                <input class="form-control" id="search-id" wire:model="search.id"
                                    tempMessageholder="ایدی مطب مورد نظر" type="text">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="search-name" class="col-md-2 form-label">عنوان</label>
                            <div class="col-md-10">
                                <input class="form-control" id="search-name" wire:model="search.title"
                                    tempMessageholder="متن عنوان" type="text">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="form-label col-md-2" for="default-dropdown">وضعیت</label>
                            <div class="col-md-10">
                                <select wire:model='search.active' name="country" class="form-control form-select"
                                    id="default-dropdown" data-bs-tempMessageholder="انتخاب کنید..">
                                    <option label="انتخاب کنید"></option>
                                    <option value="{{ App\Enum\ActiveEnum::ACTIVE }}">
                                        {{ App\Enum\ActiveEnum::ACTIVE->getName() }}</option>
                                    <option value="{{ App\Enum\ActiveEnum::DEACTIVE }}">
                                        {{ App\Enum\ActiveEnum::DEACTIVE->getName() }}</option>

                                </select>
                            </div>
                        </div>
                        <button class="btn btn-info" type="button" wire:click="startSearch"
                            wire:loading.class="bg-gray btn-loading disabled">جست و
                            جو
                        </button>
                    </form>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table text-nowrap text-md-nowrap table-bordered text-center"
                        wire:loading.class="op-0-3">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">عنوان</th>
                                <th scope="col">متن</th>
                                <th scope="col">دارای ویس</th>
                                <th scope="col">دارای فایل</th>
                                <th scope="col">وضعیت</th>
                                <th scope="col">ترتیب نمایش</th>
                                <th scope="col">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($tempMessages->isNotEmpty())
                                @foreach ($tempMessages as $tempMessage)
                                    <tr class="text-center">
                                        <td>{{ $tempMessage->id }}</td>
                                        <td>{{ $tempMessage->title }}</td>
                                        <td>
                                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ $tempMessage->body }}">
                                                {{ substr($tempMessage->body, 0, 50) }} ...
                                            </span>
                                        </td>
                                        <td>
                                            <span class="{{ $tempMessage->hasVoiceBadge() }}">
                                                @if ($tempMessage->hasVoice())
                                                    <i class="fa fa-check" aria-hidden="true"></i>
                                                @else
                                                    <i class="fa fa-times" aria-hidden="true"></i>
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge @if ($tempMessage->hasFile()) bg-success
                                                @else
                                                bg-danger @endif">
                                                @if ($tempMessage->hasFile())
                                                    <i class="fa fa-check" aria-hidden="true"></i>
                                                @else
                                                    <i class="fa fa-times" aria-hidden="true"></i>
                                                @endif
                                            </span>
                                        </td>
                                        <td>{!! $tempMessage->active->getBadge() !!}</td>
                                        <td>{{ $tempMessage->priority }}</td>
                                        <td>
                                            @canany(['edit', 'delete'], $tempMessage)
                                                <div class="btn-group mt-2 mb-2">
                                                    <button type="button" class="btn btn-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        عملیات <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu" role="menu">
                                                        @can('delete', $tempMessage)
                                                            <li><a class="delete_confirm_alert" data-label="حذف "
                                                                    data-id="{{ $tempMessage->id }}" href="#">حذف</a>
                                                            </li>
                                                        @endcan
                                                        @can('edit', $tempMessage)
                                                            <li><a data-label="ویرایش"
                                                                    wire:click='editTemp("{{ $tempMessage->id }}")'>ویرایش</a>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            @else
                                                <div class="btn-group mt-2 mb-2">
                                                    <button type="button" class="btn btn-default dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        عملیات <span class="caret"></span>
                                                    </button>
                                                </div>
                                            @endcanany
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <div class="alert alert-info">
                                            هیچ موردی یافت نشد
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div>
                    {{ $tempMessages->links() }}
                </div>
            </div>
        </div>
    </div>
    <livewire:admin::file-manager-modal />
    <livewire:appointmentuser::admin.online.sound-recorder />
</div>
@push('scripts')
    <script src="{{ admin_asset('js/sound/recorder.js') }}"></script>
    <script src="{{ admin_asset('js/sound/Fr.voice.js') }}"></script>
    <script src="{{ admin_asset('js/sound/app.js') }}"></script>
    <script src="{{ admin_asset('plugins/select2/select2.full.min.js') }}"></script>
    <script src="{{ admin_asset('plugins/sweet-alert/sweetalert.min.js') }}"></script> --}}

    <script>
        $(document).ready(function() {
            Livewire.on('editMode', function() {
                setTimeout(() => {
                    var myCollapse = document.getElementById('createTempColl')
                    var bsCollapse = new bootstrap.Collapse(myCollapse, {
                        show: true
                    })
                }, 100);
            });
            Livewire.on('fileHasUpload', function() {
                var myModalEl = document.getElementById('soundRecorderModal');
                var modalsound = bootstrap.Modal.getInstance(myModalEl);
                modalsound.hide();
            });
            Livewire.on('select_file', (param) => {
                @this.set('form.file', param.url);
                $('#file-selector-modal').modal('hide');
            });
            $(document).on("click", "#save:not(.disabled)", function() {
                function upload(blob) {
                    var formData = new FormData();
                    formData.append('file', blob);
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    });
                    $.ajax({
                        url: "/admin/appointment_user/storevoice",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(url) {
                            @this.set('form.voice', url)
                            @this.dispatch('fileHasUpload');
                            $("#audio").attr("src", url);
                            $("#secound_loading").removeClass('d-block').addClass('d-none');
                        }
                    });
                }
                if ($(this).parent().data("type") === "mp3") {
                    Fr.voice.exportMP3(upload, "blob");
                } else {
                    $("#secound_loading").removeClass('d-none').addClass('d-block');
                    Fr.voice.export(upload, "blob");
                }
            });

            function js() {
                $('.select2-show-search').select2();
                $('body').on('change', '.select2-show-search', function() {
                    @this.set('form.limitToDoctor',$(this).val());
                });
            }
            js();
            Livewire.on('loadJs', function() {
                setTimeout(() => {
                    js();
                }, 500);
            });
        });
    </script>
@endpush
