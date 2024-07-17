<!-- JQUERY JS -->
<script src="{{ admin_asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ front_asset('js/main.js') }}"></script>
<script src="{{ front_asset('js/main.js') }}"></script>
<script>
    @if (session()->has('authsuccess'))
        var msg = @json(session()->get('authsuccess'));
        Swal.fire({
            position: "center",
            icon: "success",
            title: msg,
            showConfirmButton: false,
            timer: 1500
        });
    @endif
</script>
