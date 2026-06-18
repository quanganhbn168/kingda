<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->update('about.hero', [
            'image' => null,
            'vi' => [
                'eyebrow' => 'Giới thiệu công ty',
                'title' => 'Kingda - Giải pháp vật liệu in điện tử và sơn công nghệ cao',
                'description' => 'Nghiên cứu, sản xuất, kinh doanh và cung cấp các giải pháp mực in, sơn và vật liệu ứng dụng cho điện tử 3C, ô tô, composite và gia công kính.',
                'primary_button_label' => 'Tìm hiểu thêm',
                'secondary_button_label' => 'Văn hóa doanh nghiệp',
                'floating_one_value' => '2009',
                'floating_one_label' => 'Thành lập',
                'floating_two_value' => 'ISO',
                'floating_two_label' => 'Quản lý chất lượng',
            ],
            'en' => [
                'eyebrow' => 'Company introduction',
                'title' => 'Kingda - Electronic printing materials and high-tech coating solutions',
                'description' => 'Researching, manufacturing, selling and servicing ink, coating and applied material solutions for 3C electronics, automotive parts, composites and glass processing.',
                'primary_button_label' => 'Learn more',
                'secondary_button_label' => 'Corporate culture',
                'floating_one_value' => '2009',
                'floating_one_label' => 'Founded',
                'floating_two_value' => 'ISO',
                'floating_two_label' => 'Quality management',
            ],
            'zh' => [
                'eyebrow' => '公司介绍',
                'title' => '金达 - 电子印刷材料与高科技涂层解决方案',
                'description' => '研发、生产、销售并服务于3C电子、汽车零部件、复合材料和玻璃加工领域的油墨、涂料及应用材料解决方案。',
                'primary_button_label' => '了解更多',
                'secondary_button_label' => '企业文化',
                'floating_one_value' => '2009',
                'floating_one_label' => '成立',
                'floating_two_value' => 'ISO',
                'floating_two_label' => '质量管理',
            ],
        ]);

        $this->update('about.intro', [
            'image' => null,
            'small_image_one' => null,
            'small_image_two' => null,
            'video_upload' => null,
            'video_embed_url' => null,
            'stats' => [
                ['value' => '10+', 'vi' => ['label' => 'Năm tích lũy ngành'], 'en' => ['label' => 'Years of industry experience'], 'zh' => ['label' => '行业积累年限']],
                ['value' => '3C', 'vi' => ['label' => 'Ứng dụng điện tử'], 'en' => ['label' => 'Electronic applications'], 'zh' => ['label' => '电子应用']],
                ['value' => 'ISO', 'vi' => ['label' => 'Hệ thống quản lý'], 'en' => ['label' => 'Management system'], 'zh' => ['label' => '管理体系']],
            ],
            'vi' => [
                'eyebrow' => 'Về Kingda',
                'title' => 'Nhà cung cấp giải pháp vật liệu in điện tử chuyên nghiệp',
                'content' => "Kingda được thành lập năm 2009, là doanh nghiệp chuyên nghiên cứu, sản xuất, kinh doanh và cung cấp dịch vụ trong lĩnh vực mực in, sơn và các sản phẩm vật liệu tương tự.\n\nCông ty lấy nhu cầu thị trường làm trung tâm, lấy đội ngũ kỹ thuật làm lợi thế cạnh tranh cốt lõi, hướng tới việc cung cấp cho khách hàng các giải pháp sản phẩm ổn định, chất lượng và phù hợp yêu cầu ứng dụng thực tế.",
            ],
            'en' => [
                'eyebrow' => 'About Kingda',
                'title' => 'Professional electronic printing material solution provider',
                'content' => "Founded in 2009, Kingda specializes in research, production, sales and service for inks, coatings and similar material products.\n\nThe company focuses on market demand and builds its core competitiveness around technical teams, aiming to provide stable, high-quality product solutions suited to real application requirements.",
            ],
            'zh' => [
                'eyebrow' => '关于金达',
                'title' => '专业电子印刷材料解决方案供应商',
                'content' => "金达成立于2009年，是一家专业从事油墨、涂料及相关材料产品研发、生产、销售和服务的企业。\n\n公司以市场需求为中心，以技术团队为核心竞争优势，致力于为客户提供稳定、高质量且符合实际应用需求的产品解决方案。",
            ],
        ]);

        $this->update('about.development', [
            'vi' => [
                'eyebrow' => 'Quan điểm phát triển',
                'title' => 'Làm vững chắc - Củng cố phát triển - Mở rộng',
                'description' => 'Kingda phát triển theo hướng ổn định nền tảng quản lý, nâng cao chất lượng, tăng giá trị sản phẩm và từng bước mở rộng ảnh hưởng trong ngành.',
            ],
            'en' => [
                'eyebrow' => 'Development view',
                'title' => 'Stabilize - Strengthen - Expand',
                'description' => 'Kingda develops by stabilizing management foundations, improving quality, increasing product value and expanding industry influence step by step.',
            ],
            'zh' => [
                'eyebrow' => '发展观点',
                'title' => '做稳 - 巩固发展 - 拓展',
                'description' => '金达通过稳定管理基础、提升质量、增加产品价值并逐步扩大行业影响力来发展。',
            ],
            'items' => [
                ['number' => '01', 'vi' => ['title' => 'Làm vững chắc', 'description' => 'Cam kết trung thực với các bên liên quan, thực hiện nghiêm túc hệ thống quản lý cơ bản, văn hóa và quy định doanh nghiệp.'], 'en' => ['title' => 'Stabilize', 'description' => 'Commit to integrity with stakeholders and strictly implement management systems, culture and regulations.'], 'zh' => ['title' => '做稳', 'description' => '对相关方保持诚信，严格执行基础管理体系、文化和企业制度。']],
                ['number' => '02', 'vi' => ['title' => 'Củng cố và phát triển mạnh', 'description' => 'Nâng cao trình độ quản lý, kiểm soát chi phí, ổn định chất lượng và xây dựng thương hiệu doanh nghiệp.'], 'en' => ['title' => 'Strengthen development', 'description' => 'Improve management, control costs, stabilize quality and build the corporate brand.'], 'zh' => ['title' => '巩固发展', 'description' => '提升管理水平、控制成本、稳定质量并建设企业品牌。']],
                ['number' => '03', 'vi' => ['title' => 'Mở rộng', 'description' => 'Hướng tới năng lực dẫn đầu về quy mô, thị phần và tạo ảnh hưởng tích cực trong lĩnh vực vật liệu in điện tử.'], 'en' => ['title' => 'Expand', 'description' => 'Move toward leading capability in scale, market share and positive influence in electronic printing materials.'], 'zh' => ['title' => '拓展', 'description' => '朝着规模、市场份额和电子印刷材料领域影响力领先的能力发展。']],
            ],
        ]);

        $this->update('about.timeline', [
            'vi' => ['eyebrow' => 'Hành trình phát triển', 'title' => 'Những cột mốc quan trọng của Kingda'],
            'en' => ['eyebrow' => 'Development journey', 'title' => 'Key milestones of Kingda'],
            'zh' => ['eyebrow' => '发展历程', 'title' => '金达的重要里程碑'],
            'items' => [
                ['year' => '2009', 'vi' => ['title' => 'Đăng ký chính thức tại Thâm Quyến', 'description' => 'Chuyên cung cấp các giải pháp ứng dụng vật liệu in ấn điện tử.'], 'en' => ['title' => 'Registered in Shenzhen', 'description' => 'Focused on application solutions for electronic printing materials.'], 'zh' => ['title' => '在深圳正式注册', 'description' => '专注电子印刷材料应用解决方案。']],
                ['year' => '2015', 'vi' => ['title' => 'Đạt chứng nhận hệ thống', 'description' => 'Doanh nghiệp được chứng nhận hệ thống ISO9001 / ISO14001 / ISO18001.'], 'en' => ['title' => 'System certifications', 'description' => 'Obtained ISO9001 / ISO14001 / ISO18001 system certifications.'], 'zh' => ['title' => '获得体系认证', 'description' => '获得ISO9001 / ISO14001 / ISO18001体系认证。']],
                ['year' => '2016', 'vi' => ['title' => 'Nhà cung cấp đạt chuẩn', 'description' => 'Trở thành nhà cung cấp đạt chuẩn của Lens và OFILM.'], 'en' => ['title' => 'Qualified supplier', 'description' => 'Became a qualified supplier of Lens and OFILM.'], 'zh' => ['title' => '合格供应商', 'description' => '成为蓝思和欧菲光的合格供应商。']],
                ['year' => '2017', 'vi' => ['title' => 'Thành lập công ty vật liệu mới', 'description' => 'Thành lập Công ty TNHH Vật liệu Mới Kingda tại Đông Quan.'], 'en' => ['title' => 'New materials company', 'description' => 'Established Kingda New Materials Co., Ltd. in Dongguan.'], 'zh' => ['title' => '成立新材料公司', 'description' => '在东莞成立金达新材料有限公司。']],
                ['year' => '2018', 'vi' => ['title' => 'Đầu tư trung tâm R&D', 'description' => 'Mua tòa văn phòng gần 1.000m² tại Songhu Zhigu làm trung tâm nghiên cứu và phát triển.'], 'en' => ['title' => 'R&D center investment', 'description' => 'Purchased nearly 1,000m² of office space in Songhu Zhigu for R&D.'], 'zh' => ['title' => '投资研发中心', 'description' => '在松湖智谷购买近1000平方米办公楼作为研发中心。']],
                ['year' => '2019', 'vi' => ['title' => 'Chuyển văn phòng và phòng R&D', 'description' => 'Chuyển đến Trung tâm Nghiên cứu Phát triển Songhu Zhigu.'], 'en' => ['title' => 'Moved office and R&D', 'description' => 'Moved to the Songhu Zhigu R&D center.'], 'zh' => ['title' => '办公室与研发迁址', 'description' => '迁入松湖智谷研发中心。']],
                ['year' => '2022', 'vi' => ['title' => 'Vào kho tài nguyên OPPO / VIVO', 'description' => 'Bắt đầu sản xuất đại trà và mở rộng năng lực cung ứng.'], 'en' => ['title' => 'Entered OPPO / VIVO resources', 'description' => 'Started mass production and expanded supply capability.'], 'zh' => ['title' => '进入OPPO / VIVO资源池', 'description' => '开始批量生产并扩大供应能力。']],
            ],
        ]);

        $this->update('about.culture', [
            'vi' => ['eyebrow' => 'Văn hóa doanh nghiệp', 'title' => 'Nền tảng định hình cách Kingda tư duy, hành động và phát triển', 'description' => 'Văn hóa là kim chỉ nam giúp doanh nghiệp duy trì chất lượng, đổi mới công nghệ và tạo giá trị bền vững cho khách hàng.', 'highlight_title' => 'Giá trị cốt lõi', 'highlight_description' => 'Trung thực - Tận tâm - Thiết thực - Sáng tạo. Đây là phương châm xuyên suốt trong hoạt động nghiên cứu, sản xuất và cung cấp giải pháp vật liệu.'],
            'en' => ['eyebrow' => 'Corporate culture', 'title' => 'The foundation shaping how Kingda thinks, acts and grows', 'description' => 'Culture guides quality, technology innovation and sustainable customer value.', 'highlight_title' => 'Core values', 'highlight_description' => 'Integrity - Dedication - Practicality - Creativity. This principle runs through R&D, production and material solution delivery.'],
            'zh' => ['eyebrow' => '企业文化', 'title' => '塑造金达思考、行动与发展的基础', 'description' => '文化引导企业保持质量、技术创新并为客户创造持续价值。', 'highlight_title' => '核心价值观', 'highlight_description' => '诚信 - 敬业 - 务实 - 创新。这一方针贯穿研发、生产和材料解决方案服务。'],
            'items' => [
                ['icon' => '◎', 'vi' => ['title' => 'Định vị', 'description' => 'Tận tâm trở thành nhà cung cấp giải pháp ứng dụng vật liệu in điện tử.'], 'en' => ['title' => 'Positioning', 'description' => 'Committed to becoming an electronic printing material application solution provider.'], 'zh' => ['title' => '定位', 'description' => '致力成为电子印刷材料应用解决方案供应商。']],
                ['icon' => '◇', 'vi' => ['title' => 'Phương châm', 'description' => 'Trung thực - Tận tâm - Thiết thực - Sáng tạo.'], 'en' => ['title' => 'Principle', 'description' => 'Integrity - Dedication - Practicality - Creativity.'], 'zh' => ['title' => '方针', 'description' => '诚信 - 敬业 - 务实 - 创新。']],
                ['icon' => '↗', 'vi' => ['title' => 'Mục tiêu', 'description' => 'Xây dựng thương hiệu và hướng tới chuyên nghiệp hóa.'], 'en' => ['title' => 'Goal', 'description' => 'Build the brand and move toward professionalization.'], 'zh' => ['title' => '目标', 'description' => '打造品牌并走向专业化。']],
                ['icon' => '☷', 'vi' => ['title' => 'Sứ mệnh', 'description' => 'Tạo giá trị cho khách hàng, xây dựng thương hiệu và tạo dựng thành tựu cho nhân viên.'], 'en' => ['title' => 'Mission', 'description' => 'Create customer value, build the brand and help employees achieve.'], 'zh' => ['title' => '使命', 'description' => '为客户创造价值，打造品牌，成就员工。']],
            ],
        ]);

        $this->update('about.capabilities', [
            'vi' => ['eyebrow' => 'Năng lực doanh nghiệp', 'title' => 'R&D, sản xuất và kiểm soát chất lượng toàn diện', 'description' => 'Kingda xây dựng hệ thống nghiên cứu, kiểm tra và sản xuất phục vụ các yêu cầu ứng dụng vật liệu chuyên sâu.'],
            'en' => ['eyebrow' => 'Company capabilities', 'title' => 'Comprehensive R&D, production and quality control', 'description' => 'Kingda builds research, testing and production systems for specialized material application requirements.'],
            'zh' => ['eyebrow' => '企业能力', 'title' => '全面的研发、生产与质量控制', 'description' => '金达建设研发、检测和生产体系，服务专业材料应用需求。'],
            'items' => [
                ['image' => null, 'vi' => ['title' => 'Năng lực R&D kỹ thuật', 'description' => 'Đội ngũ nghiên cứu và phát triển có khả năng cung cấp giải pháp hệ thống, đáp ứng nhu cầu tùy chỉnh của khách hàng.'], 'en' => ['title' => 'Technical R&D capability', 'description' => 'R&D teams provide systematic solutions and respond to customized customer needs.'], 'zh' => ['title' => '技术研发能力', 'description' => '研发团队能够提供系统解决方案，满足客户定制需求。']],
                ['image' => null, 'vi' => ['title' => 'Kiểm tra chất lượng', 'description' => 'Thực hiện kiểm tra, phân tích hệ thống từ nguyên vật liệu đến sản phẩm hoàn thiện.'], 'en' => ['title' => 'Quality testing', 'description' => 'Systematic inspection and analysis from raw materials to finished products.'], 'zh' => ['title' => '质量检测', 'description' => '从原材料到成品进行系统检测和分析。']],
                ['image' => null, 'vi' => ['title' => 'Năng lực sản xuất', 'description' => 'Hệ thống sản xuất và kho vận phục vụ nhu cầu cung ứng ổn định, phù hợp các đơn hàng quy mô và yêu cầu kỹ thuật khác nhau.'], 'en' => ['title' => 'Production capability', 'description' => 'Production and warehousing systems support stable supply for different scales and technical requirements.'], 'zh' => ['title' => '生产能力', 'description' => '生产和仓储体系支持不同规模和技术要求的稳定供应。']],
            ],
        ]);

        $this->update('about.certificates', [
            'vi' => ['eyebrow' => 'Chứng nhận & sở hữu trí tuệ', 'title' => 'Hệ thống chứng nhận năng lực và tài sản trí tuệ', 'description' => 'Kingda chú trọng quản lý chất lượng, môi trường, an toàn lao động và đổi mới công nghệ, tạo nền tảng cho năng lực cạnh tranh dài hạn.'],
            'en' => ['eyebrow' => 'Certificates & intellectual property', 'title' => 'Capability certifications and intellectual property', 'description' => 'Kingda values quality, environment, occupational safety and technological innovation as a foundation for long-term competitiveness.'],
            'zh' => ['eyebrow' => '认证与知识产权', 'title' => '能力认证与知识产权体系', 'description' => '金达重视质量、环境、职业安全和技术创新，为长期竞争力奠定基础。'],
            'items' => [
                ['badge' => 'ISO', 'vi' => ['title' => 'ISO 9001:2015', 'description' => 'Hệ thống quản lý chất lượng.'], 'en' => ['title' => 'ISO 9001:2015', 'description' => 'Quality management system.'], 'zh' => ['title' => 'ISO 9001:2015', 'description' => '质量管理体系。']],
                ['badge' => 'ISO', 'vi' => ['title' => 'ISO 14001:2015', 'description' => 'Hệ thống quản lý môi trường.'], 'en' => ['title' => 'ISO 14001:2015', 'description' => 'Environmental management system.'], 'zh' => ['title' => 'ISO 14001:2015', 'description' => '环境管理体系。']],
                ['badge' => 'ISO', 'vi' => ['title' => 'ISO 45001:2018', 'description' => 'An toàn và sức khỏe nghề nghiệp.'], 'en' => ['title' => 'ISO 45001:2018', 'description' => 'Occupational health and safety.'], 'zh' => ['title' => 'ISO 45001:2018', 'description' => '职业健康与安全。']],
                ['badge' => 'UL', 'vi' => ['title' => 'UL Certification', 'description' => 'Năng lực đáp ứng tiêu chuẩn an toàn sản phẩm.'], 'en' => ['title' => 'UL Certification', 'description' => 'Capability to meet product safety standards.'], 'zh' => ['title' => 'UL认证', 'description' => '满足产品安全标准的能力。']],
            ],
        ]);

        $this->update('about.intellectual_property', [
            'items' => [
                ['value' => '8', 'vi' => ['label' => 'Bản quyền phần mềm'], 'en' => ['label' => 'Software copyrights'], 'zh' => ['label' => '软件著作权']],
                ['value' => '7', 'vi' => ['label' => 'Bằng sáng chế hữu ích'], 'en' => ['label' => 'Utility patents'], 'zh' => ['label' => '实用新型专利']],
                ['value' => '16', 'vi' => ['label' => 'Bằng sáng chế phát minh'], 'en' => ['label' => 'Invention patents'], 'zh' => ['label' => '发明专利']],
                ['value' => '31', 'vi' => ['label' => 'Tổng tài sản trí tuệ'], 'en' => ['label' => 'Total intellectual property assets'], 'zh' => ['label' => '知识产权总数']],
            ],
        ]);

        $this->update('about.customers', [
            'vi' => ['eyebrow' => 'Khách hàng & đối tác', 'title' => 'Hợp tác cùng các thương hiệu và chuỗi cung ứng công nghệ', 'description' => 'Sản phẩm của Kingda được ứng dụng trong nhiều nhóm khách hàng thuộc lĩnh vực điện tử 3C, linh kiện ô tô và vật liệu công nghiệp.'],
            'en' => ['eyebrow' => 'Customers & partners', 'title' => 'Working with technology brands and supply chains', 'description' => 'Kingda products are applied across customer groups in 3C electronics, automotive components and industrial materials.'],
            'zh' => ['eyebrow' => '客户与合作伙伴', 'title' => '携手科技品牌与供应链', 'description' => '金达产品应用于3C电子、汽车零部件和工业材料等客户群体。'],
            'items' => [
                ['name' => 'Huawei'],
                ['name' => 'Xiaomi'],
                ['name' => 'BOE'],
                ['name' => 'Lens'],
                ['name' => 'OFILM'],
                ['name' => 'BYD'],
                ['name' => 'OPPO'],
                ['name' => 'VIVO'],
            ],
        ]);

        $this->update('about.contact', [
            'button_url' => '/lien-he',
            'vi' => ['eyebrow' => 'Kingda Technology', 'title' => 'Cần tư vấn giải pháp mực in, sơn và vật liệu ứng dụng?', 'description' => 'Liên hệ Kingda để được tư vấn giải pháp phù hợp với yêu cầu sản phẩm, quy trình sản xuất và tiêu chuẩn kỹ thuật của doanh nghiệp.', 'button_label' => 'Liên hệ tư vấn'],
            'en' => ['eyebrow' => 'Kingda Technology', 'title' => 'Need advice on inks, coatings and applied materials?', 'description' => 'Contact Kingda for solutions matched to your product requirements, production process and technical standards.', 'button_label' => 'Contact us'],
            'zh' => ['eyebrow' => '金达科技', 'title' => '需要油墨、涂料和应用材料方案咨询？', 'description' => '联系金达，获取符合产品要求、生产流程和技术标准的解决方案。', 'button_label' => '联系咨询'],
        ]);
    }

    private function update(string $key, array $value): void
    {
        $this->migrator->update($key, fn (): array => $value);
    }
};
