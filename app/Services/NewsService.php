<?php

namespace App\Services;

class NewsService
{
    private const CACHE_KEY = 'rss_news_feeds_v1';
    private const CACHE_TTL = 900; // 15 menit sesuai siklus update RSS

    /**
     * Daftar feed RSS Berita Indonesia
     */
    public static function getSourceDefinitions(): array
    {
        return [
            'detik' => [
                'name'     => 'Detik News',
                'url'      => 'https://news.detik.com/berita/rss',
                'category' => 'Nasional',
                'color'    => '#1E40AF',
                'bg_color' => 'rgba(30, 64, 175, 0.12)',
                'icon'     => '⚡',
                'priority' => 1,
            ],
            'cnn' => [
                'name'     => 'CNN Indonesia',
                'url'      => 'https://www.cnnindonesia.com/nasional/rss',
                'category' => 'Nasional',
                'color'    => '#DC2626',
                'bg_color' => 'rgba(220, 38, 38, 0.12)',
                'icon'     => '🔴',
                'priority' => 2,
            ],
            'antara' => [
                'name'     => 'ANTARA',
                'url'      => 'https://www.antaranews.com/rss/top-news',
                'category' => 'Top News',
                'color'    => '#0284C7',
                'bg_color' => 'rgba(2, 132, 199, 0.12)',
                'icon'     => '🌐',
                'priority' => 3,
            ],
            'cnbc' => [
                'name'     => 'CNBC Indonesia',
                'url'      => 'https://www.cnbcindonesia.com/news/rss',
                'category' => 'Ekonomi & Bisnis',
                'color'    => '#0369A1',
                'bg_color' => 'rgba(3, 105, 161, 0.12)',
                'icon'     => '📈',
                'priority' => 4,
            ],
            'cnn_ekonomi' => [
                'name'     => 'CNN Ekonomi',
                'url'      => 'https://www.cnnindonesia.com/ekonomi/rss',
                'category' => 'Ekonomi & Bisnis',
                'color'    => '#059669',
                'bg_color' => 'rgba(5, 150, 105, 0.12)',
                'icon'     => '💼',
                'priority' => 5,
            ],
            'liputan6' => [
                'name'     => 'Liputan6',
                'url'      => 'https://feed.liputan6.com/rss/news',
                'category' => 'Nasional',
                'color'    => '#EA580C',
                'bg_color' => 'rgba(234, 88, 12, 0.12)',
                'icon'     => '📰',
                'priority' => 6,
            ],
            'tempo' => [
                'name'     => 'Tempo',
                'url'      => 'http://rss.tempo.co/nasional',
                'category' => 'Nasional',
                'color'    => '#B91C1C',
                'bg_color' => 'rgba(185, 28, 28, 0.12)',
                'icon'     => '🏛️',
                'priority' => 7,
            ],
            'sindonews' => [
                'name'     => 'SINDOnews',
                'url'      => 'https://nasional.sindonews.com/rss',
                'category' => 'Nasional',
                'color'    => '#7C3AED',
                'bg_color' => 'rgba(124, 58, 237, 0.12)',
                'icon'     => '📢',
                'priority' => 8,
            ],
            'republika' => [
                'name'     => 'Republika',
                'url'      => 'https://www.republika.co.id/rss/nasional/',
                'category' => 'Nasional',
                'color'    => '#047857',
                'bg_color' => 'rgba(4, 120, 87, 0.12)',
                'icon'     => '📖',
                'priority' => 9,
            ],
            'mediaindonesia' => [
                'name'     => 'Media Indonesia',
                'url'      => 'https://mediaindonesia.com/feed',
                'category' => 'Nasional',
                'color'    => '#D97706',
                'bg_color' => 'rgba(217, 119, 6, 0.12)',
                'icon'     => '🗞️',
                'priority' => 10,
            ],
            'kompas' => [
                'name'     => 'Kompas',
                'url'      => 'https://rss.kompas.com/api/feed/social?apikey=bc58c81819dff4b8d5c53540a2fc7ffd83e6314a',
                'category' => 'Top News',
                'color'    => '#2563EB',
                'bg_color' => 'rgba(37, 99, 235, 0.12)',
                'icon'     => '🧭',
                'priority' => 11,
            ],
            'kontan' => [
                'name'     => 'Kontan',
                'url'      => 'https://rss.kontan.co.id/news/keuangan',
                'category' => 'Keuangan',
                'color'    => '#CA8A04',
                'bg_color' => 'rgba(202, 138, 4, 0.12)',
                'icon'     => '💰',
                'priority' => 12,
            ],
            'suara' => [
                'name'     => 'Suara',
                'url'      => 'https://www.suara.com/rss/news',
                'category' => 'Nasional',
                'color'    => '#E11D48',
                'bg_color' => 'rgba(225, 29, 72, 0.12)',
                'icon'     => '🎙️',
                'priority' => 13,
            ],
            'suara_bisnis' => [
                'name'     => 'Suara Bisnis',
                'url'      => 'https://www.suara.com/rss/bisnis',
                'category' => 'Ekonomi & Bisnis',
                'color'    => '#0D9488',
                'bg_color' => 'rgba(13, 148, 136, 0.12)',
                'icon'     => '📊',
                'priority' => 14,
            ],
        ];
    }

