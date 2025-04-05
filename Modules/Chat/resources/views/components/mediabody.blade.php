<div class="media-body">
    @if ($chatMessage->files()->count())
        @foreach ($chatMessage->files as $file)
            @if (in_array($file->mime, [
                    'jpg',
                    'image/png',
                    'jpeg',
                    'image/jpeg',
                    'png',
                    'image/png',
                    'gif',
                    'image/gif',
                    'webp',
                    'image/webp',
                    'bmp',
                    'image/bmp',
                    'svg',
                    'image/svg',
                    'tiff',
                    'image/tiff',
                    'heic',
                    'image/heic',
                    'heif',
                    'image/heif',
                ]))
                <a href="{{ Storage::url($file->disk . '/' . $file->path) }}"
                    data-fancybox="gallery"
                    data-caption="{{ $file->original_name }}">
                    <img src="{{ Storage::url($file->disk . '/' . $file->path) }}"
                        alt="{{ $file->original_name }}"
                        style="width: 100px; height: auto;" />
                </a>
            @elseif($file->mime == 'mp3' || $file->mime == 'wav')
                <audio
                    src="{{ Storage::url($file->disk . '/' . $file->path) }}"
                    controls preload="auto"></audio>
            @endif
            <div class="main-msg-wrapper">
                <a class="text-dark"
                    href="{{ Storage::url($file->disk . '/' . $file->path) }}">
                    <span class="fs-13 mt-1"> دانلود فایل
                    </span> <i
                        class="fe fe-download mt-3 ms-4 text-muted pe-2"></i>
                </a>
            </div>
        @endforeach
    @elseif($chatMessage->content)
        <div class="main-msg-wrapper">
            {{ $chatMessage->content }}
        </div>
    @endif
    <div>
        <span>{{ $chatMessage->created_at->format('H:i') }}</span>
    </div>
</div>
