<div>
    <!-- Modal -->
    <div class="modal fade" id="confirmabsenteeModal_1" tabindex="-1" aria-labelledby="confirmabsenteeModal"
        wire:ignore.self aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmabsenteeModal">انتخاب بخش</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="flex mt-5">
                        <button class="btn btn-info" wire:click='storeForAllSection'>
                            <span wire:loading.remove wire:target='storeForAllSection'>ذخیره تنظیمات برای تمامی بخش
                                ها</span>
                            <span wire:loading wire:target='storeForAllSection' class="spinner-border spinner-border-sm"
                                role="status" aria-hidden="true"></span>
                        </button>
                        <button class=" mt-3 mt-sm-0 btn btn-primary" data-bs-toggle="collapse"
                            data-bs-target="#selectSectionCollaps" aria-expanded="false"
                            aria-controls="selectSectionCollaps">ذخیره تنظیمات برای یک یا چند بخش</button>
                        <div class="collapse" id="selectSectionCollaps">
                            <div class="card card-body">
                                @foreach ($selectedDoctorsSection as $key => $section)
                                    <div class="col-md-4">
                                        <div class="form-group mt-2">
                                            <div class="checkbox">
                                                <div class="custom-checkbox custom-control">
                                                    <input type="checkbox"
                                                        wire:model='selectedSection.{{ $section['id'] }}'
                                                        data-checkboxes="mygroup" class="custom-control-input"
                                                        id="checkbox-{{ $key }}">
                                                    <label for="checkbox-{{ $key }}"
                                                        class="custom-control-label">{{ $section['title'] }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <p class="mt-3 mb-5"><span class="text-danger">نکته!!</span> در صورتی که مایل هستید تنظیمات برای
                            تمامی بخش های مربوط به این پزشک ذخیره شود ، روی گزینه ی تمامی بخش ها و در صورتی که مایل
                            هستید برای یک بخش تغییر بکند، گزینه ی ا اعمال برای چند بخش را انتخاب کنید</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بیخیال</button>
                    <button type="button" class="btn btn-success" wire:click='storeForSelectedsections'>ذخیره</button>
                </div>
            </div>
        </div>
    </div>
</div>
