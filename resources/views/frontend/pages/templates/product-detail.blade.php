@extends('layouts.master')

@section('content')
    @php
        use Illuminate\Support\Str;

        $locale = app()->getLocale();
        $homeUrl = url($locale === 'vi' ? '/' : '/' . $locale);
        $listingUrl = url($locale === 'vi' ? '/san-pham' : '/' . $locale . '/products');
        $contactUrl = url($locale === 'vi' ? '/lien-he' : '/' . $locale . '/contact');
        $categoryTranslation = $categoryTranslation ?? (
            $product->category?->relationLoaded('translations')
                ? $product->category->translations->firstWhere('locale', $locale)
                : $product->category?->translationFor($locale)->first()
        );
        $categoryUrl = $categoryUrl ?? (
            $categoryTranslation?->slug
                ? url($locale === 'vi' ? '/san-pham/' . $categoryTranslation->slug : '/' . $locale . '/products/' . $categoryTranslation->slug)
                : null
        );
        $hero = $translation->getFirstMediaUrl('hero') ?: $translation->getFirstMediaUrl('thumbnail');
        $thumbnail = $translation->getFirstMediaUrl('thumbnail') ?: $hero;
        $blocks = is_array($translation->blocks) ? $translation->blocks : [];
        $specifications = is_array($translation->specifications) ? $translation->specifications : [];
        $applications = collect($blocks['applications'] ?? [])->filter()->values();
        $substrates = collect($blocks['substrates'] ?? [])->filter()->values();
        $features = collect($blocks['features'] ?? [])->filter()->values();
        $processText = trim((string) ($blocks['process'] ?? ''));
        $processSteps = collect(preg_split('/\s*(?:->|→|;|\n)\s*/u', $processText))->filter()->values();
        $overview = trim((string) ($blocks['overview'] ?? $translation->description ?? ''));
        $categoryName = $categoryTranslation?->name ?: __('ui.common.products');
        $relatedCount = $relatedProducts->count();
        $applicationsForView = $applications;
        $substratesForView = $substrates;
        $featuresForView = $features;
        $processForView = $processSteps;

        $quickFacts = collect([
            ['label' => 'Nhóm sản phẩm', 'value' => $categoryName],
            ['label' => 'Mã sản phẩm', 'value' => $product->sku],
            ['label' => 'Ứng dụng chính', 'value' => $applications->isNotEmpty() ? Str::limit($applications->first(), 62) : null],
            ['label' => 'Nền vật liệu', 'value' => $substrates->isNotEmpty() ? $substrates->take(3)->implode(', ') : null],
        ])->filter(fn (array $item): bool => filled($item['value'] ?? null))->values();

        $consultingInputs = collect($blocks['consulting_inputs'] ?? [])->filter()->values();

        $selectionMatrix = collect($blocks['selection_matrix'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['criteria'] ?? null))
            ->values();

        $problemSolving = collect($blocks['problem_solving'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['problem'] ?? null))
            ->values();

        $storageNotes = collect($blocks['storage_notes'] ?? [])->filter()->values();

        $serviceFlow = collect($blocks['service_flow'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['title'] ?? null))
            ->values();

        $productFaq = collect($blocks['faq'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['question'] ?? null))
            ->values();

        $kingdaStrengths = collect($blocks['strengths'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['title'] ?? null))
            ->values();

        $qualityChecks = collect([
            ['name' => 'Mẫu nền', 'hint' => 'Kiểm tra độ bám, độ phủ và ngoại quan trên nền vật liệu thực tế.'],
            ['name' => 'Thông số gia công', 'hint' => 'Đối chiếu tốc độ, nhiệt độ, độ nhớt và điều kiện sấy/đóng rắn.'],
            ['name' => 'Thành phẩm', 'hint' => 'Đánh giá hiệu ứng bề mặt, độ ổn định và yêu cầu sau gia công.'],
        ]);

        $solutionCases = collect([
            [
                'title' => $applications->isNotEmpty() ? 'Khi cần ứng dụng đúng nền' : 'Khi cần chọn đúng vật liệu',
                'text' => $applications->isNotEmpty()
                    ? 'Phù hợp khi sản phẩm cần đáp ứng các nhóm ứng dụng như ' . $applications->take(2)->implode(', ') . '.'
                    : 'Phù hợp khi cần xác định vật liệu theo nền, công nghệ gia công và yêu cầu thành phẩm.',
            ],
            [
                'title' => $substrates->isNotEmpty() ? 'Khi nền vật liệu đa dạng' : 'Khi cần thử mẫu',
                'text' => $substrates->isNotEmpty()
                    ? 'Có thể dùng làm cơ sở thử nghiệm trên các nền như ' . $substrates->take(3)->implode(', ') . '.'
                    : 'Kingda có thể hỗ trợ thử mẫu để chốt thông số trước khi triển khai sản xuất.',
            ],
            [
                'title' => $features->isNotEmpty() ? 'Khi cần đặc tính ổn định' : 'Khi cần tối ưu quy trình',
                'text' => $features->isNotEmpty()
                    ? 'Ưu tiên khi dự án cần các đặc tính như ' . $features->take(2)->implode(', ') . '.'
                    : 'Phù hợp khi cần hiệu chỉnh vật liệu theo thiết bị, tốc độ và yêu cầu vận hành.',
            ],
        ]);

        $anchorItems = collect([
            ['id' => 'tong-quan', 'label' => 'Tổng quan'],
            $applications->isNotEmpty() ? ['id' => 'ung-dung', 'label' => 'Ứng dụng'] : null,
            $substrates->isNotEmpty() ? ['id' => 'nen-vat-lieu', 'label' => 'Nền vật liệu'] : null,
            $features->isNotEmpty() ? ['id' => 'dac-tinh', 'label' => 'Đặc tính'] : null,
            $processSteps->isNotEmpty() ? ['id' => 'quy-trinh', 'label' => 'Quy trình'] : null,
            ! empty($specifications) ? ['id' => 'thong-so', 'label' => 'Thông số'] : null,
            $selectionMatrix->isNotEmpty() ? ['id' => 'tu-van', 'label' => 'Tư vấn'] : null,
        ])->filter()->values();
    @endphp

    <section class="kd-product-hero relative overflow-hidden bg-[#17110a] text-white">
        <div class="absolute inset-0">
            @if($hero)
                <img src="{{ $hero }}" alt="{{ $translation->name }}" class="h-full w-full object-cover opacity-28">
            @else
                <div class="h-full w-full bg-[linear-gradient(135deg,#2b160f,#7f1118_48%,#17110a)]"></div>
            @endif
            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(23,17,10,.98)_0%,rgba(23,17,10,.88)_48%,rgba(23,17,10,.62)_100%)]"></div>
            <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-slate-100 to-transparent"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 pb-14 pt-10 md:pb-20 md:pt-14">
            <nav class="flex flex-wrap items-center gap-2 text-sm font-bold text-red-100/85">
                <a href="{{ $homeUrl }}" class="transition hover:text-white">{{ __('ui.common.home') }}</a>
                <span>/</span>
                <a href="{{ $listingUrl }}" class="transition hover:text-white">{{ __('ui.common.products') }}</a>
                @if($categoryTranslation)
                    <span>/</span>
                    <a href="{{ $categoryUrl ?: $listingUrl }}" class="transition hover:text-white">{{ $categoryTranslation->name }}</a>
                @endif
            </nav>

            <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_31rem] lg:items-end">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-red-100 ring-1 ring-white/15">
                            {{ $categoryName }}
                        </span>
                        @if($product->sku)
                            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-white">
                                SKU: {{ $product->sku }}
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-5 max-w-4xl text-4xl font-black leading-tight md:text-6xl">{{ $translation->name }}</h1>

                    @if($translation->description)
                        <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-200">{{ $translation->description }}</p>
                    @endif

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ $contactUrl }}" class="inline-flex items-center justify-center gap-2 rounded bg-primary px-6 py-3 text-sm font-extrabold text-white shadow-xl shadow-red-950/20 transition hover:-translate-y-0.5 hover:bg-primary-dark">
                            {{ __('ui.actions.contact_consulting') }}
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </a>
                        <a href="#thong-so" class="inline-flex items-center justify-center gap-2 rounded border border-white/20 bg-white/10 px-6 py-3 text-sm font-extrabold text-white transition hover:-translate-y-0.5 hover:border-white hover:bg-white hover:text-primary-dark">
                            Xem thông số
                            <i class="fa-solid fa-table-list"></i>
                        </a>
                    </div>
                </div>

                <div class="rounded border border-white/15 bg-white/10 p-3 shadow-2xl backdrop-blur">
                    <div class="overflow-hidden rounded bg-white">
                        @if($hero)
                            <img src="{{ $hero }}" alt="{{ $translation->name }}" class="aspect-[4/3] w-full object-cover">
                        @else
                            <div class="flex aspect-[4/3] w-full items-center justify-center bg-[linear-gradient(135deg,#fff,#fee2e2)] text-primary">
                                <div class="text-center">
                                    <i class="fa-solid fa-flask-vial text-6xl"></i>
                                    <div class="mt-4 text-sm font-extrabold uppercase text-slate-700">Kingda Material Solution</div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        @foreach($quickFacts->take(2) as $fact)
                            <div class="rounded bg-white/10 px-4 py-3 ring-1 ring-white/10">
                                <div class="text-[11px] font-extrabold uppercase text-red-100">{{ $fact['label'] }}</div>
                                <div class="mt-1 text-sm font-bold text-white">{{ $fact['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sticky top-0 z-30 hidden border-b border-slate-200 bg-white/95 backdrop-blur lg:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4">
            <nav class="flex min-w-0 items-center gap-1 overflow-x-auto py-3">
                @foreach($anchorItems as $item)
                    <a href="#{{ $item['id'] }}" class="kd-detail-anchor whitespace-nowrap rounded px-4 py-2 text-sm font-extrabold text-slate-600 transition hover:bg-red-50 hover:text-primary">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <a href="{{ $contactUrl }}" class="shrink-0 rounded bg-primary px-5 py-2.5 text-sm font-extrabold text-white transition hover:bg-primary-dark">
                Nhận tư vấn
            </a>
        </div>
    </section>

    <section class="bg-slate-100 py-12 md:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 lg:grid-cols-[minmax(0,1fr)_24rem]">
            <main class="min-w-0 space-y-8">
                <section id="tong-quan" class="kd-detail-section overflow-hidden rounded bg-white shadow ring-1 ring-slate-200">
                    <div class="grid gap-0 lg:grid-cols-[1.1fr_.9fr]">
                        <div class="p-6 md:p-8">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.product_overview') }}</p>
                            <h2 class="mt-2 text-3xl font-black leading-tight text-slate-950">{{ $translation->name }}</h2>
                            @if($overview)
                                <p class="mt-5 text-base leading-8 text-slate-600">{{ $overview }}</p>
                            @endif

                            <div class="mt-7 grid gap-4 sm:grid-cols-2">
                                @foreach($quickFacts as $fact)
                                    <div class="rounded border border-slate-200 bg-slate-50 p-4">
                                        <div class="text-xs font-extrabold uppercase text-slate-400">{{ $fact['label'] }}</div>
                                        <div class="mt-2 text-sm font-extrabold leading-6 text-slate-950">{{ $fact['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-[#17110a] p-6 text-white md:p-8">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-red-200">Hồ sơ giải pháp</p>
                            <h3 class="mt-2 text-2xl font-black leading-tight">Thông tin cần để chốt đúng vật liệu</h3>
                            <div class="mt-6 space-y-3">
                                @foreach($consultingInputs as $input)
                                    <div class="flex gap-3 rounded border border-white/10 bg-white/8 p-4">
                                        <span class="mt-1 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-black">
                                            {{ $loop->iteration }}
                                        </span>
                                        <p class="text-sm leading-6 text-slate-200">{{ $input }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section id="ung-dung" class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.applications') }}</p>
                            <h2 class="mt-2 text-3xl font-black text-slate-950">Ứng dụng phù hợp</h2>
                        </div>
                        <p class="max-w-2xl text-sm leading-6 text-slate-500">Các nhóm ứng dụng tiêu biểu cho sản phẩm này trong quy trình in, phủ, gia công và hoàn thiện bề mặt.</p>
                    </div>

                    <div class="mt-7 grid gap-4 md:grid-cols-2">
                        @foreach($applicationsForView as $item)
                            <div class="group rounded border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-1 hover:border-red-100 hover:bg-white hover:shadow-lg">
                                <div class="flex gap-4">
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded bg-red-50 text-primary transition group-hover:bg-primary group-hover:text-white">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-base font-extrabold text-slate-950">Ứng dụng {{ $loop->iteration }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="nen-vat-lieu" class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <div class="grid gap-8 lg:grid-cols-[.82fr_1.18fr] lg:items-start">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.substrates') }}</p>
                            <h2 class="mt-2 text-3xl font-black leading-tight text-slate-950">Nền vật liệu và khả năng tương thích</h2>
                            <p class="mt-4 text-sm leading-7 text-slate-600">Danh sách này là cơ sở để khách hàng gửi mẫu nền hoặc mô tả quy trình. Khi triển khai thực tế, Kingda sẽ kiểm tra lại điều kiện bề mặt, nhiệt, tốc độ và yêu cầu thành phẩm.</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach($substratesForView as $item)
                                <div class="flex items-center gap-3 rounded border border-slate-200 bg-white p-4 shadow-sm">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-red-50 text-primary">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <span class="text-sm font-extrabold text-slate-800">{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="dac-tinh" class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.technical_properties') }}</p>
                            <h2 class="mt-2 text-3xl font-black text-slate-950">Đặc tính kỹ thuật nổi bật</h2>
                            <div class="mt-6 grid gap-4">
                                @foreach($featuresForView as $item)
                                    <div class="rounded border border-slate-200 bg-slate-50 p-5">
                                        <div class="flex gap-4">
                                            <span class="flex size-10 shrink-0 items-center justify-center rounded bg-white text-primary shadow-sm">
                                                <i class="fa-solid fa-star"></i>
                                            </span>
                                            <p class="text-sm leading-7 text-slate-700">{{ $item }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded bg-[#17110a] p-6 text-white">
                            <p class="text-xs font-extrabold uppercase text-red-200">Kiểm soát chất lượng</p>
                            <div class="mt-5 space-y-4">
                                @foreach($qualityChecks as $check)
                                    <div>
                                        <h3 class="font-extrabold">{{ $check['name'] }}</h3>
                                        <p class="mt-1 text-sm leading-6 text-slate-300">{{ $check['hint'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section id="quy-trinh" class="kd-detail-section overflow-hidden rounded bg-[#17110a] text-white shadow ring-1 ring-slate-200">
                    <div class="p-6 md:p-8">
                        <p class="text-xs font-extrabold uppercase tracking-wide text-red-200">{{ __('ui.common.process') }}</p>
                        <h2 class="mt-2 text-3xl font-black">{{ __('ui.common.process_and_structure') }}</h2>
                        @if($processText)
                            <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-300">{{ $processText }}</p>
                        @endif
                    </div>

                    <div class="grid border-t border-white/10 md:grid-cols-4">
                        @foreach($processForView as $step)
                            <div class="relative border-b border-white/10 p-6 md:border-b-0 md:border-r">
                                <span class="text-4xl font-black text-white/15">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3 class="mt-6 text-lg font-extrabold">Bước {{ $loop->iteration }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-300">{{ $step }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="thong-so" class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.specifications') }}</p>
                            <h2 class="mt-2 text-3xl font-black text-slate-950">Thông số tham khảo</h2>
                        </div>
                        <p class="max-w-xl text-sm leading-6 text-slate-500">Thông số cuối cùng có thể thay đổi theo nền vật liệu, yêu cầu thành phẩm và điều kiện vận hành thực tế.</p>
                    </div>

                    @if(! empty($specifications))
                        <dl class="mt-7 overflow-hidden rounded border border-slate-200">
                            @foreach($specifications as $key => $value)
                                <div class="grid gap-2 border-b border-slate-200 bg-white p-4 last:border-b-0 md:grid-cols-3">
                                    <dt class="font-extrabold text-slate-800">{{ $key }}</dt>
                                    <dd class="leading-6 text-slate-600 md:col-span-2">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <div class="mt-7 grid gap-4 md:grid-cols-3">
                            <div class="rounded border border-slate-200 bg-slate-50 p-5">
                                <h3 class="font-extrabold text-slate-950">Màu sắc / ngoại quan</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Tùy mẫu chuẩn hoặc yêu cầu thiết kế của khách hàng.</p>
                            </div>
                            <div class="rounded border border-slate-200 bg-slate-50 p-5">
                                <h3 class="font-extrabold text-slate-950">Độ nhớt / hàm lượng rắn</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Hiệu chỉnh theo thiết bị, tốc độ và phương pháp gia công.</p>
                            </div>
                            <div class="rounded border border-slate-200 bg-slate-50 p-5">
                                <h3 class="font-extrabold text-slate-950">Quy cách đóng gói</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Theo cấu hình sản phẩm và kế hoạch cung ứng.</p>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-primary">Khi nào nên dùng sản phẩm này</p>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        @foreach($solutionCases as $case)
                            <div class="rounded border border-slate-200 bg-slate-50 p-5">
                                <h3 class="font-extrabold text-slate-950">{{ $case['title'] }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $case['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="tu-van" class="kd-detail-section overflow-hidden rounded bg-white shadow ring-1 ring-slate-200">
                    <div class="border-b border-slate-200 p-6 md:p-8">
                        <p class="text-xs font-extrabold uppercase tracking-wide text-primary">Ma trận lựa chọn</p>
                        <div class="mt-2 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                            <h2 class="text-3xl font-black leading-tight text-slate-950">Cách xác định đúng dòng vật liệu</h2>
                            <p class="max-w-2xl text-sm leading-6 text-slate-500">Đối chiếu nền vật liệu, công nghệ gia công và yêu cầu thành phẩm để lựa chọn giải pháp phù hợp hơn.</p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach($selectionMatrix as $row)
                            <div class="grid gap-4 p-6 md:grid-cols-[.7fr_1.1fr_1.1fr] md:p-8">
                                <div>
                                    <span class="inline-flex rounded bg-red-50 px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-primary">{{ $row['criteria'] ?? '' }}</span>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-wide text-slate-400">Câu hỏi cần làm rõ</h3>
                                    <p class="mt-2 text-sm leading-7 text-slate-700">{{ $row['question'] ?? '' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-wide text-slate-400">Hướng chọn vật liệu</h3>
                                    <p class="mt-2 text-sm leading-7 text-slate-700">{{ $row['decision'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <div class="grid gap-8 lg:grid-cols-[.82fr_1.18fr]">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">Vấn đề và hướng xử lý</p>
                            <h2 class="mt-2 text-3xl font-black leading-tight text-slate-950">Khi sản xuất không đạt, cần xem nguyên nhân ở đâu?</h2>
                            <p class="mt-4 text-sm leading-7 text-slate-600">Một sản phẩm vật liệu tốt vẫn cần đúng nền, đúng công nghệ và đúng thông số vận hành để đạt kết quả ổn định.</p>
                        </div>

                        <div class="space-y-4">
                            @foreach($problemSolving as $item)
                                <div class="rounded border border-slate-200 bg-slate-50 p-5">
                                    <div class="flex gap-4">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded bg-white text-primary shadow-sm">
                                            <i class="fa-solid fa-screwdriver-wrench"></i>
                                        </span>
                                        <div>
                                            <h3 class="font-black text-slate-950">{{ $item['problem'] ?? '' }}</h3>
                                            <p class="mt-2 text-sm leading-6 text-slate-600"><strong>Nguyên nhân thường gặp:</strong> {{ $item['reason'] ?? '' }}</p>
                                            <p class="mt-2 text-sm leading-6 text-slate-600"><strong>Hướng xử lý:</strong> {{ $item['solution'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="kd-detail-section overflow-hidden rounded bg-white shadow ring-1 ring-slate-200">
                    <div class="grid gap-0 lg:grid-cols-[1fr_.9fr]">
                        <div class="p-6 md:p-8">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">Triển khai cùng Kingda</p>
                            <h2 class="mt-2 text-3xl font-black leading-tight text-slate-950">Từ yêu cầu kỹ thuật đến sản phẩm dùng được</h2>
                            <div class="mt-7 grid gap-4">
                                @foreach($serviceFlow as $item)
                                    <div class="flex gap-4 rounded border border-slate-200 bg-slate-50 p-5">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded bg-primary text-sm font-black text-white">
                                            {{ $loop->iteration }}
                                        </span>
                                        <div>
                                            <h3 class="font-black text-slate-950">{{ $item['title'] ?? '' }}</h3>
                                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ $item['text'] ?? '' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-[linear-gradient(135deg,rgba(23,17,10,.94),rgba(159,15,22,.82)),url('/images/kingda-hero-rd-san-xuat-1920x760.jpg')] bg-cover bg-center p-6 text-white md:p-8">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-red-200">Lưu ý bảo quản</p>
                            <h3 class="mt-2 text-2xl font-black leading-tight">Bảo quản đúng để giữ ổn định vật liệu</h3>
                            <ul class="mt-6 space-y-4">
                                @foreach($storageNotes as $note)
                                    <li class="flex gap-3 text-sm leading-7 text-slate-200">
                                        <i class="fa-solid fa-circle-check mt-1 text-primary"></i>
                                        <span>{{ $note }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">FAQ sản phẩm</p>
                            <h2 class="mt-2 text-3xl font-black text-slate-950">Câu hỏi thường gặp</h2>
                        </div>
                        <p class="max-w-xl text-sm leading-6 text-slate-500">Một số câu hỏi thường gặp trước khi thử mẫu, xác nhận thông số và triển khai sản xuất.</p>
                    </div>

                    <div class="mt-7 divide-y divide-slate-200 rounded border border-slate-200">
                        @foreach($productFaq as $item)
                            <details class="group bg-white p-5 open:bg-slate-50">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-base font-black text-slate-950">
                                    <span>{{ $item['question'] ?? '' }}</span>
                                    <i class="fa-solid fa-chevron-down text-primary transition group-open:rotate-180"></i>
                                </summary>
                                <p class="mt-4 text-sm leading-7 text-slate-600">{{ $item['answer'] ?? '' }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>

                <section class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">Lý do chọn Kingda</p>
                            <h2 class="mt-2 text-3xl font-black leading-tight text-slate-950">Không chỉ là sản phẩm, mà là một phương án vật liệu</h2>
                        </div>
                        <p class="max-w-xl text-sm leading-6 text-slate-500">Kingda đồng hành từ lựa chọn vật liệu, thử mẫu, hiệu chỉnh thông số đến hỗ trợ trong quá trình sản xuất.</p>
                    </div>

                    <div class="mt-7 grid gap-4 md:grid-cols-2">
                        @foreach($kingdaStrengths as $strength)
                            <div class="rounded border border-slate-200 bg-slate-50 p-5">
                                <div class="flex gap-4">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded bg-white text-primary shadow-sm">
                                        <i class="fa-solid fa-award"></i>
                                    </span>
                                    <div>
                                        <h3 class="font-black text-slate-950">{{ $strength['title'] ?? '' }}</h3>
                                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $strength['text'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if($translation->content)
                    <section class="kd-detail-section rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                        <p class="text-xs font-extrabold uppercase tracking-wide text-primary">Nội dung chi tiết</p>
                        <div class="kd-product-content mt-5">{!! $translation->content !!}</div>
                    </section>
                @endif
            </main>

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                <div class="overflow-hidden rounded bg-white shadow ring-1 ring-slate-200">
                    @if($thumbnail)
                        <img src="{{ $thumbnail }}" alt="{{ $translation->name }}" class="h-56 w-full object-cover">
                    @else
                        <div class="flex h-56 w-full items-center justify-center bg-gradient-to-br from-white via-red-50 to-slate-200 text-primary">
                            <div class="text-center">
                                <i class="fa-solid fa-flask-vial text-5xl"></i>
                                <div class="mt-3 text-xs font-extrabold uppercase text-slate-600">Kingda</div>
                            </div>
                        </div>
                    @endif
                    <div class="p-6">
                        <div class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.contact') }}</div>
                        <h2 class="mt-2 text-xl font-black text-slate-950">{{ __('ui.actions.contact_consulting') }}</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('ui.common.contact_product_hint') }}</p>
                        <a href="{{ $contactUrl }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded bg-primary px-5 py-3 text-sm font-extrabold text-white transition hover:bg-primary-dark">
                            {{ __('ui.actions.contact_consulting') }}
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </a>
                    </div>
                </div>

                @if($categoryTranslation)
                    <div class="rounded bg-white p-6 shadow ring-1 ring-slate-200">
                        <div class="text-xs font-extrabold uppercase tracking-wide text-slate-400">{{ __('ui.common.product_categories') }}</div>
                        <a href="{{ $categoryUrl ?: $listingUrl }}" class="mt-2 block text-lg font-black text-slate-950 transition hover:text-primary">
                            {{ $categoryTranslation->name }}
                        </a>
                        @if($categoryTranslation->description)
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ Str::limit($categoryTranslation->description, 140) }}</p>
                        @endif
                    </div>
                @endif

                <div class="rounded bg-[#17110a] p-6 text-white shadow ring-1 ring-slate-200">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-red-200">Checklist gửi mẫu</p>
                    <ul class="mt-4 space-y-3">
                        @foreach($consultingInputs as $input)
                            <li class="flex gap-3 text-sm leading-6 text-slate-300">
                                <i class="fa-solid fa-circle-check mt-1 text-primary"></i>
                                <span>{{ $input }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>

        @if($relatedCount)
            <div class="mx-auto mt-12 max-w-7xl px-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.related_products') }}</p>
                        <h2 class="mt-2 text-3xl font-black text-slate-950">Sản phẩm liên quan</h2>
                    </div>
                    <a href="{{ $listingUrl }}" class="text-sm font-extrabold text-primary hover:text-primary-dark">Xem tất cả sản phẩm</a>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-3">
                    @foreach($relatedProducts as $related)
                        @php
                            $relatedTranslation = $related->translation;
                            $relatedCategoryTranslation = $related->category?->translation;
                            $relatedImage = $relatedTranslation?->getFirstMediaUrl('thumbnail') ?: $relatedTranslation?->getFirstMediaUrl('hero');
                            $relatedUrl = $relatedTranslation?->slug
                                ? url($locale === 'vi'
                                    ? '/san-pham/' . collect([$relatedCategoryTranslation?->slug, $relatedTranslation->slug])->filter()->join('/')
                                    : '/' . $locale . '/products/' . collect([$relatedCategoryTranslation?->slug, $relatedTranslation->slug])->filter()->join('/'))
                                : '#';
                        @endphp
                        <a href="{{ $relatedUrl }}" class="group overflow-hidden rounded bg-white shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">
                            @if($relatedImage)
                                <img src="{{ $relatedImage }}" alt="{{ $relatedTranslation?->name }}" class="h-52 w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-52 w-full items-center justify-center bg-gradient-to-br from-white via-red-50 to-slate-200 text-primary">
                                    <div class="text-center">
                                        <i class="fa-solid fa-flask-vial text-4xl"></i>
                                        <div class="mt-3 text-xs font-extrabold uppercase text-slate-600">Kingda</div>
                                    </div>
                                </div>
                            @endif
                            <div class="p-5">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ $related->category?->translation?->name ?: __('ui.common.products') }}</p>
                                <h3 class="mt-2 text-lg font-black leading-snug text-slate-950">{{ $relatedTranslation?->name }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ Str::limit($relatedTranslation?->description, 115) }}</p>
                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-primary">
                                    Xem chi tiết
                                    <i class="fa-solid fa-arrow-right-long transition group-hover:translate-x-1"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
