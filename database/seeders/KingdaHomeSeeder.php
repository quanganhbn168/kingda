<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Industry;
use App\Models\Menu;
use App\Models\Slide;
use App\Enums\Locale;
use App\Enums\MenuLocation;
use App\Enums\MenuLinkType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KingdaHomeSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::query()->updateOrCreate(
            ['key' => 'home'],
            [
                'type' => 'page',
                'template' => 'home',
                'is_home' => true,
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        foreach ([
            'vi' => [
                'title' => 'Trang chủ',
                'headline' => 'Giải pháp vật liệu in ấn điện tử & sơn phủ công nghiệp',
                'subheadline' => 'Kingda cung cấp các giải pháp mực in, sơn phủ và vật liệu chức năng cho ngành điện tử tiêu dùng, linh kiện ô tô, kính, composite và các ứng dụng công nghiệp yêu cầu độ ổn định cao.',
                'seo_title' => 'Kingda - Giải pháp mực in, sơn phủ và vật liệu công nghiệp',
                'seo_description' => 'Kingda là doanh nghiệp về mực in, sơn phủ bề mặt và vật liệu in điện tử cho các ứng dụng công nghiệp yêu cầu tiêu chuẩn kỹ thuật cao.',
            ],
            'en' => [
                'title' => 'Home',
                'headline' => 'Electronic printing materials and industrial coating solutions',
                'subheadline' => 'Kingda provides ink, coating and functional material solutions for consumer electronics, automotive components, glass, composites and demanding industrial applications.',
                'seo_title' => 'Kingda - Inks, coatings and industrial material solutions',
                'seo_description' => 'Kingda develops inks, surface coatings and electronic printing materials for industrial applications with demanding technical standards.',
            ],
            'zh' => [
                'title' => '首页',
                'headline' => '电子印刷材料与工业涂层解决方案',
                'subheadline' => '金达为消费电子、汽车零部件、玻璃、复合材料及高标准工业应用提供油墨、涂料和功能材料解决方案。',
                'seo_title' => '金达 - 油墨、涂料与工业材料解决方案',
                'seo_description' => '金达为高技术标准工业应用开发油墨、表面涂层和电子印刷材料。',
            ],
        ] as $locale => $content) {
            $page->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $content['title'],
                    'slug' => null,
                    'headline' => $content['headline'],
                    'subheadline' => $content['subheadline'],
                    'seo_title' => $content['seo_title'],
                    'seo_description' => $content['seo_description'],
                    'meta_robots' => 'index,follow',
                    'is_published' => true,
                    'published_at' => now(),
                ],
            );
        }

        $slide = Slide::query()->updateOrCreate(
            ['key' => 'home-hero-kingda'],
            [
                'location' => 'home',
                'theme' => 'dark',
                'text_position' => 'left',
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        foreach ([
            'vi' => [
                'eyebrow' => 'CÔNG TY TNHH THƯƠNG MẠI CÔNG NGHỆ KINGDA',
                'title' => 'Giải pháp mực in, sơn phủ và vật liệu ứng dụng cho ngành công nghiệp điện tử',
                'description' => 'Kingda tập trung nghiên cứu, sản xuất và cung cấp các dòng mực in, sơn phủ bề mặt, vật liệu chức năng phục vụ sản phẩm điện tử 3C, linh kiện ô tô, kính, composite và các ứng dụng công nghiệp yêu cầu tiêu chuẩn kỹ thuật cao.',
                'primary_button_label' => 'Xem sản phẩm',
                'primary_button_url' => '/san-pham',
                'secondary_button_label' => 'Liên hệ tư vấn',
                'secondary_button_url' => '/lien-he',
                'image_alt' => 'Kingda - vật liệu in ấn điện tử và sơn phủ công nghiệp',
            ],
            'en' => [
                'eyebrow' => 'KINGDA TECHNOLOGY TRADING COMPANY LIMITED',
                'title' => 'Inks, coatings and applied materials for the electronics industry',
                'description' => 'Kingda focuses on research, production and supply of inks, surface coatings and functional materials for 3C electronics, automotive components, glass, composites and industrial applications.',
                'primary_button_label' => 'View products',
                'primary_button_url' => '/en/products',
                'secondary_button_label' => 'Contact us',
                'secondary_button_url' => '/en/contact',
                'image_alt' => 'Kingda electronic printing materials and industrial coatings',
            ],
            'zh' => [
                'eyebrow' => '金达科技贸易有限公司',
                'title' => '面向电子工业的油墨、涂料与应用材料解决方案',
                'description' => '金达专注于油墨、表面涂料和功能材料的研发、生产与供应，服务3C电子、汽车零部件、玻璃、复合材料及工业应用。',
                'primary_button_label' => '查看产品',
                'primary_button_url' => '/zh/products',
                'secondary_button_label' => '联系我们',
                'secondary_button_url' => '/zh/contact',
                'image_alt' => '金达电子印刷材料与工业涂层',
            ],
        ] as $locale => $content) {
            $slide->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    ...$content,
                    'is_published' => true,
                ],
            );
        }

        $this->seedIndustries();
        $this->seedSystemPages();
        $this->seedMenus();
    }

    private function seedSystemPages(): void
    {
        $pages = [
            'about' => [
                'template' => 'about',
                'vi' => ['title' => 'Giới thiệu', 'slug' => 'gioi-thieu', 'headline' => 'Về chúng tôi', 'subheadline' => 'Chuyên nghiên cứu, sản xuất và cung cấp giải pháp vật liệu in điện tử hàng đầu'],
                'en' => ['title' => 'About', 'slug' => 'about', 'headline' => 'About us', 'subheadline' => 'Focused on R&D, production and supply of electronic printing material solutions'],
                'zh' => ['title' => '关于我们', 'slug' => 'about', 'headline' => '关于我们', 'subheadline' => '专注电子印刷材料解决方案的研发、生产与供应'],
            ],
            'products' => [
                'template' => 'products',
                'vi' => ['title' => 'Sản phẩm', 'slug' => 'san-pham', 'headline' => 'Sản phẩm', 'subheadline' => 'Danh mục sản phẩm và giải pháp vật liệu ứng dụng của Kingda.'],
                'en' => ['title' => 'Products', 'slug' => 'products', 'headline' => 'Products', 'subheadline' => 'Kingda product categories and application material solutions.'],
                'zh' => ['title' => '产品', 'slug' => 'products', 'headline' => '产品', 'subheadline' => '金达产品分类和应用材料解决方案。'],
            ],
            'news' => [
                'template' => 'news',
                'vi' => ['title' => 'Tin tức', 'slug' => 'tin-tuc', 'headline' => 'Tin tức', 'subheadline' => 'Cập nhật tin tức, hoạt động và kiến thức ứng dụng vật liệu.'],
                'en' => ['title' => 'News', 'slug' => 'news', 'headline' => 'News', 'subheadline' => 'Latest updates, activities and material application insights.'],
                'zh' => ['title' => '新闻', 'slug' => 'news', 'headline' => '新闻', 'subheadline' => '最新动态、活动和材料应用见解。'],
            ],
            'industries' => [
                'template' => 'industries',
                'vi' => ['title' => 'Lĩnh vực', 'slug' => 'linh-vuc', 'headline' => 'Lĩnh vực ứng dụng', 'subheadline' => 'Các lĩnh vực ứng dụng vật liệu, mực in và sơn phủ công nghiệp của Kingda.'],
                'en' => ['title' => 'Industries', 'slug' => 'industries', 'headline' => 'Application industries', 'subheadline' => 'Industries applying Kingda materials, inks and industrial coating solutions.'],
                'zh' => ['title' => '应用领域', 'slug' => 'industries', 'headline' => '应用领域', 'subheadline' => '金达材料、油墨和工业涂层解决方案的应用领域。'],
            ],
            'contact' => [
                'template' => 'contact',
                'vi' => ['title' => 'Liên hệ', 'slug' => 'lien-he', 'headline' => 'Liên hệ', 'subheadline' => 'Kết nối với Kingda để được tư vấn giải pháp phù hợp.'],
                'en' => ['title' => 'Contact', 'slug' => 'contact', 'headline' => 'Contact', 'subheadline' => 'Contact Kingda for tailored solution consulting.'],
                'zh' => ['title' => '联系', 'slug' => 'contact', 'headline' => '联系', 'subheadline' => '联系金达，获取定制化解决方案咨询。'],
            ],
        ];

        foreach ($pages as $key => $data) {
            $page = Page::query()->updateOrCreate(
                ['key' => $key],
                [
                    'type' => 'page',
                    'template' => $data['template'],
                    'is_home' => false,
                    'is_system' => true,
                    'is_active' => true,
                    'sort_order' => match ($key) {
                        'about' => 10,
                        'products' => 20,
                        'news' => 30,
                        'contact' => 40,
                        default => 99,
                    },
                ],
            );

            foreach (['vi', 'en', 'zh'] as $locale) {
                $content = $data[$locale];

                $page->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $content['title'],
                        'slug' => $content['slug'],
                        'headline' => $content['headline'],
                        'subheadline' => $content['subheadline'],
                        'excerpt' => $content['subheadline'],
                        'seo_title' => 'Kingda - ' . $content['title'],
                        'seo_description' => $content['subheadline'],
                        'meta_robots' => 'index,follow',
                        'is_published' => true,
                        'published_at' => now(),
                    ],
                );
            }
        }
    }

    private function seedIndustries(): void
    {
        $items = [
            [
                'icon' => 'fa-mobile-screen-button',
                'vi' => ['title' => 'Điện tử tiêu dùng 3C', 'slug' => 'dien-tu-tieu-dung-3c', 'description' => 'Sơn và mực dùng cho điện thoại, thiết bị thông minh, bảng điều khiển và sản phẩm điện tử.'],
                'en' => ['title' => '3C consumer electronics', 'slug' => '3c-consumer-electronics', 'description' => 'Coatings and inks for phones, smart devices, panels and electronic products.'],
                'zh' => ['title' => '3C消费电子', 'slug' => '3c-consumer-electronics', 'description' => '用于手机、智能设备、面板和电子产品的涂料与油墨。'],
            ],
            [
                'icon' => 'fa-car-side',
                'vi' => ['title' => 'Linh kiện ô tô', 'slug' => 'linh-kien-o-to', 'description' => 'Sơn phủ và vật liệu bề mặt cho các chi tiết nhựa, kim loại hóa và linh kiện nội thất.'],
                'en' => ['title' => 'Automotive components', 'slug' => 'automotive-components', 'description' => 'Coatings and surface materials for plastic, metallized and interior components.'],
                'zh' => ['title' => '汽车零部件', 'slug' => 'automotive-components', 'description' => '用于塑料件、金属化件和内饰件的涂料与表面材料。'],
            ],
            [
                'icon' => 'fa-layer-group',
                'vi' => ['title' => 'Vật liệu composite', 'slug' => 'vat-lieu-composite', 'description' => 'Mực in, sơn phủ bảo vệ và giải pháp bề mặt cho tấm composite.'],
                'en' => ['title' => 'Composite materials', 'slug' => 'composite-materials', 'description' => 'Inks, protective coatings and surface solutions for composite sheets.'],
                'zh' => ['title' => '复合材料', 'slug' => 'composite-materials', 'description' => '用于复合板材的油墨、防护涂层和表面解决方案。'],
            ],
            [
                'icon' => 'fa-vector-square',
                'vi' => ['title' => 'Gia công kính', 'slug' => 'gia-cong-kinh', 'description' => 'Mực bảo vệ, mực in kính mặt trước, kính mặt sau và giải pháp xử lý trong quá trình gia công kính.'],
                'en' => ['title' => 'Glass processing', 'slug' => 'glass-processing', 'description' => 'Protective inks, front and back glass inks and processing solutions for glass manufacturing.'],
                'zh' => ['title' => '玻璃加工', 'slug' => 'glass-processing', 'description' => '玻璃加工中的保护油墨、前后盖板油墨和处理方案。'],
            ],
        ];

        foreach ($items as $position => $item) {
            $industry = Industry::query()->updateOrCreate(
                ['icon' => $item['icon']],
                [
                    'is_featured' => true,
                    'is_active' => true,
                    'sort_order' => $position,
                ],
            );

            foreach (['vi', 'en', 'zh'] as $locale) {
                $industry->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        ...$item[$locale],
                        'is_published' => true,
                    ],
                );
            }
        }
    }

    private function seedMenus(): void
    {
        $headerMenu = Menu::query()->updateOrCreate(
            ['location' => MenuLocation::Header->value],
            [
                'name' => 'Header',
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        $footerMenu = Menu::query()->updateOrCreate(
            ['location' => MenuLocation::Footer->value],
            [
                'name' => 'Footer',
                'is_active' => true,
                'sort_order' => 10,
            ],
        );

        $headerMenu->items()->delete();
        $footerMenu->items()->delete();

        $headerRootGroupKeys = [];
        $headerChildGroupKeys = [];
        $footerGroupKeys = [];

        foreach ($this->localizedMenuItems(Locale::Vietnamese->value) as $position => $item) {
            $headerRootGroupKeys[$position] = (string) Str::uuid();
            $footerGroupKeys[$position] = (string) Str::uuid();

            foreach (($item['children'] ?? []) as $childPosition => $child) {
                $headerChildGroupKeys[$position][$childPosition] = (string) Str::uuid();
            }
        }

        foreach (Locale::cases() as $locale) {
            $items = $this->localizedMenuItems($locale->value);

            foreach ($items as $position => $item) {
                $children = $item['children'] ?? [];
                unset($item['children']);

                $menuItem = $headerMenu->items()->create([
                    ...$item,
                    'group_key' => $headerRootGroupKeys[$position] ?? (string) Str::uuid(),
                    'locale' => $locale->value,
                    'link_type' => MenuLinkType::Custom->value,
                    'target' => '_self',
                    'is_active' => true,
                    'sort_order' => $position,
                ]);

                foreach ($children as $childPosition => $child) {
                    $headerMenu->items()->create([
                        ...$child,
                        'group_key' => $headerChildGroupKeys[$position][$childPosition] ?? (string) Str::uuid(),
                        'parent_id' => $menuItem->id,
                        'locale' => $locale->value,
                        'link_type' => MenuLinkType::Custom->value,
                        'target' => '_self',
                        'is_active' => true,
                        'sort_order' => $childPosition,
                    ]);
                }
            }

            foreach (array_slice($items, 0, 6) as $position => $item) {
                $footerMenu->items()->create([
                    'group_key' => $footerGroupKeys[$position] ?? (string) Str::uuid(),
                    'locale' => $locale->value,
                    'label' => $item['label'],
                    'link_type' => MenuLinkType::Custom->value,
                    'url' => $item['url'],
                    'target' => '_self',
                    'is_active' => true,
                    'sort_order' => $position,
                ]);
            }
        }
    }

    private function localizedMenuItems(string $locale): array
    {
        $prefix = $locale === Locale::Vietnamese->value ? '' : '/' . $locale;
        $productChildren = match ($locale) {
            'en' => [
                ['label' => 'Water-based products', 'url' => $prefix . '/products/water-based-products'],
                ['label' => 'Embossing products', 'url' => $prefix . '/products/embossing-products'],
                ['label' => 'Anti-glare AG series', 'url' => $prefix . '/products/anti-glare-ag'],
                ['label' => 'Flame-retardant ink', 'url' => $prefix . '/products/flame-retardant-ink'],
                ['label' => 'Vacuum coating', 'url' => $prefix . '/products/vacuum-coating'],
                ['label' => 'Glass printing ink', 'url' => $prefix . '/products/glass-ink'],
            ],
            'zh' => [
                ['label' => '水性产品系列', 'url' => $prefix . '/products/water-based-products'],
                ['label' => '浮雕印刷系列', 'url' => $prefix . '/products/embossing-products'],
                ['label' => 'AG 防眩系列', 'url' => $prefix . '/products/anti-glare-ag'],
                ['label' => '阻燃油墨', 'url' => $prefix . '/products/flame-retardant-ink'],
                ['label' => '真空镀膜涂料', 'url' => $prefix . '/products/vacuum-coating'],
                ['label' => '玻璃印刷油墨', 'url' => $prefix . '/products/glass-ink'],
            ],
            default => [
                ['label' => 'Dòng sản phẩm gốc nước', 'url' => '/san-pham/dong-san-pham-goc-nuoc'],
                ['label' => 'Dòng sản phẩm in nổi', 'url' => '/san-pham/dong-san-pham-in-noi'],
                ['label' => 'Dòng AG chống chói', 'url' => '/san-pham/dong-ag-chong-choi'],
                ['label' => 'Mực in chống cháy', 'url' => '/san-pham/muc-in-chong-chay'],
                ['label' => 'Sơn phủ mạ chân không', 'url' => '/san-pham/son-phu-ma-chan-khong'],
                ['label' => 'Mực in cho kính', 'url' => '/san-pham/muc-in-cho-kinh'],
            ],
        };

        return [
            [
                'label' => match ($locale) {
                    'en' => 'Home',
                    'zh' => '首页',
                    default => 'Trang chủ',
                },
                'url' => $prefix ?: '/',
            ],
            [
                'label' => match ($locale) {
                    'en' => 'About',
                    'zh' => '关于我们',
                    default => 'Giới thiệu',
                },
                'url' => $prefix . ($locale === 'vi' ? '/gioi-thieu' : '/about'),
            ],
            [
                'label' => match ($locale) {
                    'en' => 'Products',
                    'zh' => '产品',
                    default => 'Sản phẩm',
                },
                'url' => $prefix . ($locale === 'vi' ? '/san-pham' : '/products'),
                'children' => $productChildren,
            ],
            [
                'label' => match ($locale) {
                    'en' => 'Capabilities',
                    'zh' => '能力',
                    default => 'Năng lực',
                },
                'url' => $prefix . ($locale === 'vi' ? '/nang-luc' : '/capabilities'),
            ],
            [
                'label' => match ($locale) {
                    'en' => 'News',
                    'zh' => '新闻',
                    default => 'Tin tức',
                },
                'url' => $prefix . ($locale === 'vi' ? '/tin-tuc' : '/news'),
            ],
            [
                'label' => match ($locale) {
                    'en' => 'Documents',
                    'zh' => '资料',
                    default => 'Tài liệu',
                },
                'url' => $prefix . ($locale === 'vi' ? '/tai-lieu' : '/documents'),
            ],
            [
                'label' => match ($locale) {
                    'en' => 'Contact',
                    'zh' => '联系',
                    default => 'Liên hệ',
                },
                'url' => $prefix . ($locale === 'vi' ? '/lien-he' : '/contact'),
            ],
        ];
    }

}
