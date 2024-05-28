function showSwalSuccess(title, text) {
    swal({
        title: title,
        text: text,
        buttonsStyling: false,
        confirmButtonClass: "btn btn-success",
        type: "success",
        confirmButtonText: "باشه",
        timer: 1500
    });
}

$(function (e) {
    $('body').on('click', '.delete_confirm_alert', function (e) {
        e.preventDefault();
        let $label = $(this).data('label');
        let $id = $(this).data('id');
        let $title = $(this).data('title') ? $(this).data('title') : "از حذف این مورد اطمینان دارید؟";
        let $description = $(this).data('description') || ("آیا می‌خواهید " + $label + " را حذف کنید؟");
        let $confirmBtn = $(this).data('confirmbtn') ? $(this).data('confirmbtn') : "بله حذف شود";
        swal({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false,
                title: $title,
                text: $description,
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn btn-danger",
                confirmButtonText: $confirmBtn,
                cancelButtonText: "خیر",
                closeOnConfirm: true
            },
            function () {
                Livewire.dispatch('delete', {model: $id});
                //show loading animation

            });
    });
    $('body').on('click', '.confirm_swal_alert', function (e) {
        e.preventDefault();
        let $label = $(this).data('label');
        let $action = $(this).data('action') ?? '' ;
        let $id = $(this).data('id') ?? 0 ;
        let $title = $(this).data('title') ? $(this).data('title') : "از حذف این مورد اطمینان دارید؟";
        let $description = $(this).data('description') || ("آیا می‌خواهید " + $label + " را حذف کنید؟");
        let $confirmBtn = $(this).data('confirmbtn') ? $(this).data('confirmbtn') : "بله حذف شود";
        swal({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false,
                title: $title,
                text: $description,
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn btn-danger",
                confirmButtonText: $confirmBtn,
                cancelButtonText: "خیر",
                closeOnConfirm: true
            },
            function () {
                Livewire.dispatch('confirm_swal',{action: $action,model:$id});
                //show loading animation

            });
    });

    $('.admin_sweet_alert').click(function (e) {
        e.preventDefault();
        let $title = $(this).data('title');
        let $description = $(this).data('description');
        let $type = $(this).data('type');
        $('body').removeClass('timer-alert');
        swal({
            title: "" + $title,
            text: "" + $description,
            type: "" + $type,
            showCancelButton: true,
            allowOutsideClick: true,
            showConfirmButton: false,
            cancelButtonText: "متوجه شدم",
            closeOnConfirm: false
        });
    });
});
