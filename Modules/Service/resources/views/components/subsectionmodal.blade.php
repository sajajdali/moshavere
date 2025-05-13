<div>
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" wire:ignore.self
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">زیر بخش های ویزیت</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: scroll !important; max-height: 500px;">
                    <div class="row d-flex justify-content-center">
                        <span wire:loading class="spinner-border text-primary" role="status"></span>
                    </div>
                    @if (isset($fetchData['modal']))
                        @foreach ($fetchData['modal'] as $subService)
                            <div class="card">
                                <div class="card-body d-flex justify-content-between align-items-center py-2">
                                    <h4> {{ $subService->title }}</h4>
                                    <div class="btn-group mt-2 mb-2">
                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            عملیات <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            @can('delete', $subService)
                                                <li><a class="delete_confirm_alert" data-label="حذف " data-id="3"
                                                        href="#">حذف</a>
                                                </li>
                                            @endcan
                                            @can('update', $subService)
                                                <li><a href="{{ route('admin.service.edit', ['service' => $subService->id]) }}"
                                                        data-label="ویرایش">ویرایش</a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بیخیال</button>
                </div>
            </div>
        </div>
    </div>
</div>
