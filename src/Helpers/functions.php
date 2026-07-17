<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        static $configs = [];

        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($configs[$file])) {
            $path = __DIR__ . "/../../config/{$file}.php";
            $configs[$file] = file_exists($path) ? require $path : [];
        }

        return $item !== null ? ($configs[$file][$item] ?? $default) : $configs[$file];
    }
}

if (!function_exists('mood_meta')) {
    function mood_meta(string $mood): array
    {
        $map = [
            'Sangat Senang' => ['emoji' => '😄', 'slug' => 'sangat-senang'],
            'Senang'        => ['emoji' => '🙂', 'slug' => 'senang'],
            'Netral'        => ['emoji' => '😐', 'slug' => 'netral'],
            'Sedih'         => ['emoji' => '🙁', 'slug' => 'sedih'],
            'Sangat Buruk'  => ['emoji' => '😞', 'slug' => 'sangat-buruk'],
        ];

        return $map[$mood] ?? ['emoji' => '•', 'slug' => 'netral'];
    }
}

if (!function_exists('assessment_badge_class')) {
    function assessment_badge_class(string $category): string
    {
        $map = [
            'Minimal' => 'assess-badge-green',
            'Ringan'  => 'assess-badge-yellow',
            'Sedang'  => 'assess-badge-orange',
            'Berat'   => 'assess-badge-red',
            'Tinggi'  => 'assess-badge-green',
            'Rendah'  => 'assess-badge-red',
        ];

        return $map[$category] ?? 'assess-badge-gray';
    }
}

if (!function_exists('assessment_level_badge_class')) {
    // Combined PWB+BDI-II risk level (1-6, higher = worse) — distinct from
    // assessment_badge_class since a bare category label like "Tinggi" means
    // opposite things for PWB (good) vs. this combined risk scale (bad).
    function assessment_level_badge_class(int $level): string
    {
        $map = [
            1 => 'assess-badge-green',
            2 => 'assess-badge-green',
            3 => 'assess-badge-yellow',
            4 => 'assess-badge-orange',
            5 => 'assess-badge-orange',
            6 => 'assess-badge-red',
        ];

        return $map[$level] ?? 'assess-badge-gray';
    }
}

if (!function_exists('diary_intensity_badge_class')) {
    // Structured diary's 1-5 "Intensitas Emosi" scale (1 = Sangat Ringan, 5 = Sangat Berat).
    function diary_intensity_badge_class(int $intensity): string
    {
        $map = [
            1 => 'diary-badge-green',
            2 => 'diary-badge-green',
            3 => 'diary-badge-yellow',
            4 => 'diary-badge-orange',
            5 => 'diary-badge-red',
        ];

        return $map[$intensity] ?? 'diary-badge-gray';
    }
}

if (!function_exists('pagination_links')) {
    /** Renders a Bootstrap pager for the current query string, swapping only the `page` param. */
    function pagination_links(int $page, int $totalPages, array $queryParams = []): string
    {
        if ($totalPages <= 1) {
            return '';
        }

        $url = fn (int $p) => '?' . http_build_query(array_merge($queryParams, ['page' => $p]));

        $window = 2;
        $items = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || ($i >= $page - $window && $i <= $page + $window)) {
                $items[] = $i;
            } elseif (end($items) !== '…') {
                $items[] = '…';
            }
        }

        $html = '<nav><ul class="pagination pagination-sm mb-0">';
        $html .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '">'
            . '<a class="page-link" href="' . htmlspecialchars($url(max(1, $page - 1))) . '">&laquo;</a></li>';

        foreach ($items as $item) {
            if ($item === '…') {
                $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                continue;
            }
            $html .= '<li class="page-item' . ($item === $page ? ' active' : '') . '">'
                . '<a class="page-link" href="' . htmlspecialchars($url($item)) . '">' . $item . '</a></li>';
        }

        $html .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '">'
            . '<a class="page-link" href="' . htmlspecialchars($url(min($totalPages, $page + 1))) . '">&raquo;</a></li>';
        $html .= '</ul></nav>';

        return $html;
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit;
    }
}
