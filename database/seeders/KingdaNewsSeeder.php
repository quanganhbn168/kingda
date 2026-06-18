<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KingdaNewsSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::query()->value('id');

        foreach ($this->categories() as $categoryIndex => $categoryData) {
            $category = $this->upsertCategory($categoryData, $categoryIndex);

            foreach ($categoryData['posts'] as $postIndex => $postData) {
                $this->upsertPost($category, $postData, $authorId, ($categoryIndex * 100) + $postIndex);
            }
        }
    }

    private function upsertCategory(array $data, int $sortOrder): Category
    {
        $viSlug = $data['translations']['vi']['slug'];

        $category = Category::query()
            ->where('type', CategoryType::Post->value)
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', 'vi')
                ->where('slug', $viSlug))
            ->first();

        if (! $category) {
            $category = Category::query()->create([
                'type' => CategoryType::Post->value,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]);
        } else {
            $category->update([
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]);
        }

        foreach ($data['translations'] as $locale => $translation) {
            $category->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'slug' => $translation['slug'],
                    'name' => $translation['name'],
                    'description' => $translation['description'],
                    'seo_title' => 'Kingda - ' . $translation['name'],
                    'seo_description' => $translation['description'],
                    'meta_robots' => 'index,follow',
                    'is_published' => true,
                ],
            );
        }

        return $category;
    }

    private function upsertPost(Category $category, array $data, ?int $authorId, int $sortOrder): void
    {
        $viSlug = $data['translations']['vi']['slug'];

        $post = Post::query()
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', 'vi')
                ->where('slug', $viSlug))
            ->first();

        if (! $post) {
            $post = Post::query()->create([
                'category_id' => $category->id,
                'author_id' => $authorId,
                'is_featured' => $data['featured'] ?? false,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]);
        } else {
            $post->update([
                'category_id' => $category->id,
                'author_id' => $post->author_id ?: $authorId,
                'is_featured' => $data['featured'] ?? false,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]);
        }

        foreach ($data['translations'] as $locale => $translation) {
            $post->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'slug' => $translation['slug'] ?? Str::slug($translation['title']),
                    'title' => $translation['title'],
                    'description' => $translation['description'],
                    'content' => $this->content($translation),
                    'seo_title' => 'Kingda - ' . $translation['title'],
                    'seo_description' => $translation['description'],
                    'meta_robots' => 'index,follow',
                    'is_published' => true,
                    'published_at' => now()->subDays($data['days_ago'] ?? 0),
                ],
            );
        }
    }

    private function content(array $translation): string
    {
        $html = '<p>' . e($translation['description']) . '</p>';

        foreach ($translation['sections'] ?? [] as $section) {
            $html .= '<h2>' . e($section['title']) . '</h2>';

            foreach ($section['paragraphs'] ?? [] as $paragraph) {
                $html .= '<p>' . e($paragraph) . '</p>';
            }

            if (! empty($section['items'])) {
                $html .= '<ul>';

                foreach ($section['items'] as $item) {
                    $html .= '<li>' . e($item) . '</li>';
                }

                $html .= '</ul>';
            }
        }

        return $html;
    }

    private function categories(): array
    {
        return [
            [
                'translations' => [
                    'vi' => ['name' => 'Hoạt động Kingda', 'slug' => 'hoat-dong-kingda', 'description' => 'Tin tức, sự kiện và cập nhật từ Kingda.'],
                    'en' => ['name' => 'Kingda updates', 'slug' => 'kingda-updates', 'description' => 'News, events and updates from Kingda.'],
                    'zh' => ['name' => '金达动态', 'slug' => 'kingda-updates', 'description' => '金达新闻、活动与更新。'],
                ],
                'posts' => [
                    [
                        'featured' => true,
                        'days_ago' => 1,
                        'translations' => [
                            'vi' => [
                                'title' => 'Kingda tập trung giải pháp vật liệu cho sản phẩm điện tử 3C',
                                'slug' => 'kingda-tap-trung-giai-phap-vat-lieu-cho-san-pham-dien-tu-3c',
                                'description' => 'Kingda phát triển hệ mực in, sơn phủ và vật liệu chức năng phục vụ điện tử tiêu dùng, kính, composite và linh kiện kỹ thuật.',
                                'sections' => [
                                    ['title' => 'Định hướng phát triển', 'paragraphs' => ['Kingda định vị là nhà cung cấp giải pháp ứng dụng vật liệu in điện tử, tập trung vào các yêu cầu về độ ổn định, bám dính, kháng hóa chất và tính thẩm mỹ bề mặt.']],
                                    ['title' => 'Các nhóm ứng dụng chính', 'items' => ['Sơn và mực dùng cho sản phẩm điện tử tiêu dùng.', 'Sơn cho linh kiện ô tô.', 'Sơn bảo vệ vật liệu composite.', 'Mực in cho kính và bảng điều khiển nhà thông minh.']],
                                ],
                            ],
                            'en' => [
                                'title' => 'Kingda focuses on material solutions for 3C electronics',
                                'slug' => 'kingda-focuses-on-material-solutions-for-3c-electronics',
                                'description' => 'Kingda develops inks, coatings and functional materials for consumer electronics, glass, composites and technical components.',
                                'sections' => [
                                    ['title' => 'Development direction', 'paragraphs' => ['Kingda positions itself as an electronic printing material solution provider focused on stability, adhesion, chemical resistance and surface appearance.']],
                                    ['title' => 'Main application groups', 'items' => ['Inks and coatings for consumer electronics.', 'Coatings for automotive components.', 'Protective coatings for composite materials.', 'Printing inks for glass and smart-home control panels.']],
                                ],
                            ],
                            'zh' => [
                                'title' => '金达聚焦3C电子材料解决方案',
                                'slug' => 'kingda-focuses-on-material-solutions-for-3c-electronics',
                                'description' => '金达开发用于消费电子、玻璃、复合材料和技术部件的油墨、涂料及功能材料。',
                                'sections' => [
                                    ['title' => '发展方向', 'paragraphs' => ['金达定位为电子印刷材料解决方案供应商，关注稳定性、附着力、耐化学性和表面外观。']],
                                    ['title' => '主要应用', 'items' => ['消费电子油墨和涂料。', '汽车零部件涂料。', '复合材料保护涂层。', '玻璃和智能家居控制面板印刷油墨。']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => ['name' => 'Kiến thức vật liệu', 'slug' => 'kien-thuc-vat-lieu', 'description' => 'Kiến thức về mực in, sơn phủ, vật liệu nền và kiểm nghiệm bề mặt.'],
                    'en' => ['name' => 'Material insights', 'slug' => 'material-insights', 'description' => 'Insights on inks, coatings, substrates and surface testing.'],
                    'zh' => ['name' => '材料知识', 'slug' => 'material-insights', 'description' => '油墨、涂料、基材和表面测试知识。'],
                ],
                'posts' => [
                    [
                        'featured' => true,
                        'days_ago' => 4,
                        'translations' => [
                            'vi' => [
                                'title' => 'Những chỉ tiêu quan trọng khi đánh giá sơn phủ bề mặt',
                                'slug' => 'nhung-chi-tieu-quan-trong-khi-danh-gia-son-phu-be-mat',
                                'description' => 'Độ bám dính, mài mòn, kháng hóa chất, độ cứng và khả năng uốn là các chỉ tiêu quan trọng khi chọn hệ sơn phủ.',
                                'sections' => [
                                    ['title' => 'Nhóm chỉ tiêu cơ học', 'items' => ['Độ bám dính trên vật liệu nền.', 'Độ mài mòn RCA hoặc nhung thép.', 'Khả năng uốn/bẻ sau khi tạo hình.']],
                                    ['title' => 'Nhóm chỉ tiêu môi trường', 'items' => ['Kháng hóa chất thường gặp trong quy trình sản xuất.', 'Thử nghiệm QUV hoặc thử nghiệm môi trường.', 'Khả năng chịu nước sôi hoặc độ ẩm cao.']],
                                    ['title' => 'Lựa chọn theo quy trình', 'paragraphs' => ['Không có một hệ sơn phủ phù hợp cho mọi ứng dụng. Vật liệu nền, phương pháp thi công, điều kiện đóng rắn và yêu cầu thẩm mỹ cần được đánh giá đồng thời.']],
                                ],
                            ],
                            'en' => [
                                'title' => 'Key indicators for evaluating surface coatings',
                                'slug' => 'key-indicators-for-evaluating-surface-coatings',
                                'description' => 'Adhesion, abrasion resistance, chemical resistance, hardness and bending performance are key coating selection indicators.',
                                'sections' => [
                                    ['title' => 'Mechanical indicators', 'items' => ['Adhesion on substrate.', 'RCA or steel-wool abrasion resistance.', 'Bending performance after forming.']],
                                    ['title' => 'Environmental indicators', 'items' => ['Resistance to process chemicals.', 'QUV or environmental testing.', 'Boiling water or high-humidity resistance.']],
                                    ['title' => 'Process-based selection', 'paragraphs' => ['No single coating system fits every application. Substrate, application method, curing condition and appearance requirement must be evaluated together.']],
                                ],
                            ],
                            'zh' => [
                                'title' => '评估表面涂层的关键指标',
                                'slug' => 'key-indicators-for-evaluating-surface-coatings',
                                'description' => '附着力、耐磨、耐化学性、硬度和弯折性能是选择涂层体系的重要指标。',
                                'sections' => [
                                    ['title' => '机械性能指标', 'items' => ['基材附着力。', 'RCA或钢丝绒耐磨。', '成型后的弯折性能。']],
                                    ['title' => '环境性能指标', 'items' => ['耐生产过程中的常见化学品。', 'QUV或环境测试。', '耐水煮或高湿。']],
                                    ['title' => '按工艺选择', 'paragraphs' => ['没有一种涂层体系适合所有应用。需要同时评估基材、施工方式、固化条件和外观要求。']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => ['name' => 'Ứng dụng sản phẩm', 'slug' => 'ung-dung-san-pham', 'description' => 'Các ứng dụng thực tế của dòng mực in, sơn phủ và vật liệu chức năng Kingda.'],
                    'en' => ['name' => 'Product applications', 'slug' => 'product-applications', 'description' => 'Practical applications of Kingda inks, coatings and functional materials.'],
                    'zh' => ['name' => '产品应用', 'slug' => 'product-applications', 'description' => '金达油墨、涂料和功能材料的实际应用。'],
                ],
                'posts' => [
                    [
                        'days_ago' => 7,
                        'translations' => [
                            'vi' => [
                                'title' => 'Ứng dụng mực bảo vệ trong quy trình gia công kính',
                                'slug' => 'ung-dung-muc-bao-ve-trong-quy-trinh-gia-cong-kinh',
                                'description' => 'Mực bảo vệ giúp che chắn vùng hiển thị, hỗ trợ cắt phôi, rửa kiềm, rửa nước, uốn nhiệt và khắc ăn mòn kính.',
                                'sections' => [
                                    ['title' => 'Vai trò của mực bảo vệ', 'paragraphs' => ['Trong gia công kính, mực bảo vệ giúp giảm rủi ro xước, nhiễm bẩn hoặc tác động hóa chất lên vùng cần bảo vệ.']],
                                    ['title' => 'Các dạng ứng dụng', 'items' => ['Bảo vệ tạm thời có thể bóc tách.', 'Bảo vệ khi rửa kiềm hoặc rửa nước.', 'Bảo vệ kính 3D trong quá trình uốn nhiệt.', 'Chống HF hoặc khắc ăn mòn.']],
                                ],
                            ],
                            'en' => [
                                'title' => 'Protective ink applications in glass processing',
                                'slug' => 'protective-ink-applications-in-glass-processing',
                                'description' => 'Protective inks shield display areas and support blank cutting, alkaline washing, water washing, hot bending and etching.',
                                'sections' => [
                                    ['title' => 'Role of protective inks', 'paragraphs' => ['In glass processing, protective inks reduce risks of scratching, contamination or chemical impact on protected areas.']],
                                    ['title' => 'Application types', 'items' => ['Temporary peelable protection.', 'Protection during alkaline or water washing.', '3D glass hot-bending protection.', 'HF or etching protection.']],
                                ],
                            ],
                            'zh' => [
                                'title' => '保护油墨在玻璃加工中的应用',
                                'slug' => 'protective-ink-applications-in-glass-processing',
                                'description' => '保护油墨用于保护显示区域，并支持切割、碱洗、水洗、热弯和蚀刻工艺。',
                                'sections' => [
                                    ['title' => '保护油墨的作用', 'paragraphs' => ['在玻璃加工中，保护油墨可降低划伤、污染或化学品影响保护区域的风险。']],
                                    ['title' => '应用类型', 'items' => ['可剥离临时保护。', '碱洗或水洗保护。', '3D玻璃热弯保护。', 'HF或蚀刻保护。']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => ['name' => 'R&D và chứng nhận', 'slug' => 'rd-va-chung-nhan', 'description' => 'Cập nhật về năng lực nghiên cứu, thử nghiệm, chứng nhận và sở hữu trí tuệ.'],
                    'en' => ['name' => 'R&D and certifications', 'slug' => 'rd-and-certifications', 'description' => 'Updates on R&D, testing, certifications and intellectual property.'],
                    'zh' => ['name' => '研发与认证', 'slug' => 'rd-and-certifications', 'description' => '研发、测试、认证和知识产权更新。'],
                ],
                'posts' => [
                    [
                        'days_ago' => 10,
                        'translations' => [
                            'vi' => [
                                'title' => 'Năng lực kiểm nghiệm hỗ trợ phát triển vật liệu theo yêu cầu',
                                'slug' => 'nang-luc-kiem-nghiem-ho-tro-phat-trien-vat-lieu-theo-yeu-cau',
                                'description' => 'Hệ thống kiểm nghiệm giúp đánh giá nguyên vật liệu, bán thành phẩm và thành phẩm trong quá trình phát triển giải pháp.',
                                'sections' => [
                                    ['title' => 'Kiểm soát từ đầu vào đến thành phẩm', 'paragraphs' => ['Đối với vật liệu in điện tử và sơn phủ, kiểm nghiệm không chỉ là bước cuối cùng mà cần xuất hiện xuyên suốt từ nguyên liệu đầu vào đến sản phẩm hoàn thiện.']],
                                    ['title' => 'Giá trị cho khách hàng', 'items' => ['Rút ngắn thời gian thử nghiệm mẫu.', 'Điều chỉnh công thức theo vật liệu nền thực tế.', 'Tăng độ ổn định khi chuyển sang sản xuất hàng loạt.']],
                                ],
                            ],
                            'en' => [
                                'title' => 'Testing capability supports custom material development',
                                'slug' => 'testing-capability-supports-custom-material-development',
                                'description' => 'Testing systems help evaluate raw materials, semi-finished products and finished products during solution development.',
                                'sections' => [
                                    ['title' => 'Control from input to finished product', 'paragraphs' => ['For electronic printing materials and coatings, testing is not only a final step but should run through raw material input to finished product.']],
                                    ['title' => 'Customer value', 'items' => ['Shorter sample validation time.', 'Formula adjustment based on actual substrate.', 'Better stability when moving to mass production.']],
                                ],
                            ],
                            'zh' => [
                                'title' => '检测能力支持定制材料开发',
                                'slug' => 'testing-capability-supports-custom-material-development',
                                'description' => '检测系统帮助在方案开发中评估原材料、半成品和成品。',
                                'sections' => [
                                    ['title' => '从输入到成品的控制', 'paragraphs' => ['对于电子印刷材料和涂层，检测不仅是最终步骤，而应贯穿从原材料到成品的全过程。']],
                                    ['title' => '客户价值', 'items' => ['缩短样品验证时间。', '根据实际基材调整配方。', '提升量产稳定性。']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
