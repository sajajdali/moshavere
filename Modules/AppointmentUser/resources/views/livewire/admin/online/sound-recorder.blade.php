<div class="modal fade" id="soundRecorderModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">ضبط صدا</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
              </div>
            <div id="recording">
                <div class="container mt-5 text-center">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-12">
                                    <audio controls id="audio" class="w-100"></audio>
                                </div>
                            </div>
                            <div class="row pt-2">
                                <div class="col-12">
                                    <a class="button recordButton btn btn-danger p-3 w-30" id="record">شروع ضبط</a>
                                    <a class="button disabled one btn btn-warning p-3 w-30" id="pause">توقف</a>
                                    <a class="button disabled one btn btn-info p-3 w-30" id="stop">Reset</a>
                                </div>
                            </div>

                            <div class="row pt-5">
                                <div class="col-12">
                                    <div data-type="wav">
                                        <a class="button disabled one btn btn-primary p-3 w-30" id="play">پخش</a>
                                        <a class="button disabled one btn btn-primary p-3 w-30"
                                            id="download">دانلود</a>
                                        <a class="button disabled one btn btn-primary p-3 w-30"
                                            id="save">ارسال ویس</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <canvas id="level" height="100" width="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
