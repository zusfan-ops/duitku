<?php

namespace App\Services;

class JellyfinService
{
    private const BASE_URL = 'https://film.hallosemarang.com';
    private const API_KEY  = 'f749e340be4646f18c06ae500a67b320';
    private const CACHE_KEY = 'jellyfin_movies_cache';
    private const CACHE_TTL = 900; // 15 menit

    /**
     * Ambil daftar film dari Jellyfin Server
     */
    public static function getMovies(int $limit = 30): array
    {
        $cache = null;
        try {
            if (function_exists('service')) {
                $cache = \Config\Services::cache();
                $cached = $cache->get(self::CACHE_KEY . '_' . $limit);
                if ($cached !== null && is_array($cached)) {
                    return $cached;
                }
            }
        } catch (\Throwable $e) {}

        $url = self::BASE_URL . '/Items?' . http_build_query([
            'IncludeItemTypes' => 'Movie',
            'Recursive'        => 'true',
            'SortBy'           => 'DateCreated',
            'SortOrder'        => 'Descending',
            'Limit'            => $limit,
            'Fields'           => 'PrimaryImageAspectRatio,Overview,Genres,ProductionYear,RunTimeTicks,CommunityRating,OfficialRating',
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_HTTPHEADER     => [
                'X-Emby-Token: ' . self::API_KEY,
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($response)) {
            return [];
        }

        $json = json_decode($response, true);
        if (!isset($json['Items']) || !is_array($json['Items'])) {
            return [];
        }

        $formatted = [];
        foreach ($json['Items'] as $item) {
            $id = $item['Id'] ?? '';
            if (empty($id)) continue;

            $runTimeTicks = $item['RunTimeTicks'] ?? 0;
            $durationMinutes = $runTimeTicks > 0 ? (int) round($runTimeTicks / 10000000 / 60) : 0;
            $durationStr = $durationMinutes > 0 ? floor($durationMinutes / 60) . 'j ' . ($durationMinutes % 60) . 'm' : '';

            $posterUrl = self::BASE_URL . '/Items/' . $id . '/Images/Primary?fillWidth=320&quality=85&api_key=' . self::API_KEY;
            $backdropUrl = !empty($item['BackdropImageTags'])
                ? self::BASE_URL . '/Items/' . $id . '/Images/Backdrop/0?fillWidth=960&quality=85&api_key=' . self::API_KEY
                : $posterUrl;

            $streamUrl = self::BASE_URL . '/Videos/' . $id . '/stream?static=true&api_key=' . self::API_KEY;

            $formatted[] = [
                'id'           => $id,
                'title'        => $item['Name'] ?? 'Untitled Movie',
                'year'         => $item['ProductionYear'] ?? '',
                'rating'       => isset($item['CommunityRating']) ? round((float) $item['CommunityRating'], 1) : null,
                'overview'     => $item['Overview'] ?? '',
                'genres'       => $item['Genres'] ?? [],
                'duration'     => $durationStr,
                'poster'       => $posterUrl,
                'backdrop'     => $backdropUrl,
                'stream_url'   => $streamUrl,
                'container'    => $item['Container'] ?? 'mp4',
            ];
        }

        if ($cache !== null) {
            try {
                $cache->save(self::CACHE_KEY . '_' . $limit, $formatted, self::CACHE_TTL);
            } catch (\Throwable $e) {}
        }
        return $formatted;
    }
}
