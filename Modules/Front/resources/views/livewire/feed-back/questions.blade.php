<div>
    <div class="mt-5">
        <div class="row">
            <div class="col-md-12 d-flex justify-content-between align-items-center mb-4">
                <h2> نظر سنجی </h2>
            </div>
        </div>
        <div class="row" wire:loading.class='opacity-50'>
            <div class="col-lg-12">
                <div class="card custom-card rounded-20">
                    <div class="card-header  border-bottom d-flex justify-content-between align-items-center">
                        <h5>لطفا به سوالات زیر پاسخ دهید</h5>
                        <div>
                            <span class="badge bg-info rounded-20 font-xl">سوال
                                <strong>{{ $step +1 }}</strong>/{{ count($this->fetchData['questions'])}}</span>
                        </div>
                    </div>
                    <div class="card-body border-bottom">
                        @error('selectAwnser')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            لطفا یکی از گزینه ها را انتخاب کنید
                        </div>
                        @enderror
                        @if ($feedBackCompelete)
                        <div class="alert alert-default alert-dismissible fade show" role="alert">
                            پاسخ های شما ذخیره شد ، با تشکر از شرکت شما در نظر سنجی
                        </div>

                        @else
                            <div class="row">
                                <div class="col-12 mt-2 mb-3">
                                    <span>
                                        {{ $fetchData['questions'][$step]['question'] }}
                                    </span>
                                </div>
                                @foreach ($fetchData['questions'][$step]['choises'] as $key => $answers)
                                    <div class="col-12">
                                        <label class="selectgroup-item awnswer w-100">
                                            <input type="checkbox" class="selectgroup-input"
                                                {{-- wire:model="form.awnswers.{{ $step }}.{{ $key }}" --}}
                                                wire:click='nxtQuestion({{$key}})'
                                                wire:key={{ time() }}>
                                            <span class="selectgroup-button">{{ $answers }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if (!$feedBackCompelete)
                        <div class="card-fotter">
                            <div class="row my-5 mx-5">
                                <div class="col-12">
                                    <button wire:click='nxtQuestion(null)' wire:loading.class='btn-loading btn-gray'
                                        id="paymentButton" class="btn btn-primary w-100 ">
                                        @if (count($this->fetchData['questions']) == $step)
                                            ثبت
                                        @else
                                            ادامه
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
