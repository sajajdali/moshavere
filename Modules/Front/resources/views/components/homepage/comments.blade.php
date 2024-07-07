<div>
    <section class="py-12 bg-secondary-100">
        <div class="container space-y-8">
           <div class="flex flex-row items-center justify-between gap-3">
              <h3 class="font-bold text-xl">نظر مخاطبین</h3>
              {{-- <a href="#" class="flex items-center gap-4 text-primary-main">
                 <span class="font-semibold">مشاهده همه نظرات</span>
                 <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="#sprite-arrow-left" />
                 </svg>
              </a> --}}
           </div>
          
           <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($fetchData['comments'] as $key => $comment)
              <article role="banner" class="flex flex-col gap-5 rounded-xl bg-white p-4">
                 <p class="text-black font-semibold text-center">{{$comment->user?->full_name ?? 'کاربر'}}</p>
                 <p class="text-secondary-400">
                 {!!nl2br($comment->body)!!}
                 </p>
                 <div class="flex items-center justify-between text-sm">
                    <p>{{verta($comment->star)->format('%d %b')}}</p>
                    <div class="flex items-center gap-2">
                       <span>{{$comment->star}}</span>
                       <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                          <use xlink:href="#sprite-star-full" />
                       </svg>
                    </div>
                 </div>
              </article>
            @endforeach
           </div>
        </div>
     </section>
</div>
