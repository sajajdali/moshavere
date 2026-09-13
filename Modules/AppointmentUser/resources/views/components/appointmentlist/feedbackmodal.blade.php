<div>
    <style>
        #feedBackModal .modal-dialog{max-width:680px}#feedBackModal .modal-content{overflow:hidden;border:0;border-radius:22px;background:#f8fafc;box-shadow:0 28px 80px rgba(15,23,42,.24)}
        #feedBackModal .feedback-hero{padding:24px 26px;color:#fff;background:linear-gradient(135deg,#4f46e5,#7c3aed 58%,#a855f7)}#feedBackModal .feedback-hero-content{display:flex;align-items:center;gap:14px}
        #feedBackModal .feedback-hero-icon{display:flex;align-items:center;justify-content:center;width:48px;height:48px;flex:none;border-radius:15px;background:rgba(255,255,255,.17);font-size:21px}#feedBackModal .feedback-hero h5{margin:0;color:#fff;font-size:18px;font-weight:800}#feedBackModal .feedback-hero p{margin:5px 0 0;color:rgba(255,255,255,.82);font-size:12.5px}
        #feedBackModal .feedback-close{margin-right:auto;width:36px;height:36px;border:0;border-radius:11px;background:rgba(255,255,255,.15);color:#fff;font-size:18px}#feedBackModal .modal-body{max-height:min(68vh,620px);overflow-y:auto;padding:22px}
        #feedBackModal .feedback-summary{display:flex;align-items:center;gap:12px;margin-bottom:15px;padding:13px 15px;border:1px solid #e4e7ec;border-radius:14px;background:#fff}#feedBackModal .feedback-summary-icon{display:flex;align-items:center;justify-content:center;width:38px;height:38px;flex:none;border-radius:11px;background:#fff7ed;color:#f59e0b;font-size:17px}#feedBackModal .feedback-summary strong{display:block;color:#1d2939;font-size:13.5px}#feedBackModal .feedback-summary small{display:block;margin-top:2px;color:#667085;font-size:11.5px}
        #feedBackModal .feedback-list{display:flex;flex-direction:column;gap:10px}#feedBackModal .feedback-item{display:grid;grid-template-columns:34px 1fr;gap:11px;padding:15px;border:1px solid #e4e7ec;border-radius:14px;background:#fff;box-shadow:0 1px 2px rgba(16,24,40,.03)}#feedBackModal .feedback-number{display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:9px;background:#eef2ff;color:#4f46e5;font-size:12px;font-weight:800}
        #feedBackModal .feedback-question{margin:0 0 8px;color:#344054;font-size:13px;font-weight:700;line-height:1.8}#feedBackModal .feedback-answer{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:9px;background:#f0fdf4;color:#15803d;font-size:12px;font-weight:700}#feedBackModal .feedback-score{display:flex;align-items:center;gap:3px;direction:ltr}#feedBackModal .feedback-score i{color:#d0d5dd;font-size:16px}#feedBackModal .feedback-score i.is-active{color:#f59e0b}#feedBackModal .feedback-score b{margin-left:6px;color:#475467;font-size:12px;direction:rtl}
        #feedBackModal .feedback-voice{margin-top:14px;padding:16px;border:1px solid #ddd6fe;border-radius:14px;background:linear-gradient(135deg,#faf5ff,#fff)}#feedBackModal .feedback-voice-head{display:flex;align-items:center;gap:10px;margin-bottom:12px;color:#6d28d9}#feedBackModal .feedback-voice-head i{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;background:#ede9fe}#feedBackModal .feedback-voice audio{display:block;width:100%;height:42px}
        #feedBackModal .feedback-empty{padding:35px 15px;text-align:center;color:#667085}#feedBackModal .feedback-empty i{display:block;margin-bottom:10px;color:#c7d2fe;font-size:38px}#feedBackModal .modal-footer{padding:14px 22px;border-top:1px solid #eaecf0;background:#fff}#feedBackModal .feedback-dismiss{min-width:100px;height:38px;border:0;border-radius:10px;background:#4f46e5;color:#fff;font-size:13px;font-weight:700}
    </style>
    <div wire:ignore.self class="modal fade" id="feedBackModal" tabindex="-1" aria-labelledby="feedBackModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="feedback-hero"><div class="feedback-hero-content"><span class="feedback-hero-icon"><i class="fa fa-star"></i></span><div><h5 id="feedBackModalLabel">نتیجه نظرسنجی بیمار</h5><p>بازخورد ثبت‌شده برای این نوبت</p></div><button type="button" class="feedback-close" data-bs-dismiss="modal" aria-label="بستن"><i class="fa fa-times"></i></button></div></div>
        <div class="modal-body">
            @php
                $feedbackItems = collect($fetchData['feedbacks'] ?? []);
            @endphp
            @if ($feedbackItems->isNotEmpty())
                <div class="feedback-summary"><span class="feedback-summary-icon"><i class="fa fa-commenting"></i></span><div><strong>{{ $feedbackItems->count() }} پاسخ ثبت شده</strong><small>جزئیات پاسخ‌های بیمار را در ادامه مشاهده می‌کنید.</small></div></div>
                <div class="feedback-list">@foreach ($feedbackItems as $feedback)
                    @php
                        $feedbackQuestion = Modules\Front\enum\FeedbackId::tryFrom((int) $feedback->question);
                        $feedbackChoices = $feedbackQuestion?->getQuestionChoises() ?? [];
                        $feedbackChoice = $feedbackChoices[$feedback->answer] ?? null;
                        $score = max(0, min(5, (int) $feedback->answer));
                    @endphp
                    <div class="feedback-item"><span class="feedback-number">{{ $loop->iteration }}</span><div><p class="feedback-question">{{ $feedbackQuestion?->getQuestion() ?? 'سؤال نظرسنجی' }}</p>
                        @if ($fetchData['feedbackIsVoip'] ?? false)<div class="feedback-score" aria-label="امتیاز {{ $score }} از ۵">@for($star = 1; $star <= 5; $star++)<i class="fa fa-star {{ $star <= $score ? 'is-active' : '' }}"></i>@endfor<b>{{ $score }} از ۵</b></div>
                        @else<span class="feedback-answer"><i class="fa fa-check-circle"></i>{{ $feedbackChoice ?? $feedback->answer }}</span>@endif
                    </div></div>
                @endforeach</div>
            @elseif (empty($fetchData['feedbackVoiceUrl']))<div class="feedback-empty"><i class="fa fa-comment-o"></i><strong>پاسخی برای این نظرسنجی ثبت نشده است.</strong></div>@endif
            @if (! empty($fetchData['feedbackVoiceUrl']))<div class="feedback-voice"><div class="feedback-voice-head"><i class="fa fa-microphone"></i><strong>پیام صوتی بیمار</strong></div><audio controls preload="metadata" aria-label="پخش صدای ضبط‌شده بیمار"><source src="{{ $fetchData['feedbackVoiceUrl'] }}" type="audio/wav">مرورگر شما امکان پخش صدا را ندارد.</audio></div>@endif
        </div>
        <div class="modal-footer"><button type="button" class="feedback-dismiss" data-bs-dismiss="modal">بستن</button></div>
    </div></div></div>
</div>
