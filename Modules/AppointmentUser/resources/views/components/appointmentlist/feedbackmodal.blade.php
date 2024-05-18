<div>
    <div class="modal fade" id="feedBackModal" tabindex="-1" aria-labelledby="feedBackModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedBackModalLabel">نتیجه ی نظرسنجی</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (isset($fetchData['feedbacks']))
                        @foreach ($fetchData['feedbacks'] as $feedback)
                        <div class="row">
                            <div class="card">
                                <div class="card-header">
                                    <p>
                                        {{ Modules\Front\Enum\FeedbackId::tryFrom($feedback->question)->getQuestion() }}</p>

                                </div>
                                <div class="card-body">
                                    <p>{{ Modules\Front\Enum\FeedbackId::tryFrom($feedback->question)->getQuestionChoises()[$feedback->answer] }}
                                </div>
                            </div>
                            @if (! $loop->last)
                            <div class="col-12 border-bottom my-2 opacity-25"></div>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>
</div>
