@if (session()->has('error'))
    <div class="bg-red text-white text-lg max-w-3xl text-center py-3 px-5 rounded-lg mb-5 mx-auto">
        {{ session()->get('error') }}
    </div>
@endif
@if (session()->has('success'))
    <div class="bg-emerald-200 text-gray-500 text-lg max-w-3xl text-center py-3 px-5 rounded-lg mb-5 mx-auto">
        {{ session()->get('success') }}
    </div>
@endif
