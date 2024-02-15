<div>
    <div class="modal fade" id="changeDocmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">تغییر پزشک</h5>
                    <div class="modal-title">
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model='search.doctors'
                                placeholder="نام خانوادگی پزشک">
                            <button wire:click='searchDoctors'
                                class="btn ripple btn-info text-fixed-white input-group-text border-0" type="button">
                                <span wire:target='searchDoctors' wire:loading.remove>جست و جو</span>
                                <span wire:target='searchDoctors' wire:loading class="spinner-border spinner-border-sm"
                                    role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <a href="#">
                                        <i class="fa fa-user-md fa-2x text-primary me-3" aria-hidden="true"></i>
                                        <span style="font-size: medium">دکتر قاسمی</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    {{-- refresh data of the page with selection of the doctor with wire:click--}}
                                    <a href="#">
                                        <i class="fa fa-user-md fa-2x text-primary me-3" aria-hidden="true"></i>
                                        <span style="font-size: medium">دکتر ممد</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بیخیال</button>
                </div>
            </div>
        </div>
    </div>
</div>
