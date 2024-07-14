  <section class="dashboard__main" id="myCommentSection" wire:ignore.self>
      <div class="bg-white p-4 space-y-5 rounded-lg">
          <h3 class="font-bold">لیست نظرات شما در این بخش قرار دارد</h3>
          <div class="flex flex-col gap-3">
              @if ($form['comments']->isNotEmpty())
                  <div class="bg-white rounded-b-lg p-4 space-y-4">
                      @foreach ($form['comments'] as $key => $comment)
                          <main class="flex flex-col gap-3">
                              <div class="border-2 border-secondary-200 rounded-lg flex flex-col gap-4 p-4">
                                  <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
                                      <div class="flex items-center gap-4">
                                          <div
                                              class="w-[65px] h-[65px] rounded-full flex items-center justify-center bg-primary-main text-white font-bold text-2xl">
                                              ن</div>
                                          <div class="w-[calc(100%-65px-0.75rem)] space-y-2">
                                              <p>{{ $comment->user->full_name }}</p>
                                              <div class="flex items-center gap-3 text-sm text-secondary-400">
                                                  <div class="hidden sm:block py-1 px-3 bg-secondary-100 rounded-full">
                                                      <p>{{ verta($comment->created_at)->formatDifference() }}</p>
                                                  </div>
                                                  <div class="w-[1px] h-3 bg-secondary-400"></div>
                                                  <p>{{ $comment->doctor->dr_display_address }}</p>
                                              </div>
                                          </div>
                                      </div>
                                      <div
                                          class="flex items-center gap-2 bg-green/20 text-green rounded-full py-1 px-4 text-sm font-bold">
                                          <p class="w-[calc(100%-1.75rem)]">{{ $comment->star }}</p>
                                          <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg">
                                              <use xlink:href="#sprite-star-full" />
                                          </svg>
                                      </div>
                                  </div>
                                  <p class="leading-7">{{ $comment->body }}</p>
                                  @if (isset($comment->reply))
                                      <div class="text-center border-r-4 border-blue-500  mr-3 text-start">
                                          <p class="mr-2"> {{ $comment->reply }}</p>
                                      </div>
                                  @endif
                              </div>
                          </main>
                      @endforeach
                  </div>
              @endif
          </div>
      </div>
  </section>
