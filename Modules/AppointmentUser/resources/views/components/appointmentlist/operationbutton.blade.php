@if ($ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_PENDING)
    <li>
        <a wire:click='ApproveOnlineAppointment({{ $ap->id }})' href="#" data-label="ویرایش">
            <i class="fa fa-check text-success" aria-hidden="true"></i>
            تایید نوبت
        </a>
    </li>
    <li>
        <a wire:click='disApproveOnlineAppointment({{ $ap->id }})' href="#" data-label="ویرایش">
            <i class="fa fa-ban text-danger" aria-hidden="true"></i>
            عدم تایید نوبت
        </a>
    </li>
@elseif($ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_MONITORING)
    <li>
        <a wire:click='ApprovemonitoringAppointment({{ $ap->id }})' href="#" data-label="ویرایش">
            <i class="fa fa-check text-success" aria-hidden="true"></i>
            تایید نوبت
        </a>
    </li>
    <li>
        <a wire:click='disApprovemonitoringAppointment({{ $ap->id }})' href="#" data-label="ویرایش">
            <i class="fa fa-ban text-danger" aria-hidden="true"></i>
            عدم تایید نوبت
        </a>
    </li>
@elseif(
    $ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_DISAPPROVED ||
        $ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL)
@else
    <li>
        <a wire:click='editAppointment("{{ $ap->id }}")' href="#">
            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
            ویرایش</a>
    </li>
    @if (setting(Modules\Setting\Enum\SettingKeyEnum::APPOINTMENT_USER_PERESENT_STATUS_REGISTRATION))
        @if (isset($ap->details[\Modules\AppointmentUser\app\Models\AppointmentUser::USRE_ATTENDED_STATUS]))
            @if ($ap->details[\Modules\AppointmentUser\app\Models\AppointmentUser::USRE_ATTENDED_STATUS])
                <li>
                    <a wire:click='userNotAttenedToAppointment({{ $ap->id }})' href="#">
                        <i class="fa fa-user-times text-danger" aria-hidden="true"></i>
                        عدم حضور بیمار
                    </a>
                </li>
            @else
                <li>
                    <a wire:click='userAttenedToAppointment({{ $ap->id }})' href="#">
                        <i class="fa fa-user-plus text-success" aria-hidden="true"></i>
                        حضور بیمار
                    </a>
                </li>
            @endif
        @else
            <li>
                <a wire:click='userNotAttenedToAppointment({{ $ap->id }})' href="#">
                    <i class="fa fa-user-times text-danger" aria-hidden="true"></i>
                    عدم حضور بیمار
                </a>
            </li>
            <li>
                <a wire:click='userAttenedToAppointment({{ $ap->id }})' href="#">
                    <i class="fa fa-user-plus text-success" aria-hidden="true"></i>
                    حضور بیمار
                </a>
            </li>
        @endif
    @endif
    @if ($ap->type !== Modules\AppointmentUser\Enum\AppointmentUserTypeEnum::BETWEEN_PATIENTS)
        <li><a data-description="میخواهید نوبت به بین مریض تبدیل شود؟" data-title="تغییر وضعیت "
                data-confirmbtn="بله تغییر کند" data-action="changeType" data-id="{{ $ap->id }}"
                class="confirm_swal_alert" data-label="نوبت" href="">
                <i class="fa fa-retweet" aria-hidden="true"></i>
                تبدیل
                به نوبت بین مریض</a>
        </li>
    @endif
    <li>
        <a class="confirm_swal_alert" data-label="نوبت" data-description="از کنسل کردن نوبت مطمعن هستید؟"
            data-title="کنسل کردن " data-confirmbtn="بله کنسل شود" data-action="cancelWithSms"
            data-id="{{ $ap->id }}" data-id="{{ $ap->id }}" href="">
            <i class="fa fa-envelope-o" aria-hidden="true"></i>
            کنسل
            کردن <small>(با ارسال پیامک)</small>
        </a>
    </li>
    <li><a class="confirm_swal_alert" data-label="نوبت" data-description="از کنسل کردن نوبت مطمعن هستید؟"
            data-title="کنسل کردن " data-confirmbtn="بله کنسل شود"
            data-action="cancelWithOutSms"
        data-id="{{ $ap->id }}" href="">
            <i class="fa fa-times" aria-hidden="true"></i>
            کنسل
            کردن <small>(بدون ارسال پیامک)</small></a>
    </li>
@endif
@can('delete', $ap)
    <li>
        @php
            if (
                $ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_CANCEL ||
                $ap->status == Modules\AppointmentUser\Enum\AppointmentUserStatusEnum::STATUS_DISAPPROVED
            ) {
                $title = 'حذف  کردن';
            } else {
                $title = 'کنسل و حذف  کردن';
            }
        @endphp
        <a class="confirm_swal_alert" data-label="نوبت" data-description="از {{ $title }} نوبت مطمعن هستید؟"
            data-title="{{ $title }}" data-confirmbtn="بله {{ $title }}" data-action="delete"
            data-id="{{ $ap->id }}" href="">
            <i class="fa fa-trash text-danger" aria-hidden="true"></i>
            {{ $title }}
        </a>
    </li>
@endcan
