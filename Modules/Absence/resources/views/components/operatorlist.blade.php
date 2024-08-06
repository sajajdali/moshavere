<div id="operatorCard">
    <div class="card">
        <div class="card-header border-bottom">
            <h3 class="card-title"> ثبت عدم حضور برای <strong>اپراتور</strong></h3>
            <div class="card-options">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse"
                    data-bs-target="#advanceSearchForoperator" aria-expanded="false"
                    aria-controls="advanceSearchForoperator">
                    جست و جوی پیشرفته
                </button>
                @if (isset($search['operator_name']) || isset($search['operator_mobile']))
                    <button class="btn btn-secondary ms-2" type="button" wire:click="resetPropertiesOperators"
                    data-bs-toggle="collapse"
                    data-bs-target="#advanceSearchForoperator" aria-expanded="false"
                    aria-controls="advanceSearchForoperator"
                        wire:loading.class="bg-gray btn-loading disabled">نمایش همه
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="mb-5 collapse @if (isset($search['operator_name']) || isset($search['operator_mobile'])) show @endif"
            id="advanceSearchForoperator" wire:ignore>
                    <div class="row mb-4">
                        <label for="search-id" class="col-md-2 form-label"> نام اپراتور</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-id" wire:model="search.operator_name"
                                absenteeholder="ایدی پزشک" type="text">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="search-id" class="col-md-2 form-label">شماره تلفن اپراتور</label>
                        <div class="col-md-10">
                            <input class="form-control" id="search-id" wire:model="search.operator_mobile"
                                absenteeholder="ایدی پزشک" type="text">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="button" wire:click="searchOperators"
                        wire:target='searchOperators' wire:loading.class="bg-gray btn-loading disabled">جست و
                        جو
                    </button>
            </div>
            <div>
                <div class="row">
                    @if ($operators->isNotEmpty())
                        <div class="d-flex mt-1 mb-3 align-items-center" wire:ignore>
                            <p style="font-size: medium" class="text-muted">
                                لطفا اپراتور و یا اپراتور هایی که مایل هستید برای آن عدم حضور را ثبت کنید، انتخاب کنید!
                            </p>
                        </div>
                        @error('form.operator')
                            <div class="alert alert-danger" role="alert">
                                <strong>خطا!! </strong>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                        @foreach ($operators as $key => $operator)
                            <div class="col-md-4" wire:ignore.self>
                                <div class="form-group mt-2">
                                    <div class="checkbox">
                                        <div class="custom-checkbox custom-control">
                                            <input type="checkbox" wire:model='form.operator.{{ $operator->id }}'
                                                data-id="operator-{{ $operator->id }}" data-for="operator" data-checkboxes="mygroup"
                                                class="custom-control-input" id="operatorbox-{{ $key }}">
                                            <label for="operatorbox-{{ $key }}"
                                                class="custom-control-label">{{ $operator->full_name }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-center mt-3">
                            {{$operators->links()}}
                        </div>
                    @else
                        <div class="col-12 alert alert-primary fade show w-100" role="alert">
                            <i class="fa fa-bell-o me-2 ms-1" aria-hidden="true"></i>
                            برای ثبت عدم حضور ، لازم هست که ابتدا پزشک به سیستم اضافه کنید!
                        </div>
                    @endif

                </div>
            </div>
            <div class="row">
                <div class="col-9"></div>
                <div class="col-3 text-end">
                    <button class="btn btn-warning" type="button" wire:click="AddStep('opreator')">
                        <span wire:loading.remove wire:target="AddStep('opreator')">مرحله بعد</span>
                        <span wire:loading wire:target="AddStep('opreator')" class="spinner-border spinner-border-sm"
                            role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
