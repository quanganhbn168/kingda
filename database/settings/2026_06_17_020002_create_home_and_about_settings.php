<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('home.stats', [
            ['value' => '2009', 'vi' => ['label' => 'Năm thành lập', 'description' => 'Bắt đầu hoạt động trong lĩnh vực vật liệu in ấn điện tử.'], 'en' => ['label' => 'Founded', 'description' => 'Started operating in electronic printing materials.'], 'zh' => ['label' => '成立年份', 'description' => '开始从事电子印刷材料领域。']],
            ['value' => '10+', 'vi' => ['label' => 'Năm kinh nghiệm', 'description' => 'Tích lũy chuyên môn trong ngành mực in, sơn phủ và vật liệu chức năng.'], 'en' => ['label' => 'Years of experience', 'description' => 'Accumulated expertise in inks, coatings and functional materials.'], 'zh' => ['label' => '年经验', 'description' => '在油墨、涂料和功能材料领域积累专业能力。']],
            ['value' => '31', 'vi' => ['label' => 'Sáng chế & bản quyền', 'description' => 'Bao gồm bản quyền phần mềm, bằng sáng chế hữu ích và bằng sáng chế phát minh.'], 'en' => ['label' => 'IP rights', 'description' => 'Including software copyrights, utility patents and invention patents.'], 'zh' => ['label' => '知识产权', 'description' => '包括软件著作权、实用新型专利和发明专利。']],
            ['value' => '3', 'vi' => ['label' => 'Hệ thống ISO', 'description' => 'ISO9001, ISO14001, ISO45001 phục vụ quản lý chất lượng, môi trường và an toàn.'], 'en' => ['label' => 'ISO systems', 'description' => 'ISO9001, ISO14001 and ISO45001 for quality, environment and safety.'], 'zh' => ['label' => 'ISO体系', 'description' => 'ISO9001、ISO14001、ISO45001质量、环境和安全管理体系。']],
        ]);

        $this->migrator->add('home.intro', [
            'vi' => [
                'eyebrow' => 'Về Kingda',
                'title' => 'Nhà cung cấp giải pháp ứng dụng vật liệu in điện tử',
                'description' => 'Kingda là doanh nghiệp chuyên nghiên cứu, sản xuất, kinh doanh và cung cấp dịch vụ trong lĩnh vực mực in, sơn phủ và các sản phẩm vật liệu tương tự.',
                'content' => 'Chúng tôi luôn kiên trì triết lý: chất lượng hàng đầu, dịch vụ xuất sắc, đổi mới sáng tạo, cùng nhau phát triển.',
                'button_label' => 'Tìm hiểu thêm',
            ],
            'en' => [
                'eyebrow' => 'About Kingda',
                'title' => 'Application material solutions for electronic printing',
                'description' => 'Kingda specializes in research, production, sales and service for inks, coatings and related material products.',
                'content' => 'We pursue first-class quality, excellent service, innovation and shared growth.',
                'button_label' => 'Learn more',
            ],
            'zh' => [
                'eyebrow' => '关于金达',
                'title' => '电子印刷应用材料解决方案供应商',
                'description' => '金达专注于油墨、涂料及相关材料产品的研发、生产、销售和服务。',
                'content' => '我们坚持一流品质、卓越服务、创新发展、互利共赢。',
                'button_label' => '了解更多',
            ],
            'items' => [
                ['icon' => 'fa-flask-vial', 'vi' => ['title' => 'Định vị', 'description' => 'Nhà cung cấp giải pháp ứng dụng vật liệu in điện tử.'], 'en' => ['title' => 'Positioning', 'description' => 'Application material solution provider for electronic printing.'], 'zh' => ['title' => '定位', 'description' => '电子印刷应用材料解决方案供应商。']],
                ['icon' => 'fa-bullseye', 'vi' => ['title' => 'Mục tiêu', 'description' => 'Xây dựng thương hiệu, chuyên nghiệp hóa.'], 'en' => ['title' => 'Goal', 'description' => 'Build the brand and professionalize operations.'], 'zh' => ['title' => '目标', 'description' => '打造品牌，实现专业化。']],
                ['icon' => 'fa-handshake-angle', 'vi' => ['title' => 'Sứ mệnh', 'description' => 'Tạo giá trị cho khách hàng, xây dựng thương hiệu và tạo dựng thành tựu cho nhân viên.'], 'en' => ['title' => 'Mission', 'description' => 'Create customer value, build the brand and help employees achieve.'], 'zh' => ['title' => '使命', 'description' => '为客户创造价值，打造品牌，成就员工。']],
                ['icon' => 'fa-compass', 'vi' => ['title' => 'Phương châm', 'description' => 'Trung thực - Tận tâm - Thiết thực - Sáng tạo.'], 'en' => ['title' => 'Principle', 'description' => 'Integrity, dedication, practicality and creativity.'], 'zh' => ['title' => '方针', 'description' => '诚信、敬业、务实、创新。']],
            ],
            'image' => null,
            'video_upload' => null,
            'video_embed_url' => null,
        ]);

        $this->migrator->add('home.industries', self::section('Ứng dụng', 'Lĩnh vực ứng dụng', null, 'Applications', 'Application fields', null, '应用', '应用领域', null, 8));
        $this->migrator->add('home.products', self::section('Sản phẩm', 'Dòng sản phẩm chính', 'Kingda cung cấp nhiều nhóm vật liệu phục vụ quy trình in, phủ, bảo vệ và xử lý bề mặt trong sản xuất công nghiệp.', 'Products', 'Main product lines', 'Kingda provides material groups for printing, coating, protection and surface treatment in industrial production.', '产品', '主要产品系列', '金达提供用于工业生产中印刷、涂覆、保护和表面处理的多类材料。', 8));
        $this->migrator->add('home.capabilities', [
            ...self::section('Năng lực', 'Năng lực R&D và sản xuất', 'Kingda sở hữu đội ngũ nghiên cứu, hệ thống kiểm nghiệm, cơ sở sản xuất và phòng thí nghiệm phục vụ phát triển giải pháp theo yêu cầu kỹ thuật của khách hàng.', 'Capabilities', 'R&D and production capabilities', 'Kingda has R&D teams, testing systems, production facilities and laboratories to develop solutions for customer requirements.', '能力', '研发与生产能力', '金达拥有研发团队、检测系统、生产基地和实验室，为客户技术需求开发解决方案。', 3),
            'items' => [
                ['icon' => 'fa-flask-vial', 'vi' => ['title' => 'Trung tâm nghiên cứu & phát triển', 'description' => 'Hệ thống phòng R&D phục vụ thử nghiệm, phân tích và phát triển vật liệu.'], 'en' => ['title' => 'R&D center', 'description' => 'R&D laboratories for testing, analysis and material development.'], 'zh' => ['title' => '研发中心', 'description' => '用于测试、分析和材料开发的研发实验室。']],
                ['icon' => 'fa-industry', 'vi' => ['title' => 'Cơ sở sản xuất', 'description' => 'Nhà xưởng, kho, thiết bị sản xuất và hệ thống lưu trữ phục vụ sản xuất ổn định.'], 'en' => ['title' => 'Production facilities', 'description' => 'Workshops, warehouses, production equipment and storage systems for stable output.'], 'zh' => ['title' => '生产基地', 'description' => '厂房、仓库、生产设备和存储系统保障稳定生产。']],
                ['icon' => 'fa-handshake-angle', 'vi' => ['title' => 'Hợp tác trường học - doanh nghiệp', 'description' => 'Hợp tác phòng thí nghiệm với các trường đại học trong các dự án vật liệu và lớp phủ.'], 'en' => ['title' => 'University cooperation', 'description' => 'Laboratory collaboration with universities on materials and coating projects.'], 'zh' => ['title' => '校企合作', 'description' => '与高校在材料和涂层项目中开展实验室合作。']],
            ],
        ]);
        $this->migrator->add('home.certifications', [
            'certificates' => ['ISO 9001', 'ISO 14001', 'ISO 45001', 'UL'],
            'items' => [
                ['value' => '8', 'vi' => ['label' => 'Bản quyền phần mềm'], 'en' => ['label' => 'Software copyrights'], 'zh' => ['label' => '软件著作权']],
                ['value' => '7', 'vi' => ['label' => 'Bằng sáng chế hữu ích'], 'en' => ['label' => 'Utility patents'], 'zh' => ['label' => '实用新型专利']],
                ['value' => '16', 'vi' => ['label' => 'Bằng sáng chế phát minh'], 'en' => ['label' => 'Invention patents'], 'zh' => ['label' => '发明专利']],
                ['value' => '31', 'vi' => ['label' => 'Tổng số mục sở hữu trí tuệ'], 'en' => ['label' => 'Total IP items'], 'zh' => ['label' => '知识产权总数']],
            ],
            'vi' => ['title' => 'Chứng nhận & sở hữu trí tuệ'],
            'en' => ['title' => 'Certificates & intellectual property'],
            'zh' => ['title' => '认证与知识产权'],
        ]);
        $this->migrator->add('home.advantages', [
            ...self::section('Lợi thế cạnh tranh', 'Ưu thế của Kingda', 'Kingda xây dựng lợi thế cạnh tranh dựa trên năng lực kỹ thuật, chất lượng ổn định và khả năng phản hồi dịch vụ nhanh.', 'Competitive strengths', 'Kingda advantages', 'Kingda builds competitiveness through technical capability, stable quality and fast service response.', '竞争优势', '金达优势', '金达以技术能力、稳定质量和快速服务响应构建竞争优势。', 4),
            'items' => [
                ['icon' => 'fa-people-group', 'vi' => ['title' => 'Đội ngũ doanh nghiệp', 'description' => 'Cơ cấu kiến thức hợp lý, phân công rõ ràng và khả năng sáng tạo mạnh.'], 'en' => ['title' => 'Professional team', 'description' => 'Balanced knowledge structure, clear responsibilities and strong creativity.'], 'zh' => ['title' => '专业团队', 'description' => '知识结构合理、分工明确、创新能力强。']],
                ['icon' => 'fa-microscope', 'vi' => ['title' => 'Năng lực R&D kỹ thuật', 'description' => 'Cung cấp giải pháp hệ thống và đáp ứng nhu cầu tùy chỉnh của khách hàng.'], 'en' => ['title' => 'Technical R&D', 'description' => 'System solutions and customized response for customer needs.'], 'zh' => ['title' => '技术研发能力', 'description' => '提供系统解决方案，满足客户定制需求。']],
                ['icon' => 'fa-shield-halved', 'vi' => ['title' => 'Chất lượng ổn định', 'description' => 'Kiểm tra và phân tích toàn bộ quy trình từ nguyên vật liệu đến thành phẩm.'], 'en' => ['title' => 'Stable quality', 'description' => 'Inspection and analysis across raw materials and finished products.'], 'zh' => ['title' => '质量稳定', 'description' => '从原材料到成品进行全过程检测分析。']],
                ['icon' => 'fa-headset', 'vi' => ['title' => 'Phản hồi dịch vụ nhanh', 'description' => 'Đội ngũ kỹ thuật xử lý sự cố tại hiện trường và hỗ trợ khách hàng trong quá trình sản xuất.'], 'en' => ['title' => 'Fast service response', 'description' => 'Technical teams support on-site troubleshooting and production needs.'], 'zh' => ['title' => '快速服务响应', 'description' => '技术团队现场处理问题并支持客户生产。']],
            ],
        ]);
        $this->migrator->add('home.customers', [
            'items' => [['name' => 'Huawei'], ['name' => 'Xiaomi'], ['name' => 'BOE'], ['name' => 'Lens Technology'], ['name' => 'OFILM'], ['name' => 'BYD'], ['name' => 'OPPO'], ['name' => 'VIVO']],
            'vi' => ['eyebrow' => 'Đối tác', 'title' => 'Khách hàng & đối tác tiêu biểu', 'description' => 'Kingda đồng hành cùng nhiều thương hiệu trong chuỗi sản xuất điện tử, in ấn và sơn phủ công nghiệp.'],
            'en' => ['eyebrow' => 'Partners', 'title' => 'Representative customers & partners', 'description' => 'Kingda works with brands across electronics, printing and industrial coating supply chains.'],
            'zh' => ['eyebrow' => '合作伙伴', 'title' => '代表客户与合作伙伴', 'description' => '金达服务于电子、印刷和工业涂装供应链中的多个品牌。'],
        ]);
        $this->migrator->add('home.news', self::section('Tin tức & cập nhật', 'Tin tức mới nhất', 'Cập nhật hoạt động, xu hướng vật liệu và các giải pháp ứng dụng từ Kingda.', 'News & updates', 'Latest news', 'Activities, material trends and application solutions from Kingda.', '新闻动态', '最新新闻', '金达活动、材料趋势和应用解决方案。', 3));
        $this->migrator->add('home.cta', [
            'vi' => ['title' => 'Cần giải pháp vật liệu cho sản phẩm của bạn?', 'description' => 'Liên hệ Kingda để được tư vấn giải pháp mực in, sơn phủ và vật liệu chức năng phù hợp với yêu cầu kỹ thuật, quy trình sản xuất và tiêu chuẩn chất lượng của doanh nghiệp.', 'button_label' => 'Liên hệ tư vấn'],
            'en' => ['title' => 'Need a material solution for your product?', 'description' => 'Contact Kingda for ink, coating and functional material solutions matched to your technical requirements, production process and quality standards.', 'button_label' => 'Contact us'],
            'zh' => ['title' => '需要适合产品的材料解决方案？', 'description' => '联系金达，获取符合技术要求、生产流程和质量标准的油墨、涂料及功能材料方案。', 'button_label' => '联系咨询'],
            'button_url' => null,
        ]);

        $this->migrator->add('about.hero', [
            'vi' => ['eyebrow' => 'Kingda Technology', 'title' => 'Về chúng tôi', 'subtitle' => 'Chuyên nghiên cứu, sản xuất và cung cấp giải pháp vật liệu in điện tử hàng đầu', 'description' => 'Kingda phát triển các giải pháp mực in, sơn phủ và vật liệu chức năng phục vụ ngành điện tử, linh kiện ô tô, composite, kính và thiết bị thông minh.'],
            'en' => ['eyebrow' => 'Kingda Technology', 'title' => 'About us', 'subtitle' => 'Focused on R&D, production and supply of electronic printing material solutions', 'description' => 'Kingda develops inks, coatings and functional material solutions for electronics, automotive parts, composites, glass and smart devices.'],
            'zh' => ['eyebrow' => '金达科技', 'title' => '关于我们', 'subtitle' => '专注电子印刷材料解决方案的研发、生产与供应', 'description' => '金达开发油墨、涂料和功能材料解决方案，服务电子、汽车零部件、复合材料、玻璃和智能设备。'],
        ]);
        $this->migrator->add('about.intro', [
            'image' => null,
            'video_upload' => null,
            'video_embed_url' => null,
            'vi' => ['title' => 'Giới thiệu công ty', 'content' => "Công ty được thành lập năm 2009, với vốn đăng ký 10 triệu NDT, là một doanh nghiệp tư nhân chuyên về nghiên cứu, sản xuất, kinh doanh và cung cấp dịch vụ các sản phẩm mực in, sơn và các sản phẩm tương tự.\n\nKingda luôn tuân thủ nghiêm ngặt các tiêu chuẩn ISO9001, ISO14001, ISO45001 và đang tiếp tục hoàn thiện hệ thống quản lý chất lượng để đáp ứng yêu cầu của khách hàng trong ngành.\n\nSau hơn 10 năm tích lũy và phát triển, các sản phẩm của công ty đã được ứng dụng rộng rãi trong lĩnh vực điện tử 3C, đồng thời thiết lập quan hệ hợp tác lâu dài với nhiều khách hàng lớn như Huawei, Xiaomi, BOE, Lens Technology, OFILM và BYD."],
            'en' => ['title' => 'Company introduction', 'content' => "Founded in 2009 with registered capital of 10 million RMB, Kingda is a private enterprise specializing in research, production, sales and service for inks, coatings and related products.\n\nKingda follows ISO9001, ISO14001 and ISO45001 standards and continues improving its quality management system for industry customers.\n\nAfter more than ten years of development, Kingda products are widely used in 3C electronics and the company has built long-term cooperation with customers including Huawei, Xiaomi, BOE, Lens Technology, OFILM and BYD."],
            'zh' => ['title' => '公司介绍', 'content' => "公司成立于2009年，注册资本1000万元人民币，是一家专业从事油墨、涂料及相关产品研发、生产、销售和服务的民营企业。\n\n金达严格遵循ISO9001、ISO14001、ISO45001等标准，并持续完善质量管理体系，以满足行业客户需求。\n\n经过十余年的积累与发展，公司产品广泛应用于3C电子领域，并与华为、小米、京东方、蓝思科技、欧菲光、比亚迪等客户建立长期合作。"],
        ]);
        $this->migrator->add('about.timeline', [
            'vi' => ['title' => 'Quá trình phát triển'],
            'en' => ['title' => 'Development history'],
            'zh' => ['title' => '发展历程'],
            'items' => [
                ['year' => '2009', 'icon' => '▣', 'vi' => ['description' => 'Công ty được đăng ký chính thức tại Thâm Quyến, chuyên cung cấp giải pháp ứng dụng vật liệu in điện tử.'], 'en' => ['description' => 'The company was officially registered in Shenzhen, focusing on electronic printing material applications.'], 'zh' => ['description' => '公司在深圳正式注册，专注电子印刷材料应用解决方案。']],
                ['year' => '2015', 'icon' => 'ISO', 'vi' => ['description' => 'Doanh nghiệp được chứng nhận hệ thống ISO9001, ISO14001 và ISO18001.'], 'en' => ['description' => 'The company obtained ISO9001, ISO14001 and ISO18001 system certifications.'], 'zh' => ['description' => '公司获得ISO9001、ISO14001、ISO18001体系认证。']],
                ['year' => '2017', 'icon' => '▤', 'vi' => ['description' => 'Thành lập Công ty TNHH Vật liệu Mới Kingda tại Đông Quan.'], 'en' => ['description' => 'Kingda New Materials Co., Ltd. was established in Dongguan.'], 'zh' => ['description' => '东莞金达新材料有限公司成立。']],
                ['year' => '2022', 'icon' => '↗', 'vi' => ['description' => 'Vào kho tài nguyên OPPO, VIVO và bắt đầu sản xuất đại trà.'], 'en' => ['description' => 'Entered OPPO and VIVO supplier resources and started mass production.'], 'zh' => ['description' => '进入OPPO、VIVO资源池并开始批量生产。']],
            ],
        ]);
        $this->migrator->add('about.culture', [
            'vi' => ['title' => 'Văn hóa doanh nghiệp'],
            'en' => ['title' => 'Corporate culture'],
            'zh' => ['title' => '企业文化'],
            'items' => [
                ['icon' => '◎', 'vi' => ['title' => 'Định vị', 'description' => 'Tận tâm trở thành nhà cung cấp giải pháp ứng dụng vật liệu in điện tử.'], 'en' => ['title' => 'Positioning', 'description' => 'Committed to becoming an electronic printing material solution provider.'], 'zh' => ['title' => '定位', 'description' => '致力成为电子印刷材料应用解决方案供应商。']],
                ['icon' => '◆', 'vi' => ['title' => 'Sứ mệnh', 'description' => 'Tạo giá trị cho khách hàng, xây dựng thương hiệu và tạo dựng thành tựu cho nhân viên.'], 'en' => ['title' => 'Mission', 'description' => 'Create customer value, build the brand and help employees achieve.'], 'zh' => ['title' => '使命', 'description' => '为客户创造价值，打造品牌，成就员工。']],
                ['icon' => '⚑', 'vi' => ['title' => 'Mục tiêu', 'description' => 'Xây dựng thương hiệu, chuyên nghiệp hóa và phát triển bền vững.'], 'en' => ['title' => 'Goal', 'description' => 'Build the brand, professionalize and grow sustainably.'], 'zh' => ['title' => '目标', 'description' => '打造品牌、专业化、可持续发展。']],
                ['icon' => '✦', 'vi' => ['title' => 'Phương châm', 'description' => 'Trung thực - Tận tâm - Thiết thực - Sáng tạo.'], 'en' => ['title' => 'Principle', 'description' => 'Integrity, dedication, practicality and creativity.'], 'zh' => ['title' => '方针', 'description' => '诚信、敬业、务实、创新。']],
            ],
        ]);

        foreach (['development', 'capabilities', 'production', 'certificates', 'intellectual_property', 'research', 'advantages', 'applications', 'customers', 'organization', 'commitment', 'contact'] as $key) {
            $this->migrator->add('about.' . $key, []);
        }
    }

    private static function section(string $viEyebrow, string $viTitle, ?string $viDescription, string $enEyebrow, string $enTitle, ?string $enDescription, string $zhEyebrow, string $zhTitle, ?string $zhDescription, int $limit): array
    {
        return [
            'limit' => $limit,
            'vi' => ['eyebrow' => $viEyebrow, 'title' => $viTitle, 'description' => $viDescription, 'button_label' => 'Xem tất cả'],
            'en' => ['eyebrow' => $enEyebrow, 'title' => $enTitle, 'description' => $enDescription, 'button_label' => 'View all'],
            'zh' => ['eyebrow' => $zhEyebrow, 'title' => $zhTitle, 'description' => $zhDescription, 'button_label' => '查看全部'],
        ];
    }
};
