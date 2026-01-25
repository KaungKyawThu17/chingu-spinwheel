<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="utf-8">
    <title>Survey Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        // Tailwind config (keep your background)
        tailwind.config = {
            theme: {
                extend: {
                    backgroundImage: {
                        'chingu-pattern': 'linear-gradient(to right, #55B9E6 0 20%, #E5243B 20% 40%, #52B848 40% 60%, #F7B7C4 60% 80%, #F8D7C0 80% 100%)'
                    }
                }
            }
        }
    </script>

    <style>
        /* You can still add custom CSS here if you want */
    </style>
</head>

<body class="min-h-screen bg-chingu-pattern flex justify-center items-start py-4 px-3 sm:px-4 text-base md:text-lg">

    <div class="w-full max-w-lg sm:max-w-xl mx-auto">
        <!-- Popup Overlay -->
        <div id="introPopup"
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 @if ($errors->any()) hidden @endif">

            <div class="bg-white w-11/12 max-w-md p-5 sm:p-7 rounded-2xl shadow-2xl text-center">
                <div class="relative flex flex-col items-center mb-8 justify-center">

                    <img src="{{ asset('images/chingulogo.jpg') }}"
                        class="w-28 sm:w-32 object-contain mb-2 relative z-10" />
                </div>
                <h2 class="text-xl sm:text-2xl font-bold mb-3 text-green-700">Chingu Soju Survey</h2>

                <p class="text-gray-700 leading-relaxed mb-3 text-sm sm:text-base">
                    ကျွန်ုပ်တို့ Chingu Soju နဲ့ပတ်သက်လို့ လေ့လာမှုတစ်ခု ပြုလုပ်နေပါတယ်။ အဲဒါနဲ့ပတ်သက်လို့ မေးခွန်း
                    အနည်းငယ်ဖြေပြီး ပွိုင့်တွေ စုကာ ဆုလက်ဆောင်ကံစမ်း ခွင့်တွေပါဝင်ကံစမ်းလိုက်ပါ။ ဖြေဆိုရန်ကြာချိန်ကတော့
                    ၃ မိနစ်ခန့်ကြာမြင့်မှာဖြစ်ပါတယ်။
                </p>

                <p class="text-gray-600 leading-relaxed mb-5 text-sm sm:text-base">
                    သင့်ရဲ့ထင်မြင်ချက်တွေဟာ ကျွန်တော်/မတို့အတွက် အဖိုးထိုက်တန်ပြီးတော့ သုတေသနရည်ရွယ်ချက်အတွက်
                    ဒီအချက်အလက်တွေကို အသုံးပြုမှာပါ။ ဒီအချက်အလက်တွေကို ကျွန်တော်/မတို့ရဲ့အထက်အရာရှိများထံ တင်ပြ
                    အစီရင်ခံဖို့သာဖြစ်ပြီး ပုဂ္ဂိုလ်ရေးအချက်အလက်များကို ဖော်ထုတ်ပြောကြားမှာမဟုတ်ဘဲ
                    လျှို့ဝှက်စွာသိမ်းဆည်းထားမှာဖြစ်ပါတယ်။ ပါဝင်ဖြေကြားပေးမှုအတွက်ကျေးဇူးအထူးတင်ပါသည်။
                </p>

                <button id="closeIntroPopup"
                    class="w-full bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 active:scale-[0.99] transition">
                    Survey ဖြေဆိုရန်
                </button>
            </div>
        </div>

        <!-- Main Card -->
        <div
            class="bg-white/95 backdrop-blur border border-white/70 rounded-2xl px-4 sm:px-6 md:px-8 py-6 sm:py-8 mx-auto my-6 sm:my-10 shadow-lg">

            <!-- Logo -->
            <div class="relative flex flex-col items-center mb-8 justify-center">
                <div class="absolute w-32 h-32 sm:w-40 sm:h-40 bg-white/30 blur-2xl rounded-full"></div>
                <img src="{{ asset('images/chingulogo.jpg') }}"
                    class="w-28 sm:w-32 object-contain mb-2 relative z-10" />
            </div>

            {{-- You can log errors in controller instead of here --}}
            @if ($errors->any())
                @php
                    \Log::info('Survey form errors:', $errors->all());
                @endphp
            @endif

            @php
                $questions = $questions ?? collect();
                $activeEvent = $activeEvent ?? null;
                $location = $location ?? null;
            @endphp

            @if ($activeEvent)
                <div class="mb-4 rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm sm:text-base">
                    <div class="font-semibold text-gray-900">{{ $activeEvent->name }}</div>
                    <div class="text-gray-600">{{ $activeEvent->location }}</div>
                    <div class="text-gray-600">
                        {{ $activeEvent->starts_at?->format('Y-m-d') }} to {{ $activeEvent->ends_at?->format('Y-m-d') }}
                    </div>
                </div>

                @if ($errors->has('event'))
                    <p class="text-red-600 text-xs sm:text-sm mb-2">{{ $errors->first('event') }}</p>
                @endif

                <form action="{{ route('survey.submit') }}" method="POST" class="space-y-5 sm:space-y-6">
                    @csrf
                    <input type="hidden" name="location" value="{{ $location ?? $activeEvent->location }}">

                    @if ($questions->isNotEmpty())
                    @foreach ($questions as $question)
                        @php
                            $key = $question->key;
                            $type = $question->type;
                            $options = $question->option_pairs ?? [];
                            $required = $question->is_required;
                            $hasOther = $question->has_other;
                            $otherKey = $key . '_other';
                        @endphp

                        <div>
                            <label class="font-semibold block mb-1">{{ $question->label }}</label>

                            @if ($type === 'text')
                                <input type="text" name="{{ $key }}" value="{{ old($key) }}"
                                    @if ($key === 'phone') inputmode="numeric" @endif
                                    class="border border-gray-400 p-3 w-full rounded-lg focus:outline-none focus:border-blue-500 text-sm sm:text-base"
                                    @if ($required) required @endif>
                            @elseif ($type === 'select')
                                <select name="{{ $key }}"
                                    class="border border-gray-400 p-3 w-full rounded-lg focus:outline-none focus:border-blue-500 text-sm sm:text-base"
                                    @if ($required) required @endif>
                                    <option value="">Select</option>
                                    @foreach ($options as $option)
                                        <option value="{{ $option['value'] }}"
                                            {{ old($key) == $option['value'] ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                    @if ($hasOther)
                                        <option value="other" {{ old($key) == 'other' ? 'selected' : '' }}>Other</option>
                                    @endif
                                </select>
                                @if ($hasOther)
                                    <input id="{{ $otherKey }}" data-other-input="{{ $key }}" type="text"
                                        name="{{ $otherKey }}" value="{{ old($otherKey) }}"
                                        class="w-full mt-2 p-3 border border-gray-300 rounded-lg text-sm sm:text-base"
                                        placeholder="Please specify" disabled>
                                @endif
                            @elseif ($type === 'radio')
                                <div class="mt-1 space-y-1 text-sm sm:text-base">
                                    @foreach ($options as $option)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="{{ $key }}" value="{{ $option['value'] }}"
                                                {{ old($key) == $option['value'] ? 'checked' : '' }}
                                                @if ($required) required @endif>
                                            <span>{{ $option['label'] }}</span>
                                        </label>
                                    @endforeach
                                    @if ($hasOther)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="{{ $key }}" value="other"
                                                {{ old($key) == 'other' ? 'checked' : '' }}
                                                @if ($required) required @endif>
                                            <span>Other</span>
                                        </label>
                                        <input id="{{ $otherKey }}" data-other-input="{{ $key }}" type="text"
                                            name="{{ $otherKey }}" value="{{ old($otherKey) }}"
                                            class="w-full mt-2 p-3 border border-gray-300 rounded-lg text-sm sm:text-base"
                                            placeholder="Please specify" disabled>
                                    @endif
                                </div>
                            @elseif ($type === 'checkbox')
                                <div class="mt-1 space-y-1 text-sm sm:text-base">
                                    @foreach ($options as $option)
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" name="{{ $key }}[]" value="{{ $option['value'] }}"
                                                {{ in_array($option['value'], old($key, [])) ? 'checked' : '' }}>
                                            <span>{{ $option['label'] }}</span>
                                        </label>
                                    @endforeach
                                    @if ($hasOther)
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" name="{{ $key }}[]" value="other"
                                                {{ in_array('other', old($key, [])) ? 'checked' : '' }}>
                                            <span>Other</span>
                                        </label>
                                        <input id="{{ $otherKey }}" data-other-input="{{ $key }}" type="text"
                                            name="{{ $otherKey }}" value="{{ old($otherKey) }}"
                                            class="w-full mt-2 p-3 border border-gray-300 rounded-lg text-sm sm:text-base"
                                            placeholder="Please specify" disabled>
                                    @endif
                                </div>
                            @endif

                            @error($key)
                                <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error($otherKey)
                                <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <button type="submit"
                        class="w-full bg-gray-900 text-white py-3.5 rounded-2xl font-semibold text-base sm:text-lg active:opacity-80 mt-2">
                        Submit &amp; Spin
                    </button>
                @else

                <!-- Phone -->
                <div>
                    <label class="font-semibold block mb-1">ဖုန်းနံပါတ်</label>
                    <input type="text" inputmode="numeric" name="phone" value="{{ old('phone') }}"
                        class="border border-gray-400 p-3 w-full rounded-lg focus:outline-none focus:border-blue-500 text-sm sm:text-base"
                        required>
                    @error('phone')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div>
                    <label class="font-semibold block mb-1">နာမည်</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="border border-gray-400 p-3 w-full rounded-lg focus:outline-none focus:border-blue-500 text-sm sm:text-base"
                        required>
                    @error('name')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Age Group -->
                <div>
                    <label class="font-semibold block mb-1">အသက်အုပ်စု</label>
                    <select name="age"
                        class="border border-gray-400 p-3 w-full rounded-lg focus:outline-none focus:border-blue-500 text-sm sm:text-base"
                        required>
                        <option value="">ရွေးပါ</option>
                        <option value="18-24" {{ old('age') == '18-24' ? 'selected' : '' }}>၁၈-၂၄</option>
                        <option value="25-29" {{ old('age') == '25-29' ? 'selected' : '' }}>၂၅-၂၉</option>
                        <option value="30-35" {{ old('age') == '30-35' ? 'selected' : '' }}>၃၀-၃၅</option>
                        <option value="35+" {{ old('age') == '35+' ? 'selected' : '' }}>၃၅ နှင့်အထက်</option>
                    </select>
                    @error('age')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gender -->
                <div>
                    <label class="font-semibold block mb-1">လိင်</label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="gender" value="male" required
                                {{ old('gender') == 'male' ? 'checked' : '' }}>
                            <span>အမျိုးသား</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="gender" value="female"
                                {{ old('gender') == 'female' ? 'checked' : '' }}>
                            <span>အမျိုးသမီး</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="gender" value="prefer_no"
                                {{ old('gender') == 'prefer_no' ? 'checked' : '' }}>
                            <span>မပြောပြလိုပါ</span>
                        </label>
                    </div>
                    @error('gender')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Occupation -->
                <div>
                    <label class="font-semibold block mb-1">အလုပ်အကိုင်</label>
                    <select id="job_title" name="job_title"
                        class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm sm:text-base"
                        required>
                        <option value="">ရွေးပါ</option>
                        <option value="student" {{ old('job_title') == 'student' ? 'selected' : '' }}>
                            ကျောင်းသား/ကျောင်းသူ
                        </option>
                        <option value="office" {{ old('job_title') == 'office' ? 'selected' : '' }}>ရုံးဝန်ထမ်း
                        </option>
                        <option value="business" {{ old('job_title') == 'business' ? 'selected' : '' }}>
                            စီးပွားရေးပိုင်ရှင်</option>
                        <option value="freelancer" {{ old('job_title') == 'freelancer' ? 'selected' : '' }}>Freelancer
                        </option>
                        <option value="other" {{ old('job_title') == 'other' ? 'selected' : '' }}>အခြား
                        </option>
                    </select>
                    @error('job_title')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <input id="job_title_other" type="text" name="job_title_other"
                        value="{{ old('job_title_other') }}" placeholder="အခြားဆိုပါက ရေးထည့်ပါ"
                        class="w-full mt-2 p-3 border border-gray-300 rounded-lg text-sm sm:text-base" disabled>
                    @error('job_title_other')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Drinking Frequency -->
                <div>
                    <label class="font-semibold block mb-1">ဆိုဂျူးကို ဘယ်လောက်မကြာခဏသောက်လေ့ရှိလဲ?</label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_time" required value="daily"
                                {{ old('drink_time') == 'daily' ? 'checked' : '' }}>
                            <span>နေ့စဉ်</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_time" value="oneormore"
                                {{ old('drink_time') == 'oneormore' ? 'checked' : '' }}>
                            <span>တစ်ပတ် ၁ကြိမ် (သို့) ပိုများ</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_time" value="two-three"
                                {{ old('drink_time') == 'two-three' ? 'checked' : '' }}>
                            <span>တစ်ပတ်လျှင် ၂-၃ ကြိမ်</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_time" value="monthly"
                                {{ old('drink_time') == 'monthly' ? 'checked' : '' }}>
                            <span>တစ်လလျှင် တစ်ကြိမ်</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_time" value="special"
                                {{ old('drink_time') == 'special' ? 'checked' : '' }}>
                            <span>အထူးအခမ်းအနားများတွင်သာ</span>
                        </label>
                    </div>
                    @error('drink_time')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Drinking Location -->
                <div>
                    <label class="font-semibold block mb-1">
                        ဆိုဂျူးကို ဘယ်မှာသောက်လေ့ရှိလဲ? (သက်ဆိုင်သည်များအားလုံးကို ရွေးချယ်ပါ)
                    </label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_place[]" value="home"
                                {{ in_array('home', old('drink_place', [])) ? 'checked' : '' }}>
                            <span>အိမ်တွင်/သူငယ်ချင်းအိမ်တွင်</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_place[]" value="bar"
                                {{ in_array('bar', old('drink_place', [])) ? 'checked' : '' }}>
                            <span>ဘားများတွင်</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_place[]" value="restaurant"
                                {{ in_array('restaurant', old('drink_place', [])) ? 'checked' : '' }}>
                            <span>စားသောက်ဆိုင်များ/BBQ စားသောက်ဆိုင်များတွင်</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_place[]" value="korean_bbq"
                                {{ in_array('korean_bbq', old('drink_place', [])) ? 'checked' : '' }}>
                            <span>ကိုရီးယားစားသောက်ဆိုင်များ</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_place[]" value="staycation"
                                {{ in_array('staycation', old('drink_place', [])) ? 'checked' : '' }}>
                            <span>staycation တွင်</span>
                        </label>
                    </div>
                    @error('drink_place')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Drinking Partner -->
                <div>
                    <label class="font-semibold block mb-1">
                        staycation တွင် ဘယ်သူနဲ့ ဆိုဂျူး အများဆုံးသောက်လေ့ရှိလဲ?
                    </label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_whom" required value="friends"
                                {{ old('drink_whom') == 'friends' ? 'checked' : '' }}>
                            <span>သူငယ်ချင်းများ</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_whom" value="family"
                                {{ old('drink_whom') == 'family' ? 'checked' : '' }}>
                            <span>မိသားစု</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_whom" value="colleagues"
                                {{ old('drink_whom') == 'colleagues' ? 'checked' : '' }}>
                            <span>လုပ်ဖော်ကိုင်ဖက်များ</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="drink_whom" value="alone"
                                {{ old('drink_whom') == 'alone' ? 'checked' : '' }}>
                            <span>တစ်ယောက်တည်း</span>
                        </label>
                    </div>
                    @error('drink_whom')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reason for choosing brand -->
                <div>
                    <label class="font-semibold block mb-1">
                        ဆိုဂျူးအမှတ်တံဆိပ်ကို ရွေးချယ်ရာတွင် အရေးကြီးဆုံးအချက်များ
                    </label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="choose_reason[]" value="taste"
                                {{ in_array('taste', old('choose_reason', [])) ? 'checked' : '' }}>
                            <span>အရသာ</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="choose_reason[]" value="alcohol"
                                {{ in_array('alcohol', old('choose_reason', [])) ? 'checked' : '' }}>
                            <span>အရက်ပါဝင်မှုရာခိုင်နှုန်း</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="choose_reason[]" value="price"
                                {{ in_array('price', old('choose_reason', [])) ? 'checked' : '' }}>
                            <span>ဈေးနှုန်း</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="choose_reason[]" value="packaging"
                                {{ in_array('packaging', old('choose_reason', [])) ? 'checked' : '' }}>
                            <span>ထုပ်ပိုးမှု/ဒီဇိုင်း</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="choose_reason[]" value="brand_reputation"
                                {{ in_array('brand_reputation', old('choose_reason', [])) ? 'checked' : '' }}>
                            <span>အမှတ်တံဆိပ်ဂုဏ်သတင်း</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="choose_reason[]" value="availability"
                                {{ in_array('availability', old('choose_reason', [])) ? 'checked' : '' }}>
                            <span>လွယ်ကူစွာ ဝယ်ယူနိုင်မှု</span>
                        </label>
                    </div>
                    @error('choose_reason')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Food Pairing Importance -->
                <div>
                    <label class="font-semibold block mb-1">
                        ဆိုဂျူးသောက်တဲ့အခါ အစားအစာတွဲဖက်စားသုံးမှုက သင့်အတွက် ဘယ်လောက်အရေးကြီးလဲ?
                    </label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-start gap-2">
                            <input type="radio" class='mt-1' name="drink_meal_important" required value="very"
                                {{ old('drink_meal_important') == 'very' ? 'checked' : '' }}>
                            <span class="leading-tight">အလွန်အရေးကြီးပါတယ် - အမြဲတမ်း အစားအစာနဲ့
                                တွဲသောက်တတ်ပါသည်</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" class='mt-1' name="drink_meal_important" value="somewhat"
                                {{ old('drink_meal_important') == 'somewhat' ? 'checked' : '' }}>
                            <span class="leading-tight">ပုံမှန် အရေးကြီးပါတယ် - တစ်ခါတရံ တွဲသောက်တတ်ပါသည်</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="radio" class='mt-1'name="drink_meal_important" value="not_important"
                                {{ old('drink_meal_important') == 'not_important' ? 'checked' : '' }}>
                            <span class="leading-tight">အရေးမကြီးပါဘူး - အစားအစာမပါဘဲ ဆိုဂျူးကို သောက်တတ်ပါသည်</span>
                        </label>
                    </div>
                    @error('drink_meal_important')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Food Pairings -->
                <div>
                    <label class="font-semibold block mb-1">
                        ဆိုဂျူးနဲ့ ဘယ်လိုအစားအစာတွေ တွဲဖက်စားသုံးလေ့ရှိလဲ?
                    </label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_meal_type[]" value="korean_bbq"
                                {{ in_array('korean_bbq', old('drink_meal_type', [])) ? 'checked' : '' }}>
                            <span>ကိုရီးယား BBQ</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_meal_type[]" value="spicy"
                                {{ in_array('spicy', old('drink_meal_type', [])) ? 'checked' : '' }}>
                            <span>Spicy dishes (ramyeon, tteokbokki, hot pot, etc.)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_meal_type[]" value="fried"
                                {{ in_array('fried', old('drink_meal_type', [])) ? 'checked' : '' }}>
                            <span>Fried food (fried chicken, tempura, French fries etc.)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_meal_type[]" value="other"
                                {{ in_array('other', old('drink_meal_type', [])) ? 'checked' : '' }}>
                            <span>အခြား</span>
                        </label>
                    </div>
                    @error('drink_meal_type')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <input id="drink_meal_type_other" type="text" name="drink_meal_type_other"
                        value="{{ old('drink_meal_type_other') }}"
                        class="mt-2 w-full p-3 border border-gray-300 rounded-lg text-sm sm:text-base"
                        placeholder="အခြားဆိုပါက ရေးပါ" disabled>
                    @error('drink_meal_type_other')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Flavor Preference -->
                <div>
                    <label class="font-semibold block mb-1">
                        ဆိုဂျူးရဲ့ ဘယ်အရသာကို ပိုနှစ်သက်လဲ။ (သက်ဆိုင်တာအားလုံးကို ရွေးချယ်ပါ)
                    </label>
                    <div class="mt-1 space-y-1 text-sm sm:text-base">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="fresh"
                                {{ in_array('fresh', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Fresh</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="peach"
                                {{ in_array('peach', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Peach</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="yogurt"
                                {{ in_array('yogurt', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Yogurt</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="blueberry"
                                {{ in_array('blueberry', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Blueberry</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="grapefruit"
                                {{ in_array('grapefruit', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Grapefruit</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="strawberry"
                                {{ in_array('strawberry', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Strawberry</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="green_grape"
                                {{ in_array('green_grape', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Green Grape</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="drink_flavor[]" value="lychee"
                                {{ in_array('lychee', old('drink_flavor', [])) ? 'checked' : '' }}>
                            <span>Lychee</span>
                        </label>
                    </div>
                    @error('drink_flavor')
                        <p class="text-red-600 text-xs sm:text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-gray-900 text-white py-3.5 rounded-2xl font-semibold text-base sm:text-lg active:opacity-80 mt-2">
                    Submit &amp; Spin
                </button>

                @endif
                </form>
            @else
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm sm:text-base text-gray-700">
                    No active event is available right now. Please check back later.
                </div>
            @endif
        </div>
    </div>

    <script>
        // Intro popup
        document.addEventListener("DOMContentLoaded", function() {
            const popup = document.getElementById("introPopup");
            const closeBtn = document.getElementById("closeIntroPopup");

            closeBtn.addEventListener("click", function() {
                popup.style.display = "none";
            });
        });

        // Job title "other"
        document.addEventListener("DOMContentLoaded", function() {
            const jobSelect = document.querySelector('select[name="job_title"]');
            const otherInput = document.getElementById('job_title_other');

            function toggleJobOtherInput() {
                if (jobSelect.value === 'other') {
                    otherInput.disabled = false;
                } else {
                    otherInput.disabled = true;
                    otherInput.value = "";
                }
            }

            toggleJobOtherInput();
            jobSelect.addEventListener('change', toggleJobOtherInput);
        });

        // Drink meal "other"
        document.addEventListener("DOMContentLoaded", function() {
            const otherCheckbox = document.querySelector('input[name="drink_meal_type[]"][value="other"]');
            const otherInput = document.getElementById('drink_meal_type_other');

            function toggleMealOtherInput() {
                if (otherCheckbox && otherCheckbox.checked) {
                    otherInput.disabled = false;
                } else {
                    otherInput.disabled = true;
                    otherInput.value = "";
                }
            }

            toggleMealOtherInput();
            if (otherCheckbox) {
                otherCheckbox.addEventListener('change', toggleMealOtherInput);
            }
        });

        // Generic "other" toggle for dynamic questions
        document.addEventListener("DOMContentLoaded", function() {
            const otherInputs = document.querySelectorAll('[data-other-input]');

            otherInputs.forEach((input) => {
                const key = input.getAttribute('data-other-input');
                const singleField = document.querySelector(`[name="${key}"]`);
                const checkboxOther = document.querySelector(`[name="${key}[]"][value="other"]`);

                function toggleOtherInput() {
                    if (singleField && singleField.tagName === 'SELECT') {
                        input.disabled = singleField.value !== 'other';
                    } else {
                        const radioChecked = document.querySelector(`[name="${key}"]:checked`);

                        if (radioChecked) {
                            input.disabled = radioChecked.value !== 'other';
                        } else if (checkboxOther) {
                            input.disabled = !checkboxOther.checked;
                        } else {
                            input.disabled = true;
                        }
                    }

                    if (input.disabled) {
                        input.value = "";
                    }
                }

                toggleOtherInput();

                document.querySelectorAll(`[name="${key}"], [name="${key}[]"]`).forEach((field) => {
                    field.addEventListener('change', toggleOtherInput);
                });
            });
        });
    </script>

</body>

</html>
