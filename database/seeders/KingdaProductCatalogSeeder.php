<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KingdaProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalog() as $categoryIndex => $categoryData) {
            $category = $this->upsertCategory($categoryData, $categoryIndex);

            foreach ($categoryData['products'] as $productIndex => $productData) {
                $this->upsertProduct($category, $productData, ($categoryIndex * 100) + $productIndex);
            }
        }
    }

    private function upsertCategory(array $data, int $sortOrder): Category
    {
        $viSlug = $data['translations']['vi']['slug'];

        $category = Category::query()
            ->where('type', CategoryType::Product->value)
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', 'vi')
                ->where('slug', $viSlug))
            ->first();

        if (! $category) {
            $category = Category::query()->create([
                'type' => CategoryType::Product->value,
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

    private function upsertProduct(Category $category, array $data, int $sortOrder): void
    {
        $product = Product::query()->updateOrCreate(
            ['sku' => $data['sku']],
            [
                'category_id' => $category->id,
                'unit' => 'solution',
                'is_featured' => $data['featured'] ?? true,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ],
        );

        foreach ($data['translations'] as $locale => $translation) {
            $description = $translation['description'];

            $product->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'slug' => $translation['slug'] ?? Str::slug($translation['name']),
                    'name' => $translation['name'],
                    'description' => $description,
                    'content' => $this->content($description, $translation['features'] ?? [], $translation['process'] ?? null, $locale),
                    'specifications' => $translation['specifications'] ?? null,
                    'blocks' => $this->blocks($data['sku'], $translation, $locale),
                    'seo_title' => 'Kingda - ' . $translation['name'],
                    'seo_description' => $description,
                    'meta_robots' => 'index,follow',
                    'is_published' => true,
                    'published_at' => now(),
                ],
            );
        }
    }

    private function content(string $description, array $features, ?string $process, string $locale): string
    {
        $featureHeading = match ($locale) {
            'en' => 'Advantages and key properties',
            'zh' => '优势与特性',
            default => 'Ưu điểm và đặc tính',
        };
        $processHeading = match ($locale) {
            'en' => 'Process and applications',
            'zh' => '工艺与应用',
            default => 'Quy trình và ứng dụng',
        };

        $html = '<p>' . e($description) . '</p>';

        if ($features !== []) {
            $html .= '<h2>' . e($featureHeading) . '</h2><ul>';

            foreach ($features as $feature) {
                $html .= '<li>' . e($feature) . '</li>';
            }

            $html .= '</ul>';
        }

        if (filled($process)) {
            $html .= '<h2>' . e($processHeading) . '</h2><p>' . e($process) . '</p>';
        }

        return $html;
    }

    private function blocks(string $sku, array $translation, string $locale): array
    {
        return [
            'overview' => $translation['description'],
            'applications' => $translation['applications'] ?? $this->defaultApplications($sku, $locale),
            'substrates' => $translation['substrates'] ?? $this->defaultSubstrates($sku),
            'features' => $translation['features'] ?? [],
            'process' => $translation['process'] ?? null,
            'notes' => $translation['notes'] ?? [],
        ];
    }

    private function defaultApplications(string $sku, string $locale): array
    {
        if ($locale === 'en') {
            return match ($sku) {
                'WS-30C' => ['3C electronic product surfaces', 'Color base and transparent top coats', 'Rear laser engraving process'],
                'EMBOSS-3D' => ['3D decorative texture', 'Metallic sand, snow, stone-like and CD textures', 'Covers/parts requiring 3D forming'],
                'AG-HARDCOAT' => ['AG anti-glare surface', 'Anti-fingerprint parts', 'Fine transparent surfaces'],
                'FR-INK-SHEET' => ['Flame-retardant plastic sheets', 'PMMA or PC/PMMA structures', 'Products requiring UL94 V-0'],
                'UV-HARDCOAT' => ['UV hard coat', 'Injected PC products', 'Composite sheets'],
                'GLASS-PROTECTIVE-INK' => ['Display-area glass protection', 'Blank cutting, alkaline washing and water washing', '3D glass hot bending and etching'],
                'UV-LR-HM-CM' => ['Front glass', 'Exposure-development printing', 'Pad and screen printing with sharp edges'],
                'PT-HL-HG-JM' => ['Back glass', 'PET/TPU', 'Mirror silver and plating effects'],
                'UVB-UVA-HA' => ['PET film', 'UV offset color-shift printing', 'Electroplated decorative structure'],
                'HV-JM-HL-COMPOSITE' => ['Composite sheets', 'Translucent inks', 'Mirror silver and base-covering layers'],
                'NCVM-COATING' => ['NCVM vacuum plating', 'Plastic metallization effects', 'Optical and ceramic-like effects'],
                'PDS-LDS-NO-GRIND' => ['PDS/LDS antennas', 'Direct surface printing', 'No-grinding process'],
                default => ['Industrial material applications', 'Electronic products and technical components'],
            };
        }

        if ($locale === 'zh') {
            return match ($sku) {
                'WS-30C' => ['3C电子产品表面', '颜色底漆与透明面漆', '背面激光雕刻工艺'],
                'EMBOSS-3D' => ['3D装饰纹理', '金属砂、雪花、仿石和CD纹理', '需要3D成型的盖板/部件'],
                'AG-HARDCOAT' => ['AG防眩表面', '防指纹部件', '细腻透明表面'],
                'FR-INK-SHEET' => ['阻燃塑料板材', 'PMMA或PC/PMMA结构', '需要UL94 V-0的产品'],
                'UV-HARDCOAT' => ['UV强化涂层', '注塑PC产品', '复合板材'],
                'GLASS-PROTECTIVE-INK' => ['显示区域玻璃保护', '切割、碱洗和水洗', '3D热弯和蚀刻'],
                'UV-LR-HM-CM' => ['前盖玻璃', '曝光显影印刷', '边缘清晰的移印和丝印'],
                'PT-HL-HG-JM' => ['后盖玻璃', 'PET/TPU', '镜面银和镀层效果'],
                'UVB-UVA-HA' => ['PET膜', 'UV胶印变色效果', '电镀装饰结构'],
                'HV-JM-HL-COMPOSITE' => ['复合板材', '半透明油墨', '镜面银和遮盖底层'],
                'NCVM-COATING' => ['NCVM真空镀膜', '塑料金属化效果', '光学和仿陶瓷效果'],
                'PDS-LDS-NO-GRIND' => ['PDS/LDS天线', '表面直接印刷', '免打磨工艺'],
                default => ['工业材料应用', '电子产品和技术部件'],
            };
        }

        return match ($sku) {
            'WS-30C' => ['Bề mặt sản phẩm điện tử 3C', 'Lớp nền màu và lớp phủ trong suốt', 'Quy trình khắc laser mặt sau'],
            'EMBOSS-3D' => ['Hoa văn trang trí 3D', 'Vân cát ánh kim, vân tuyết, vân giả đá, vân CD', 'Ốp/lưng/chi tiết cần tạo hình 3D'],
            'AG-HARDCOAT' => ['Bề mặt chống chói AG', 'Chi tiết cần chống dấu vân tay', 'Bề mặt cần độ mịn và độ trong suốt cao'],
            'FR-INK-SHEET' => ['Tấm nhựa chống cháy', 'Cấu trúc PMMA hoặc PC/PMMA', 'Sản phẩm cần đạt UL94 V-0'],
            'UV-HARDCOAT' => ['Lớp phủ tăng cứng UV', 'Sản phẩm PC ép phun', 'Tấm vật liệu composite'],
            'GLASS-PROTECTIVE-INK' => ['Bảo vệ vùng hiển thị kính', 'Cắt phôi, rửa kiềm, rửa nước', 'Uốn nhiệt kính 3D và khắc ăn mòn'],
            'UV-LR-HM-CM' => ['Kính mặt trước', 'In phơi sáng-hiện hình', 'In pad và in lụa viền sắc nét'],
            'PT-HL-HG-JM' => ['Kính mặt sau', 'PET/TPU', 'Hiệu ứng bạc gương và lớp mạ'],
            'UVB-UVA-HA' => ['Màng PET', 'In offset UV hiệu ứng chuyển màu', 'Cấu trúc mạ điện trang trí'],
            'HV-JM-HL-COMPOSITE' => ['Tấm composite', 'Mực bán trong suốt', 'Hiệu ứng bạc gương và phủ nền'],
            'NCVM-COATING' => ['Mạ chân không NCVM', 'Hiệu ứng kim loại hóa nhựa', 'Hiệu ứng quang học và giả gốm'],
            'PDS-LDS-NO-GRIND' => ['Anten PDS/LDS', 'Bề mặt cần in trực tiếp', 'Quy trình không cần mài'],
            default => ['Ứng dụng vật liệu công nghiệp', 'Sản phẩm điện tử và linh kiện kỹ thuật'],
        };
    }

    private function defaultSubstrates(string $sku): array
    {
        return match ($sku) {
            'FR-INK-SHEET' => ['PMMA', 'PC/PMMA composite', 'Tấm nhựa kỹ thuật'],
            'UV-HARDCOAT' => ['PC ép phun áp suất cao', 'Tấm composite'],
            'GLASS-PROTECTIVE-INK', 'UV-LR-HM-CM', 'PT-HL-HG-JM' => ['Kính mặt trước', 'Kính mặt sau', 'PET', 'TPU'],
            'UVB-UVA-HA' => ['Màng PET'],
            'HV-JM-HL-COMPOSITE' => ['Tấm composite', 'PC'],
            'NCVM-COATING' => ['PC+ABS', 'PC', 'ABS', 'PC+GF'],
            'PDS-LDS-NO-GRIND' => ['Phôi nhựa PDS/LDS', 'Nền PU'],
            default => ['PC', 'PMMA', 'Composite', 'Vật liệu điện tử 3C'],
        };
    }

    private function catalog(): array
    {
        return [
            [
                'translations' => [
                    'vi' => [
                        'name' => 'Dòng sản phẩm gốc nước',
                        'slug' => 'dong-san-pham-goc-nuoc',
                        'description' => 'Vật liệu phủ gốc nước và phân tán polyurethane dùng cho bề mặt sản phẩm điện tử, lớp nền màu và lớp phủ trong suốt.',
                    ],
                    'en' => [
                        'name' => 'Water-based product series',
                        'slug' => 'water-based-product-series',
                        'description' => 'Water-based coatings and polyurethane dispersions for electronic product surfaces, color base layers and transparent top coats.',
                    ],
                    'zh' => [
                        'name' => '水性产品系列',
                        'slug' => 'water-based-product-series',
                        'description' => '用于电子产品表面、颜色底层和透明面漆的水性涂料及聚氨酯分散体。',
                    ],
                ],
                'products' => [
                    [
                        'sku' => 'WS-30C',
                        'translations' => [
                            'vi' => [
                                'name' => 'WS-30C - Polyurethane dispersions',
                                'slug' => 'ws-30c-polyurethane-dispersions',
                                'description' => 'Dung dịch phân tán polyurethane gốc nước cho bề mặt có cảm giác tốt, bám dính trực tiếp trên nền vật liệu và độ bền mài mòn cao.',
                                'features' => [
                                    'Cảm giác bề mặt tốt, bám dính trực tiếp trên nền vật liệu.',
                                    'Độ mài mòn RCA 175gf/250 lần, cao hơn mức phổ biến trên thị trường.',
                                    'Kháng hóa chất tốt, đã thử nghiệm với hơn 30 loại hóa chất.',
                                    'Khả năng phân hủy đạt khoảng 300 giờ trong thử nghiệm QUV.',
                                ],
                                'process' => 'Phù hợp cấu trúc sản phẩm 2 lớp: lớp nền màu và lớp sơn phủ trong suốt; hỗ trợ quy trình khắc laser mặt sau qua lớp sơn phủ.',
                            ],
                            'en' => [
                                'name' => 'WS-30C - Polyurethane dispersions',
                                'slug' => 'ws-30c-polyurethane-dispersions',
                                'description' => 'Water-based polyurethane dispersion with good surface touch, direct adhesion to substrates and high wear resistance.',
                                'features' => [
                                    'Good surface feel and direct substrate adhesion.',
                                    'RCA abrasion resistance reaches 175gf/250 cycles.',
                                    'Good chemical resistance, tested with more than 30 chemicals.',
                                    'QUV degradation resistance reaches about 300 hours.',
                                ],
                                'process' => 'Suitable for two-layer structures with color base coat and transparent top coat; supports rear laser engraving processes.',
                            ],
                            'zh' => [
                                'name' => 'WS-30C 聚氨酯分散体',
                                'slug' => 'ws-30c-polyurethane-dispersions',
                                'description' => '水性聚氨酯分散体，具有良好的表面手感、基材附着力和耐磨性能。',
                                'features' => [
                                    '表面手感好，可直接附着于基材。',
                                    'RCA耐磨可达175gf/250次。',
                                    '耐化学品性能良好，已测试30多种化学品。',
                                    'QUV测试约300小时。',
                                ],
                                'process' => '适用于颜色底漆加透明面漆的双层结构，并支持背面激光雕刻工艺。',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => [
                        'name' => 'Dòng sản phẩm in nổi',
                        'slug' => 'dong-san-pham-in-noi',
                        'description' => 'Dung dịch tăng cường in nổi hoa văn 3D bằng phương pháp phủ tràn, phù hợp tạo hiệu ứng trang trí bền mài mòn.',
                    ],
                    'en' => [
                        'name' => 'Embossing product series',
                        'slug' => 'embossing-product-series',
                        'description' => 'Flow-coating enhancement solution for 3D embossed patterns, suitable for decorative textures with high abrasion resistance.',
                    ],
                    'zh' => [
                        'name' => '浮雕印刷系列',
                        'slug' => 'embossing-product-series',
                        'description' => '用于3D浮雕纹理的淋涂增强方案，适合高耐磨装饰纹理。',
                    ],
                ],
                'products' => [
                    [
                        'sku' => 'EMBOSS-3D',
                        'translations' => [
                            'vi' => [
                                'name' => 'Dung dịch tăng cường in nổi hoa văn 3D',
                                'slug' => 'dung-dich-tang-cuong-in-noi-hoa-van-3d',
                                'description' => 'Giải pháp phủ tràn cho hoa văn in nổi 3D áp lực cao, giúp họa tiết rõ nét, dễ bóc màng và chịu tạo hình tốt.',
                                'features' => [
                                    'Họa tiết in nổi rõ ràng, dễ bóc màng.',
                                    'Chịu được tạo hình 3D áp lực cao, không nứt.',
                                    'Độ mài mòn bằng nhung thép 1000gf/1000 lần.',
                                    'Luộc nước 100°C trong 30 phút không nổi bọt và không bong sơn.',
                                    'Góc tiếp xúc giọt nước >105°, chống dấu vân tay tốt.',
                                ],
                                'process' => 'Phủ tràn, sấy, tạo vân, tiền đóng rắn LED UV, bóc màng, tạo hình 3D áp lực cao, đóng rắn UV lần hai và CNC.',
                            ],
                            'en' => [
                                'name' => '3D embossing enhancement solution',
                                'slug' => '3d-embossing-enhancement-solution',
                                'description' => 'Flow-coating solution for high-pressure 3D embossed patterns with clear texture, easy film release and forming resistance.',
                                'features' => [
                                    'Clear embossed texture and easy release.',
                                    'Withstands high-pressure 3D forming without cracking.',
                                    'Steel wool abrasion resistance 1000gf/1000 cycles.',
                                    'No blistering or peeling after boiling at 100°C for 30 minutes.',
                                    'Water contact angle >105° with good anti-fingerprint performance.',
                                ],
                                'process' => 'Flow coating, drying, embossing, LED UV pre-curing, pattern film release, high-pressure 3D forming, secondary UV curing and CNC.',
                            ],
                            'zh' => [
                                'name' => '3D浮雕纹理增强液',
                                'slug' => '3d-embossing-enhancement-solution',
                                'description' => '适用于高压3D浮雕纹理的淋涂方案，纹理清晰、易脱膜并具备良好成型性能。',
                                'features' => [
                                    '浮雕纹理清晰，易脱膜。',
                                    '可承受高压3D成型不开裂。',
                                    '钢丝绒耐磨1000gf/1000次。',
                                    '100°C水煮30分钟不起泡、不脱漆。',
                                    '水接触角>105°，防指纹性能好。',
                                ],
                                'process' => '淋涂、干燥、压纹、LED UV预固化、脱膜、高压3D成型、二次UV固化和CNC。',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => [
                        'name' => 'Dòng AG chống chói',
                        'slug' => 'dong-ag-chong-choi',
                        'description' => 'Dung dịch tăng cường hiệu ứng AG chống chói, bề mặt mịn trong suốt, chịu mài mòn và chống dấu vân tay.',
                    ],
                    'en' => [
                        'name' => 'Anti-glare AG series',
                        'slug' => 'anti-glare-ag-series',
                        'description' => 'AG anti-glare enhancement solution with fine transparent surface, abrasion resistance and anti-fingerprint performance.',
                    ],
                    'zh' => [
                        'name' => 'AG防眩系列',
                        'slug' => 'anti-glare-ag-series',
                        'description' => 'AG防眩增强液，表面细腻透明，具备耐磨和防指纹性能。',
                    ],
                ],
                'products' => [
                    [
                        'sku' => 'AG-HARDCOAT',
                        'translations' => [
                            'vi' => [
                                'name' => 'Dung dịch tăng cường hiệu ứng AG chống chói',
                                'slug' => 'dung-dich-tang-cuong-hieu-ung-ag-chong-choi',
                                'description' => 'Dung dịch phủ tràn tạo hiệu ứng AG chống chói chịu mài mòn cao, bề mặt đẹp, mịn và trong suốt.',
                                'features' => [
                                    'Hiệu ứng bề mặt đẹp, mịn và trong suốt.',
                                    'Cảm giác bề mặt trơn mượt.',
                                    'Độ mài mòn bằng nhung thép 1000gf/3000 lần.',
                                    'Góc tiếp xúc giọt nước >110°, chống dấu vân tay tốt.',
                                    'Uốn trụ Φ40mm không nứt.',
                                ],
                                'process' => 'Phủ tràn, sấy, đóng rắn UV bằng đèn thủy ngân và CNC; có thể điều chỉnh độ bóng và độ dày màng sơn tại hiện trường.',
                            ],
                            'en' => [
                                'name' => 'AG anti-glare enhancement solution',
                                'slug' => 'ag-anti-glare-enhancement-solution',
                                'description' => 'Flow-coating AG anti-glare solution with high abrasion resistance and a fine transparent surface.',
                                'features' => [
                                    'Fine, transparent and attractive surface effect.',
                                    'Smooth surface touch.',
                                    'Steel wool abrasion resistance 1000gf/3000 cycles.',
                                    'Water contact angle >110° with good anti-fingerprint performance.',
                                    'No cracking when bent around Φ40mm cylinder.',
                                ],
                                'process' => 'Flow coating, drying, mercury lamp UV curing and CNC; gloss and coating thickness can be adjusted on site.',
                            ],
                            'zh' => [
                                'name' => 'AG防眩增强液',
                                'slug' => 'ag-anti-glare-enhancement-solution',
                                'description' => '高耐磨淋涂AG防眩方案，表面细腻透明。',
                                'features' => [
                                    '表面效果美观、细腻、透明。',
                                    '表面手感顺滑。',
                                    '钢丝绒耐磨1000gf/3000次。',
                                    '水接触角>110°，防指纹性能好。',
                                    'Φ40mm弯折不开裂。',
                                ],
                                'process' => '淋涂、干燥、汞灯UV固化和CNC；光泽和膜厚可现场调节。',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => [
                        'name' => 'Mực in chống cháy và phủ tăng cường UV',
                        'slug' => 'muc-in-chong-chay-va-phu-tang-cuong-uv',
                        'description' => 'Nhóm mực in chống cháy, lớp phủ tăng cứng UV và giải pháp phủ cho tấm PMMA, PC/PMMA, PC ép phun và vật liệu composite.',
                    ],
                    'en' => [
                        'name' => 'Flame-retardant inks and UV hard coating',
                        'slug' => 'flame-retardant-inks-and-uv-hard-coating',
                        'description' => 'Flame-retardant inks, UV hard coating and coating solutions for PMMA, PC/PMMA, injected PC and composite sheets.',
                    ],
                    'zh' => [
                        'name' => '阻燃油墨与UV强化涂层',
                        'slug' => 'flame-retardant-inks-and-uv-hard-coating',
                        'description' => '适用于PMMA、PC/PMMA、注塑PC和复合板材的阻燃油墨及UV强化涂层方案。',
                    ],
                ],
                'products' => [
                    [
                        'sku' => 'FR-INK-SHEET',
                        'translations' => [
                            'vi' => [
                                'name' => 'Mực in chống cháy cho vật liệu dạng tấm',
                                'slug' => 'muc-in-chong-chay-cho-vat-lieu-dang-tam',
                                'description' => 'Mực in chống cháy dùng cho tấm PMMA hoặc composite PC/PMMA, có khả năng tự chống cháy, tản nhiệt và cách điện.',
                                'features' => [
                                    'Khả năng tự chống cháy rất tốt, hỗ trợ tản nhiệt và cách điện.',
                                    'Thử nghiệm kim lửa cho thời gian cháy trên 80-120 giây.',
                                    'Cấp độ chống cháy đạt tiêu chuẩn UL94 V-0.',
                                    'Không chứa halogen, thân thiện môi trường.',
                                    'Phù hợp nhiều loại tấm nhựa khác nhau.',
                                ],
                                'process' => 'Ứng dụng trong cấu trúc PMMA hoặc PC/PMMA với lớp HC/AG, nhiều lớp màu/hiệu ứng và lớp mực chống cháy.',
                            ],
                            'en' => [
                                'name' => 'Flame-retardant ink for sheet materials',
                                'slug' => 'flame-retardant-ink-for-sheet-materials',
                                'description' => 'Flame-retardant ink for PMMA or PC/PMMA composite sheets with self-extinguishing, heat dissipation and insulation performance.',
                                'features' => [
                                    'Excellent self-extinguishing, heat dissipation and insulation performance.',
                                    'Needle flame test burning time over 80-120 seconds.',
                                    'Flame retardancy reaches UL94 V-0.',
                                    'Halogen-free and environmentally friendly.',
                                    'Suitable for multiple plastic sheet substrates.',
                                ],
                                'process' => 'Used in PMMA or PC/PMMA structures with HC/AG layers, multi-layer color/effect layers and flame-retardant ink.',
                            ],
                            'zh' => [
                                'name' => '板材阻燃油墨',
                                'slug' => 'flame-retardant-ink-for-sheet-materials',
                                'description' => '用于PMMA或PC/PMMA复合板材的阻燃油墨，具备自阻燃、散热和绝缘性能。',
                                'features' => [
                                    '自阻燃、散热和绝缘性能优异。',
                                    '针焰测试燃烧时间80-120秒以上。',
                                    '阻燃等级达到UL94 V-0。',
                                    '无卤环保。',
                                    '适用于多种塑料板材。',
                                ],
                                'process' => '用于PMMA或PC/PMMA结构，包含HC/AG层、多层颜色/效果层和阻燃油墨层。',
                            ],
                        ],
                    ],
                    [
                        'sku' => 'UV-HARDCOAT',
                        'translations' => [
                            'vi' => [
                                'name' => 'Dung dịch phủ tăng cường UV',
                                'slug' => 'dung-dich-phu-tang-cuong-uv',
                                'description' => 'Dung dịch phủ tăng cứng UV thi công bằng phủ tràn hoặc phun sơn, dẫn đầu về chống mài mòn, độ cứng và khả năng uốn.',
                                'features' => [
                                    'Thi công bằng phủ tràn hoặc phun sơn.',
                                    'Phù hợp PC ép phun áp suất cao và vật liệu tấm composite.',
                                    'Chống mài mòn, độ cứng và khả năng uốn/bẻ ở mức cao.',
                                ],
                                'process' => 'Dùng làm lớp phủ tăng cứng UV cho các vật liệu phổ biến trong ngành điện tử và composite.',
                            ],
                            'en' => [
                                'name' => 'UV hard coating solution',
                                'slug' => 'uv-hard-coating-solution',
                                'description' => 'UV hard coating solution applied by flow coating or spraying, with leading abrasion resistance, hardness and bending performance.',
                                'features' => [
                                    'Applied by flow coating or spraying.',
                                    'Suitable for high-pressure injected PC and composite sheets.',
                                    'High abrasion resistance, hardness and bending performance.',
                                ],
                                'process' => 'Used as a UV hard coat for mainstream electronic and composite materials.',
                            ],
                            'zh' => [
                                'name' => 'UV强化涂层液',
                                'slug' => 'uv-hard-coating-solution',
                                'description' => '可淋涂或喷涂的UV强化涂层方案，耐磨、硬度和弯折性能优异。',
                                'features' => [
                                    '可采用淋涂或喷涂施工。',
                                    '适用于高压注塑PC和复合板材。',
                                    '耐磨、硬度和弯折性能高。',
                                ],
                                'process' => '用于电子和复合材料的UV强化保护层。',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => [
                        'name' => 'Giải pháp mực in cho kính',
                        'slug' => 'giai-phap-muc-in-cho-kinh',
                        'description' => 'Hệ mực bảo vệ và mực trang trí cho gia công kính, kính mặt trước, kính mặt sau, PET, TPU và mực bạc gương.',
                    ],
                    'en' => [
                        'name' => 'Glass printing ink solutions',
                        'slug' => 'glass-printing-ink-solutions',
                        'description' => 'Protective and decorative ink systems for glass processing, front glass, back glass, PET, TPU and mirror silver effects.',
                    ],
                    'zh' => [
                        'name' => '玻璃印刷油墨方案',
                        'slug' => 'glass-printing-ink-solutions',
                        'description' => '用于玻璃加工、前盖玻璃、后盖玻璃、PET、TPU和镜面银效果的保护及装饰油墨体系。',
                    ],
                ],
                'products' => [
                    [
                        'sku' => 'GLASS-PROTECTIVE-INK',
                        'translations' => [
                            'vi' => [
                                'name' => 'Mực bảo vệ cho quy trình gia công kính',
                                'slug' => 'muc-bao-ve-cho-quy-trinh-gia-cong-kinh',
                                'description' => 'Hệ mực bảo vệ tạm thời cho vùng hiển thị, rửa kiềm, rửa nước, uốn nhiệt kính 3D và chống HF/khắc ăn mòn.',
                                'features' => [
                                    'Có thể bóc tách tạm thời để bảo vệ vùng hiển thị.',
                                    'Có dạng nhiệt rắn không dung môi, gốc nước và dung môi.',
                                    'Có dòng chịu nhiệt cao đến 900°C cho uốn nhiệt kính 3D.',
                                    'Mực chống HF/khắc ăn mòn chịu dung dịch axit 30% trong 1 giờ và dễ tẩy.',
                                ],
                                'process' => 'Phù hợp bảo vệ trong công đoạn cắt phôi, rửa kiềm, rửa nước, uốn nhiệt và khắc ăn mòn kính.',
                            ],
                            'en' => [
                                'name' => 'Protective inks for glass processing',
                                'slug' => 'protective-inks-for-glass-processing',
                                'description' => 'Temporary protective ink systems for display areas, alkaline washing, water washing, 3D glass hot bending and HF etching protection.',
                                'features' => [
                                    'Temporary peelable protection for display areas.',
                                    'Available in solvent-free thermosetting, water-based and solvent-based types.',
                                    'High-temperature series withstands up to 900°C for 3D glass hot bending.',
                                    'HF/etching protection withstands 30% acid solution for 1 hour and is easy to remove.',
                                ],
                                'process' => 'Suitable for blank cutting, alkaline washing, water washing, hot bending and glass etching processes.',
                            ],
                            'zh' => [
                                'name' => '玻璃加工保护油墨',
                                'slug' => 'protective-inks-for-glass-processing',
                                'description' => '用于显示区域、碱洗、水洗、3D热弯和HF蚀刻保护的临时保护油墨体系。',
                                'features' => [
                                    '可临时剥离，保护显示区域。',
                                    '包括无溶剂热固、水性和溶剂型。',
                                    '高温系列可耐900°C，用于3D玻璃热弯。',
                                    '抗HF/蚀刻油墨可耐30%酸液1小时，易清洗。',
                                ],
                                'process' => '适用于切割、碱洗、水洗、热弯和玻璃蚀刻工艺。',
                            ],
                        ],
                    ],
                    [
                        'sku' => 'UV-LR-HM-CM',
                        'translations' => [
                            'vi' => [
                                'name' => 'Mực in cho kính mặt trước',
                                'slug' => 'muc-in-cho-kinh-mat-truoc',
                                'description' => 'Bộ mực cho kính mặt trước gồm dòng UV-LR phơi sáng-hiện hình, HM in pad và CM in lụa.',
                                'features' => [
                                    'UV-LR có bám dính và chịu hóa chất vượt trội, không đổi màu, viền in sắc nét.',
                                    'HM in pad cho mép in sắc nét, không khuyết cạnh, không lem.',
                                    'CM in lụa có màng mực đồng đều, mịn, độ che phủ cao và chịu hóa chất tốt.',
                                ],
                                'process' => 'Dùng cho các chi tiết kính mặt trước cần độ sắc nét, độ bám dính và kháng hóa chất cao.',
                            ],
                            'en' => [
                                'name' => 'Front glass printing inks',
                                'slug' => 'front-glass-printing-inks',
                                'description' => 'Front glass ink set including UV-LR exposure-development ink, HM pad printing ink and CM screen printing ink.',
                                'features' => [
                                    'UV-LR offers strong adhesion, chemical resistance, no discoloration and sharp edges.',
                                    'HM pad printing creates sharp edges without missing edges or bleeding.',
                                    'CM screen printing delivers smooth film, high hiding power and chemical resistance.',
                                ],
                                'process' => 'For front glass parts requiring sharp printing, adhesion and chemical resistance.',
                            ],
                            'zh' => [
                                'name' => '前盖玻璃印刷油墨',
                                'slug' => 'front-glass-printing-inks',
                                'description' => '前盖玻璃油墨组合，包括UV-LR曝光显影、HM移印和CM丝印油墨。',
                                'features' => [
                                    'UV-LR附着力和耐化学性强，不变色，边缘清晰。',
                                    'HM移印边缘锐利，不缺边、不扩散。',
                                    'CM丝印墨膜均匀细腻，遮盖力高，耐化学性好。',
                                ],
                                'process' => '用于需要高清晰度、强附着和耐化学性的前盖玻璃部件。',
                            ],
                        ],
                    ],
                    [
                        'sku' => 'PT-HL-HG-JM',
                        'translations' => [
                            'vi' => [
                                'name' => 'Mực in cho kính mặt sau và vật liệu PET/TPU',
                                'slug' => 'muc-in-cho-kinh-mat-sau-va-vat-lieu-pet-tpu',
                                'description' => 'Giải pháp mực phun phủ PT, mực PET HL, mực TPU HG và mực bạc gương JM cho kính mặt sau.',
                                'features' => [
                                    'PT có độ bám dính lớp mạ tốt, tương thích phun tốt, bề mặt mịn phẳng.',
                                    'HL cho PET có mực mịn, mềm, phẳng và kháng hóa chất tốt.',
                                    'HG cho TPU có khả năng kéo giãn tốt và bề mặt mịn.',
                                    'JM tạo hiệu ứng gương sáng, đường in sắc nét, phù hợp máy tự động.',
                                ],
                                'process' => 'Dùng cho kính mặt sau, PET, TPU và các hiệu ứng trang trí cần bề mặt phẳng, độ bám dính lớp mạ và hiệu ứng gương.',
                            ],
                            'en' => [
                                'name' => 'Back glass and PET/TPU printing inks',
                                'slug' => 'back-glass-and-pet-tpu-printing-inks',
                                'description' => 'PT spray ink, HL PET ink, HG TPU ink and JM mirror silver ink solutions for back glass applications.',
                                'features' => [
                                    'PT has good plating adhesion, spray compatibility and smooth flat surface.',
                                    'HL for PET provides smooth soft film and chemical resistance.',
                                    'HG for TPU has good stretchability and smooth surface.',
                                    'JM creates bright mirror effect and sharp automatic printing lines.',
                                ],
                                'process' => 'For back glass, PET, TPU and decorative effects requiring flat surface, plating adhesion and mirror brightness.',
                            ],
                            'zh' => [
                                'name' => '后盖玻璃及PET/TPU印刷油墨',
                                'slug' => 'back-glass-and-pet-tpu-printing-inks',
                                'description' => '用于后盖玻璃的PT喷涂、HL PET、HG TPU和JM镜面银油墨方案。',
                                'features' => [
                                    'PT镀层附着力好，喷涂适应性好，表面平整。',
                                    'HL用于PET，墨层细腻柔软，耐化学性好。',
                                    'HG用于TPU，拉伸性能好，表面细腻。',
                                    'JM镜面效果亮，线条清晰，适合自动化印刷。',
                                ],
                                'process' => '用于后盖玻璃、PET、TPU和需要平整表面、镀层附着及镜面效果的装饰应用。',
                            ],
                        ],
                    ],
                    [
                        'sku' => 'UVB-UVA-HA',
                        'translations' => [
                            'vi' => [
                                'name' => 'Mực in offset hiệu ứng chuyển màu',
                                'slug' => 'muc-in-offset-hieu-ung-chuyen-mau',
                                'description' => 'Giải pháp in offset UV trên màng PET với mực UVB, keo chuyển in UVA và mực nền HA cho hiệu ứng chuyển màu.',
                                'features' => [
                                    'Mực in offset độ mịn ≤3um, độ nhớt 100.000-130.000 CPS, hàm lượng rắn 100%.',
                                    'Keo chuyển in UV độ mịn ≤3um, độ nhớt 4.000-7.000 CPS, độ bóng ≥60.',
                                    'Dải màu gồm trắng, đen, vàng, hồng, đỏ, tím, xanh lục lam và xanh thẫm.',
                                ],
                                'process' => 'Cấu trúc gồm màng PET, in offset UV, keo chuyển in UV, mạ điện, in lụa phủ nền đen và in lụa màu đen Dain.',
                            ],
                            'en' => [
                                'name' => 'Color-shift offset printing ink solution',
                                'slug' => 'color-shift-offset-printing-ink-solution',
                                'description' => 'UV offset printing solution on PET film using UVB ink, UVA transfer adhesive and HA base ink for color-shift effects.',
                                'features' => [
                                    'Offset ink fineness ≤3um, viscosity 100,000-130,000 CPS and 100% solid content.',
                                    'UV transfer adhesive fineness ≤3um, viscosity 4,000-7,000 CPS and gloss ≥60.',
                                    'Color range includes white, black, yellow, rose, red, purple, cyan green and deep blue.',
                                ],
                                'process' => 'Structure includes PET film, UV offset printing, UV transfer adhesive, electroplating, black base screen printing and Dain black screen printing.',
                            ],
                            'zh' => [
                                'name' => '变色效果UV胶印油墨方案',
                                'slug' => 'color-shift-offset-printing-ink-solution',
                                'description' => '用于PET膜的UV胶印方案，采用UVB油墨、UVA转印胶和HA底墨形成变色效果。',
                                'features' => [
                                    '胶印油墨细度≤3um，粘度100,000-130,000 CPS，固含100%。',
                                    'UV转印胶细度≤3um，粘度4,000-7,000 CPS，光泽≥60。',
                                    '颜色包括白、黑、黄、玫红、红、紫、青绿和深蓝。',
                                ],
                                'process' => '结构包括PET膜、UV胶印、UV转印胶、电镀、黑色底层丝印和Dain黑色丝印。',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => [
                        'name' => 'Mực in cho vật liệu composite',
                        'slug' => 'muc-in-cho-vat-lieu-composite',
                        'description' => 'Nhóm mực in cho tấm composite gồm mực bán trong suốt HV, bạc gương JM và mực phủ nền HL.',
                    ],
                    'en' => [
                        'name' => 'Composite sheet printing inks',
                        'slug' => 'composite-sheet-printing-inks',
                        'description' => 'Printing ink group for composite sheets including HV translucent ink, JM mirror silver and HL base-covering ink.',
                    ],
                    'zh' => [
                        'name' => '复合板材印刷油墨',
                        'slug' => 'composite-sheet-printing-inks',
                        'description' => '用于复合板材的印刷油墨，包括HV半透明、JM镜面银和HL遮盖底墨。',
                    ],
                ],
                'products' => [
                    [
                        'sku' => 'HV-JM-HL-COMPOSITE',
                        'translations' => [
                            'vi' => [
                                'name' => 'Mực in cho tấm vật liệu composite',
                                'slug' => 'muc-in-cho-tam-vat-lieu-composite',
                                'description' => 'Bộ mực HV, JM và HL cho tấm composite, chú trọng bám dính giữa các lớp, bề mặt mịn và kháng hóa chất.',
                                'features' => [
                                    'HV bán trong suốt có bám dính giữa các lớp tốt, không làm cháy bề mặt PC, tự san phẳng tốt.',
                                    'JM bạc gương có độ sáng tốt, mực mịn, không rỗ bề mặt và kháng hóa chất tốt.',
                                    'HL phủ nền có khả năng kéo giãn tốt, màng mực phẳng mịn, che phủ cao.',
                                ],
                                'process' => 'Dùng cho các tấm composite cần hiệu ứng bán trong suốt, gương bạc hoặc lớp phủ nền có độ che phủ cao.',
                            ],
                            'en' => [
                                'name' => 'Printing inks for composite sheets',
                                'slug' => 'printing-inks-for-composite-sheets',
                                'description' => 'HV, JM and HL ink set for composite sheets, focused on interlayer adhesion, smooth surface and chemical resistance.',
                                'features' => [
                                    'HV translucent ink has good interlayer adhesion, does not burn PC surface and self-levels well.',
                                    'JM mirror silver has good brightness, smooth ink film, no pitting and good chemical resistance.',
                                    'HL base-covering ink has good stretchability, smooth film and high hiding power.',
                                ],
                                'process' => 'For composite sheets requiring translucent effects, mirror silver or high-coverage base layers.',
                            ],
                            'zh' => [
                                'name' => '复合板材印刷油墨',
                                'slug' => 'printing-inks-for-composite-sheets',
                                'description' => '用于复合板材的HV、JM和HL油墨组合，强调层间附着、表面细腻和耐化学性。',
                                'features' => [
                                    'HV半透明油墨层间附着好，不烧PC表面，自流平好。',
                                    'JM镜面银亮度好，墨层细腻，无麻点，耐化学性好。',
                                    'HL遮盖底墨拉伸性能好，墨膜平整细腻，遮盖力高。',
                                ],
                                'process' => '适用于需要半透明、镜面银或高遮盖底层效果的复合板材。',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'translations' => [
                    'vi' => [
                        'name' => 'Sơn phủ mạ chân không và quang học',
                        'slug' => 'son-phu-ma-chan-khong-va-quang-hoc',
                        'description' => 'Giải pháp sơn lót, sơn trung gian, sơn phủ UV và chất xử lý cho quy trình mạ chân không NCVM và hiệu ứng quang học.',
                    ],
                    'en' => [
                        'name' => 'Vacuum coating and optical coating solutions',
                        'slug' => 'vacuum-coating-and-optical-coating-solutions',
                        'description' => 'Primer, middle coat, UV top coat and treatment solutions for NCVM vacuum coating and optical effects.',
                    ],
                    'zh' => [
                        'name' => '真空镀膜与光学涂层方案',
                        'slug' => 'vacuum-coating-and-optical-coating-solutions',
                        'description' => '用于NCVM真空镀膜和光学效果的底漆、中涂、UV面漆及处理剂方案。',
                    ],
                ],
                'products' => [
                    [
                        'sku' => 'NCVM-COATING',
                        'translations' => [
                            'vi' => [
                                'name' => 'Giải pháp sơn phủ cho mạ chân không và quang học',
                                'slug' => 'giai-phap-son-phu-cho-ma-chan-khong-va-quang-hoc',
                                'description' => 'Hệ sơn phủ NCVM cho vật liệu PC+ABS, PC, ABS, PC+GF với kim loại mạ Al, In, Sn.',
                                'features' => [
                                    'Sơn lót UV-LE có khả năng che phủ tốt, dễ gia công, kháng dầu và chịu thử nghiệm đun nước.',
                                    'Sơn trung gian UV-LF thấm ướt tốt, tạo màu tốt và ổn định trong thử nghiệm môi trường.',
                                    'Sơn phủ UV-LG có độ bền dẻo tốt, bề mặt đầy, chịu khắc laser, chịu rung và chống mài mòn.',
                                    'Chất xử lý dòng 2600 hỗ trợ che phủ và bám dính giữa các lớp.',
                                ],
                                'process' => 'Xử lý bề mặt, sơn lót, mạ điện chân không, lớp sơn trung gian pha màu và sơn phủ bề mặt.',
                            ],
                            'en' => [
                                'name' => 'Coating solution for vacuum plating and optical effects',
                                'slug' => 'coating-solution-for-vacuum-plating-and-optical-effects',
                                'description' => 'NCVM coating system for PC+ABS, PC, ABS and PC+GF substrates with Al, In and Sn vacuum-deposited metals.',
                                'features' => [
                                    'UV-LE primer has good hiding power, easy processing, oil resistance and boiling water resistance.',
                                    'UV-LF middle coat has good wetting, color creation and environmental test stability.',
                                    'UV-LG top coat has good flexibility, surface fullness, laser engraving resistance, vibration and abrasion resistance.',
                                    '2600 treatment agent improves coverage and interlayer adhesion.',
                                ],
                                'process' => 'Surface treatment, primer, vacuum plating, colored middle coat and top coating.',
                            ],
                            'zh' => [
                                'name' => '真空镀膜与光学效果涂层方案',
                                'slug' => 'coating-solution-for-vacuum-plating-and-optical-effects',
                                'description' => '适用于PC+ABS、PC、ABS、PC+GF基材及Al、In、Sn真空镀金属的NCVM涂层体系。',
                                'features' => [
                                    'UV-LE底漆遮盖好，施工性好，耐油并可耐水煮测试。',
                                    'UV-LF中涂润湿好，调色性好，环境测试稳定。',
                                    'UV-LG面漆柔韧性好，丰满度高，可激光雕刻，耐振动和耐磨。',
                                    '2600处理剂提升遮盖和层间附着。',
                                ],
                                'process' => '表面处理、底漆、真空镀膜、彩色中涂和面漆。',
                            ],
                        ],
                    ],
                    [
                        'sku' => 'PDS-LDS-NO-GRIND',
                        'translations' => [
                            'vi' => [
                                'name' => 'Sơn phủ PDS/LDS không cần mài',
                                'slug' => 'son-phu-pds-lds-khong-can-mai',
                                'description' => 'Giải pháp sơn phủ PDS/LDS cho anten in trực tiếp trên nền, không cần mài, hiệu suất phủ cao.',
                                'features' => [
                                    'Anten được in trực tiếp trên bề mặt vật liệu nền, cho tín hiệu mạnh hơn.',
                                    'Sơn phủ có độ che phủ tốt, không cần mài.',
                                    'Hiệu suất sơn/phủ cao.',
                                ],
                                'process' => 'Cấu trúc gồm chất xử lý PU, lớp PU màu cam/vàng, khắc laser làm lộ lớp màu và sơn phủ UV mờ.',
                            ],
                            'en' => [
                                'name' => 'PDS/LDS no-grinding coating solution',
                                'slug' => 'pds-lds-no-grinding-coating-solution',
                                'description' => 'PDS/LDS coating solution for direct antenna printing on substrates, with no grinding and high coating efficiency.',
                                'features' => [
                                    'Antenna is printed directly on the substrate surface for stronger signal.',
                                    'Good hiding power without grinding.',
                                    'High coating efficiency.',
                                ],
                                'process' => 'Structure includes PU treatment, orange/yellow PU layers, laser exposure of color layer and matte UV top coat.',
                            ],
                            'zh' => [
                                'name' => 'PDS/LDS免打磨涂层方案',
                                'slug' => 'pds-lds-no-grinding-coating-solution',
                                'description' => '用于基材表面直接印刷天线的PDS/LDS涂层方案，免打磨且涂装效率高。',
                                'features' => [
                                    '天线直接印刷在基材表面，信号更强。',
                                    '遮盖力好，无需打磨。',
                                    '涂装效率高。',
                                ],
                                'process' => '结构包括PU处理剂、橙/黄PU层、激光露色和哑光UV面漆。',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
