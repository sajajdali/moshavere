<section class="dashboard__main" id="jibitCredit" wire:ignore.self>
    <div class="flex flex-col gap-4">

        <div class="bg-white p-4 rounded-lg space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h3 class="font-bold text-slate-800">هم اکنون غیر فعال میباشد.</h3>
        </div>
        {{-- <!-- Header / Balance -->
        <div class="bg-white p-4 rounded-lg space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h3 class="font-bold text-slate-800">اعتبار جت پی</h3>

                <!-- Optional: quick action button -->
                <button type="button" class="btn__blue--round-full px-4 py-2 text-sm">
                    افزایش اعتبار
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Current Balance -->
                <div class="border border-secondary-300 rounded-lg bg-primary-tint-100 p-4">
                    <div class="text-sm text-slate-600">اعتبار فعلی</div>
                    <div class="mt-2 text-xl font-extrabold text-slate-800">
                        15,222,222 ریال
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        آخرین بروزرسانی: ۱۴۰۴/۱۲/۰۵ - ۱۳:۲۲
                    </div>
                </div>

                <!-- Total Credit (example) -->
                <div class="border border-secondary-300 rounded-lg bg-white p-4">
                    <div class="text-sm text-slate-600">مجموع شارژها (۳۰ روز اخیر)</div>
                    <div class="mt-2 text-lg font-bold text-emerald-600">
                        + 8,500,000 ریال
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        تعداد تراکنش: 6
                    </div>
                </div>

                <!-- Total Spend (example) -->
                <div class="border border-secondary-300 rounded-lg bg-white p-4">
                    <div class="text-sm text-slate-600">مجموع خریدها (۳۰ روز اخیر)</div>
                    <div class="mt-2 text-lg font-bold text-rose-600">
                        - 3,120,000 ریال
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        تعداد تراکنش: 9
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg space-y-4">
            <div class="flex flex-col lg:flex-row gap-3 lg:items-end lg:justify-between">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full">
                    <!-- Search -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700">جستجو</label>
                        <input type="text"
                               class="w-full border border-secondary-300 rounded-lg bg-white py-2 px-3 text-sm"
                               placeholder="شماره تراکنش، توضیح، کلید..."
                               value="">
                    </div>

                    <!-- Type -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700">نوع</label>
                        <select class="w-full border border-secondary-300 rounded-lg bg-white py-2 px-3 text-sm">
                            <option value="">همه</option>
                            <option value="credit">شارژ</option>
                            <option value="purchase">خرید</option>
                            <option value="refund">بازگشت وجه</option>
                            <option value="admin_adjustment">اصلاح ادمین</option>
                        </select>
                    </div>

                    <!-- Date range (simple placeholders) -->
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-slate-700">بازه زمانی</label>
                        <select class="w-full border border-secondary-300 rounded-lg bg-white py-2 px-3 text-sm">
                            <option>۷ روز اخیر</option>
                            <option selected>۳۰ روز اخیر</option>
                            <option>۹۰ روز اخیر</option>
                            <option>همه</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" class="btn__blue--round-full px-4 py-2 text-sm">
                        اعمال فیلتر
                    </button>
                    <button type="button"
                            class="px-4 py-2 text-sm rounded-full border border-secondary-300 text-slate-700 hover:bg-slate-50">
                        پاک کردن
                    </button>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white p-4 rounded-lg space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-slate-800">لیست تراکنش‌ها</h4>
                <div class="text-xs text-slate-500">نمایش ۱ تا ۱۰ از ۳۲</div>
            </div>

            <div class="overflow-x-auto border border-secondary-200 rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr class="text-right">
                            <th class="p-3 whitespace-nowrap">تاریخ</th>
                            <th class="p-3 whitespace-nowrap">شناسه</th>
                            <th class="p-3 whitespace-nowrap">نوع</th>
                            <th class="p-3 whitespace-nowrap">تغییر مبلغ</th>
                            <th class="p-3 whitespace-nowrap">قبل</th>
                            <th class="p-3 whitespace-nowrap">بعد</th>
                            <th class="p-3 whitespace-nowrap">جزئیات</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-secondary-200">
                        <!-- Row 1: credit (+) -->
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 whitespace-nowrap text-slate-700">۱۴۰۴/۱۲/۰۵ - ۱۳:۲۲</td>
                            <td class="p-3 whitespace-nowrap">
                                <div class="font-mono text-xs text-slate-700">#10291</div>
                                <div class="text-[11px] text-slate-400">txn_id: 88421</div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    شارژ
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap font-bold text-emerald-600">+ 5,000,000 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-700">10,222,222 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-900 font-semibold">15,222,222 ریال</td>
                            <td class="p-3">
                                <div class="text-slate-700">واریز از بانک</div>
                                <div class="text-[11px] text-slate-400 font-mono">idem: JBT-2026-00091</div>
                            </td>
                        </tr>

                        <!-- Row 2: purchase (-) -->
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 whitespace-nowrap text-slate-700">۱۴۰۴/۱۲/۰۴ - ۱۹:۱۰</td>
                            <td class="p-3 whitespace-nowrap">
                                <div class="font-mono text-xs text-slate-700">#10290</div>
                                <div class="text-[11px] text-slate-400">txn_id: 88377</div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200">
                                    خرید
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap font-bold text-rose-600">- 320,000 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-700">10,542,222 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-900 font-semibold">10,222,222 ریال</td>
                            <td class="p-3">
                                <div class="text-slate-700">خرید بسته ویژه</div>
                                <div class="text-[11px] text-slate-400">order: #A-5512</div>
                            </td>
                        </tr>

                        <!-- Row 3: refund (+) -->
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 whitespace-nowrap text-slate-700">۱۴۰۴/۱۲/۰۳ - ۰۹:۴۲</td>
                            <td class="p-3 whitespace-nowrap">
                                <div class="font-mono text-xs text-slate-700">#10276</div>
                                <div class="text-[11px] text-slate-400">txn_id: 88001</div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-sky-50 text-sky-700 border border-sky-200">
                                    بازگشت وجه
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap font-bold text-emerald-600">+ 120,000 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-700">10,102,222 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-900 font-semibold">10,222,222 ریال</td>
                            <td class="p-3">
                                <div class="text-slate-700">لغو سفارش</div>
                                <div class="text-[11px] text-slate-400">order: #A-5410</div>
                            </td>
                        </tr>

                        <!-- Row 4: admin_adjustment (+/-) -->
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 whitespace-nowrap text-slate-700">۱۴۰۴/۱۲/۰۱ - ۱۵:۰۰</td>
                            <td class="p-3 whitespace-nowrap">
                                <div class="font-mono text-xs text-slate-700">#10220</div>
                                <div class="text-[11px] text-slate-400">txn_id: —</div>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200">
                                    اصلاح ادمین
                                </span>
                            </td>
                            <td class="p-3 whitespace-nowrap font-bold text-rose-600">- 50,000 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-700">10,152,222 ریال</td>
                            <td class="p-3 whitespace-nowrap text-slate-900 font-semibold">10,102,222 ریال</td>
                            <td class="p-3">
                                <div class="text-slate-700">تصحیح مانده</div>
                                <div class="text-[11px] text-slate-400 font-mono">note: manual correction</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (placeholder) -->
            <div class="flex items-center justify-between pt-2">
                <div class="text-xs text-slate-500">صفحه ۱ از ۴</div>
                <div class="flex gap-2">
                    <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-secondary-300 hover:bg-slate-50">
                        قبلی
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm rounded-lg bg-slate-900 text-white">
                        ۱
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-secondary-300 hover:bg-slate-50">
                        ۲
                    </button>
                    <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-secondary-300 hover:bg-slate-50">
                        بعدی
                    </button>
                </div>
            </div>
        </div> --}}

    </div>
</section>
