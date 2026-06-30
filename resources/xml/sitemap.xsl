<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="sitemap xhtml"
>
    <xsl:output method="html" encoding="UTF-8" indent="yes" />

    <xsl:template match="/">
        <html lang="vi">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>XML Sitemap</title>
                <style>
                    :root {
                        color-scheme: light;
                        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                        color: #172033;
                        background: #f5f7fb;
                    }

                    * { box-sizing: border-box; }
                    body { margin: 0; background: #f5f7fb; }
                    a { color: #b5121b; text-decoration: none; }
                    a:hover { text-decoration: underline; }

                    .header {
                        border-bottom: 1px solid #e3e8f1;
                        background: #ffffff;
                        padding: 32px 24px;
                    }

                    .header-inner,
                    .content {
                        width: min(1180px, 100%);
                        margin: 0 auto;
                    }

                    h1 { margin: 0; font-size: clamp(26px, 4vw, 40px); }
                    .description { margin: 10px 0 0; color: #637083; line-height: 1.6; }
                    .count { color: #b5121b; font-weight: 800; }
                    .content { padding: 24px; }

                    .table-shell {
                        overflow: hidden;
                        border: 1px solid #e3e8f1;
                        border-radius: 14px;
                        background: #ffffff;
                        box-shadow: 0 12px 36px rgba(23, 32, 51, .07);
                    }

                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 14px 16px; text-align: left; vertical-align: top; }
                    th {
                        position: sticky;
                        top: 0;
                        background: #172033;
                        color: #ffffff;
                        font-size: 12px;
                        letter-spacing: .06em;
                        text-transform: uppercase;
                    }

                    td { border-top: 1px solid #edf0f5; font-size: 14px; line-height: 1.5; }
                    tbody tr:hover { background: #fff8f8; }
                    .index { width: 70px; color: #8290a3; }
                    .modified { width: 220px; color: #637083; white-space: nowrap; }

                    .languages { margin-top: 7px; display: flex; flex-wrap: wrap; gap: 6px; }
                    .language {
                        display: inline-flex;
                        border-radius: 999px;
                        background: #f1f4f8;
                        padding: 3px 8px;
                        color: #637083;
                        font-size: 11px;
                        font-weight: 700;
                        text-transform: uppercase;
                    }

                    @media (max-width: 720px) {
                        .header { padding: 24px 16px; }
                        .content { padding: 16px; }
                        .table-shell { overflow-x: auto; }
                        table { min-width: 760px; }
                    }
                </style>
            </head>
            <body>
                <header class="header">
                    <div class="header-inner">
                        <h1>XML Sitemap</h1>
                        <p class="description">
                            Sitemap này chứa
                            <span class="count"><xsl:value-of select="count(sitemap:urlset/sitemap:url)" /></span>
                            URL đang được công khai cho công cụ tìm kiếm.
                        </p>
                    </div>
                </header>

                <main class="content">
                    <div class="table-shell">
                        <table>
                            <thead>
                                <tr>
                                    <th class="index">STT</th>
                                    <th>URL</th>
                                    <th class="modified">Cập nhật lần cuối</th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:for-each select="sitemap:urlset/sitemap:url">
                                    <tr>
                                        <td class="index"><xsl:value-of select="position()" /></td>
                                        <td>
                                            <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc" /></a>
                                            <xsl:if test="xhtml:link">
                                                <div class="languages">
                                                    <xsl:for-each select="xhtml:link">
                                                        <a class="language" href="{@href}">
                                                            <xsl:value-of select="@hreflang" />
                                                        </a>
                                                    </xsl:for-each>
                                                </div>
                                            </xsl:if>
                                        </td>
                                        <td class="modified"><xsl:value-of select="sitemap:lastmod" /></td>
                                    </tr>
                                </xsl:for-each>
                            </tbody>
                        </table>
                    </div>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
