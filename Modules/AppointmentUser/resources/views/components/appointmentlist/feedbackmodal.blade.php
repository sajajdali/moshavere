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
                        @php
                            $feedbackQuestion = Modules\Front\enum\FeedbackId::tryFrom((int) $feedback->question);
                            $feedbackChoices = $feedbackQuestion?->getQuestionChoises() ?? [];
                            $feedbackChoice = $feedbackChoices[$feedback->answer] ?? null;
                        @endphp
                        <div class="row">
                            <div class="card">
                                <div class="card-header">
                                    <p>
                                        {{ $feedbackQuestion?->getQuestion() ?? 'سوال نظرسنجی' }}</p>

                                </div>
                                <div class="card-body">
                                    @if ($fetchData['feedbackIsVoip'] ?? false)
                                        <p class="mb-0">امتیاز ثبت شده: {{ $feedback->answer }}</p>
                                    @else
                                        <p class="mb-0">{{ $feedbackChoice ?? $feedback->answer }}</p>
                                    @endif
                                </div>
                            </div>
                            @if (! $loop->last)
                            <div class="col-12 border-bottom my-2 opacity-25"></div>
                            @endif
                        </div>
                        @endforeach
                    @endif
                    @if (! empty($fetchData['feedbackVoiceUrl']))
                        <div class="card mt-3">
                            <div class="card-header">
                                <p class="mb-0">صدای ضبط شده کاربر</p>
                            </div>
                            <div class="card-body">
                                <audio class="w-100" controls preload="metadata"
                                    aria-label="پخش صدای ضبط شده کاربر">
                                    <source src="{{ $fetchData['feedbackVoiceUrl'] }}" type="audio/wav">
                                    مرورگر شما امکان پخش صدا را ندارد.
                                </audio>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>
</div>