    /**
     * Ambil semua item berita (dengan sistem cache dan multi-cURL)
     */
    public static function getAllItems(bool $forceRefresh = false): array
    {
        $cache = null;
        try {
            if (function_exists('service')) {
                $cache = \Config\Services::cache();
                if (!$forceRefresh && $cache !== null) {
                    $cached = $cache->get(self::CACHE_KEY);
                    if ($cached !== null && is_array($cached) && !empty($cached)) {
                        return $cached;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Fetch fresh feeds via parallel cURL
        $sources = self::getSourceDefinitions();
        $rawResponses = self::multiFetch(array_column($sources, 'url', 'detik'));
        
        // Re-map with actual keys
        $urlToKey = [];
        foreach ($sources as $k => $cfg) {
            $urlToKey[$cfg['url']] = $k;
        }

        $allItems = [];
        foreach ($rawResponses as $url => $xmlString) {
            $key = $urlToKey[$url] ?? '';
            if (empty($key) || empty($xmlString)) continue;

            $meta = $sources[$key] ?? [];
            $parsed = self::parseRssXml($xmlString, $key, $meta);
            if (!empty($parsed)) {
                $allItems = array_merge($allItems, $parsed);
            }
        }

        // Sort by timestamp descending
        usort($allItems, function ($a, $b) {
            return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
        });

        // Simpan ke cache
        try {
            if ($cache !== null && !empty($allItems)) {
                $cache->save(self::CACHE_KEY, $allItems, self::CACHE_TTL);
            }
        } catch (\Throwable $e) {}

        return $allItems;
    }

    /**
     * Ambil berita headline terbaik untuk card dashboard
     */
    public static function getHeadlines(int $limit = 8): array
    {
        $items = self::getAllItems();
        if (empty($items)) {
            return [];
        }

        // Ambil mix dari beberapa sumber agar headline bervariasi
        $result = [];
        $seenSources = [];

        // Ronde pertama: 1 berita teratas per sumber
        foreach ($items as $it) {
            $src = $it['source_key'];
            if (!isset($seenSources[$src])) {
                $result[] = $it;
                $seenSources[$src] = true;
                if (count($result) >= $limit) {
                    break;
                }
            }
        }

        // Jika masih kurang dari limit, penuhi dari berita terbaru lainnya
        if (count($result) < $limit) {
            foreach ($items as $it) {
                if (!in_array($it['id'], array_column($result, 'id'), true)) {
                    $result[] = $it;
                    if (count($result) >= $limit) {
                        break;
                    }
                }
            }
        }

        // Pastikan terurut berdasarkan waktu
        usort($result, function ($a, $b) {
            return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
        });

        return array_slice($result, 0, $limit);
    }

    /**
     * Filter berita berdasarkan parameter
     */
    public static function getNews(array $params = []): array
    {
        $items = self::getAllItems(!empty($params['refresh']));

        $category = trim($params['category'] ?? '');
        $source   = trim($params['source'] ?? '');
        $search   = trim(mb_strtolower($params['search'] ?? ''));
        $limit    = max(1, (int)($params['limit'] ?? 60));
        $page     = max(1, (int)($params['page'] ?? 1));

        $filtered = array_filter($items, function ($it) use ($category, $source, $search) {
            if ($category !== '' && $category !== 'Semua') {
                if (strcasecmp($it['category'], $category) !== 0) {
                    return false;
                }
            }
            if ($source !== '' && $source !== 'semua') {
                if ($it['source_key'] !== $source && strcasecmp($it['source'], $source) !== 0) {
                    return false;
                }
            }
            if ($search !== '') {
                $titleMatch = mb_strpos(mb_strtolower($it['title']), $search) !== false;
                $descMatch  = mb_strpos(mb_strtolower($it['description']), $search) !== false;
                if (!$titleMatch && !$descMatch) {
                    return false;
                }
            }
            return true;
        });

        $filtered = array_values($filtered);
        $total = count($filtered);
        $offset = ($page - 1) * $limit;
        $paged = array_slice($filtered, $offset, $limit);

        return [
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => (int)ceil($total / $limit),
            'items'       => $paged,
            'categories'  => self::getAvailableCategories($items),
            'sources'     => self::getAvailableSources($items),
        ];
    }

    /**
     * List kategori unik beserta jumlah artikelnya
     */
    public static function getAvailableCategories(array $items = []): array
    {
        if (empty($items)) {
            $items = self::getAllItems();
        }

        $cats = ['Semua' => count($items)];
        foreach ($items as $it) {
            $c = $it['category'] ?? 'Nasional';
            $cats[$c] = ($cats[$c] ?? 0) + 1;
        }

        $res = [];
        foreach ($cats as $name => $count) {
            $res[] = ['name' => $name, 'count' => $count];
        }
        return $res;
    }

    /**
     * List sumber media yang aktif beserta jumlah artikelnya
     */
    public static function getAvailableSources(array $items = []): array
    {
        if (empty($items)) {
            $items = self::getAllItems();
        }

        $defs = self::getSourceDefinitions();
        $counts = [];
        foreach ($items as $it) {
            $sk = $it['source_key'] ?? '';
            $counts[$sk] = ($counts[$sk] ?? 0) + 1;
        }

        $res = [];
        foreach ($defs as $k => $d) {
            $count = $counts[$k] ?? 0;
            if ($count > 0) {
                $res[] = [
                    'key'      => $k,
                    'name'     => $d['name'],
                    'category' => $d['category'],
                    'color'    => $d['color'],
                    'icon'     => $d['icon'],
                    'count'    => $count,
                ];
            }
        }
        return $res;
    }

    /**
     * Fetch multiple RSS URLs in parallel using curl_multi
     */
    private static function multiFetch(array $urls): array
    {
        $sources = self::getSourceDefinitions();
        $targetUrls = [];
        foreach ($sources as $cfg) {
            $targetUrls[] = $cfg['url'];
        }

        $mh = curl_multi_init();
        $handles = [];

        foreach ($targetUrls as $url) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 DuitKuNews/1.0',
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/rss+xml, application/xml, text/xml, */*;q=0.9',
                    'Accept-Language: id,en;q=0.8',
                ],
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$url] = $ch;
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh, 0.1);
            }
        } while ($active > 0 && $mrc == CURLM_OK);

        $results = [];
        foreach ($handles as $url => $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $content  = curl_multi_getcontent($ch);
            if ($httpCode >= 200 && $httpCode < 300 && !empty($content)) {
                $results[$url] = $content;
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        return $results;
    }

    /**
     * Parse RSS XML format (RSS 2.0 / Atom)
     */
    private static function parseRssXml(string $xmlContent, string $sourceKey, array $sourceMeta): array
    {
        libxml_use_internal_errors(true);
        $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) {
            libxml_clear_errors();
            return [];
        }

        $items = [];
        $rawItems = $xml->channel->item ?? $xml->item ?? [];

        foreach ($rawItems as $it) {
            $title = trim((string)$it->title);
            $link  = trim((string)$it->link);
            if (empty($title) || empty($link)) continue;

            $rawDesc = (string)$it->description;
            $contentEncoded = '';
            $contentChildren = $it->children('http://purl.org/rss/1.0/modules/content/');
            if (isset($contentChildren->encoded)) {
                $contentEncoded = (string)$contentChildren->encoded;
            }

            // Clean plain text summary
            $cleanDesc = self::cleanDescription($rawDesc);
            if (empty($cleanDesc) && !empty($contentEncoded)) {
                $cleanDesc = self::cleanDescription($contentEncoded);
            }

            // Extract Image
            $imageUrl = self::extractImage($it, $rawDesc . ' ' . $contentEncoded);

            // Extract Publication Date & Timestamp
            $pubDateStr = (string)$it->pubDate;
            $timestamp = !empty($pubDateStr) ? strtotime($pubDateStr) : time();
            if ($timestamp === false || $timestamp <= 0) {
                $timestamp = time();
            }

            // Format relative time (Indonesian)
            $timeAgo = self::formatRelativeTime($timestamp);

            $id = md5($link);

            $items[] = [
                'id'          => $id,
                'title'       => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'link'        => $link,
                'description' => $cleanDesc,
                'image'       => $imageUrl,
                'has_image'   => !empty($imageUrl),
                'source'      => $sourceMeta['name'] ?? ucfirst($sourceKey),
                'source_key'  => $sourceKey,
                'category'    => $sourceMeta['category'] ?? 'Nasional',
                'color'       => $sourceMeta['color'] ?? '#2563EB',
                'bg_color'    => $sourceMeta['bg_color'] ?? 'rgba(37, 99, 235, 0.12)',
                'icon'        => $sourceMeta['icon'] ?? '📰',
                'pub_date'    => date('d M Y, H:i', $timestamp),
                'timestamp'   => $timestamp,
                'time_ago'    => $timeAgo,
            ];
        }

        return $items;
    }

    /**
     * Ekstraksi URL gambar dari item XML atau tag description
     */
    private static function extractImage(\SimpleXMLElement $item, string $combinedHtml): string
    {
        // 1. Enclosure attribute
        if (isset($item->enclosure['url'])) {
            $url = (string)$item->enclosure['url'];
            $type = (string)($item->enclosure['type'] ?? '');
            if (empty($type) || strpos($type, 'image') !== false || preg_match('/\.(jpe?g|png|webp|gif)/i', $url)) {
                return $url;
            }
        }

        // 2. Media RSS: <media:content url="...">
        $media = $item->children('http://search.yahoo.com/mrss/');
        if (isset($media->content)) {
            $attrs = $media->content->attributes();
            if (isset($attrs['url'])) {
                return (string)$attrs['url'];
            }
        }
        if (isset($media->thumbnail)) {
            $attrs = $media->thumbnail->attributes();
            if (isset($attrs['url'])) {
                return (string)$attrs['url'];
            }
        }

        // 3. Regex dari tag <img> di dalam description atau content:encoded
        if (!empty($combinedHtml)) {
            if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $combinedHtml, $matches)) {
                $img = trim($matches[1]);
                if (filter_var($img, FILTER_VALIDATE_URL) && !preg_match('/(beacon|tracker|pixel|\.gif)/i', $img)) {
                    return $img;
                }
            }
        }

        return '';
    }

    /**
     * Bersihkan deskripsi dari HTML, iklan, dan whitespace berlebih
     */
    private static function cleanDescription(string $html): string
    {
        // Remove style & script tags
        $clean = preg_replace('/<(script|style)[^>]*?>.*?<\/\\1>/si', '', $html);
        // Strip tags
        $clean = strip_tags($clean);
        // Decode HTML entities
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalize whitespace
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        // Limit excerpt to ~180 chars cleanly
        if (mb_strlen($clean) > 180) {
            $clean = mb_substr($clean, 0, 175) . '...';
        }

        return $clean;
    }

    /**
     * Format waktu relatif dalam bahasa Indonesia
     */
    private static function formatRelativeTime(int $timestamp): string
    {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Baru saja';
        }
        if ($diff < 3600) {
            $m = floor($diff / 60);
            return $m . ' mnt lalu';
        }
        if ($diff < 86400) {
            $h = floor($diff / 3600);
            return $h . ' jam lalu';
        }
        if ($diff < 172800) {
            return 'Kemarin';
        }
        if ($diff < 604800) {
            $d = floor($diff / 86400);
            return $d . ' hari lalu';
        }

        return date('d M Y', $timestamp);
    }
}
