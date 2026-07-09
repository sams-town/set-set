<?php

/**
 * Asset Helper
 * Fungsi-fungsi bantu untuk sistem manajemen aset.
 *
 * Cara pakai di Controller:
 *   helper('asset');
 *   atau otomatis lewat BaseController jika didaftarkan di $helpers.
 */

// ---------------------------------------------------------------
// Format rupiah
// ---------------------------------------------------------------
if (! function_exists('rupiah')) {
    function rupiah(float|int|null $amount, string $prefix = 'Rp '): string
    {
        if ($amount === null) {
            return '-';
        }

        return $prefix . number_format($amount, 0, ',', '.');
    }
}

// ---------------------------------------------------------------
// Badge HTML untuk status aset
// ---------------------------------------------------------------
if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'tersedia'   => ['success', 'Tersedia'],
            'dipinjam'   => ['warning text-dark', 'Dipinjam'],
            'diperbaiki' => ['info text-dark', 'Diperbaiki'],
            'dihapus'    => ['danger', 'Dihapus'],
        ];

        [$class, $label] = $map[$status] ?? ['secondary', ucfirst($status)];

        return '<span class="badge bg-' . $class . '">' . $label . '</span>';
    }
}

// ---------------------------------------------------------------
// Badge HTML untuk kondisi aset
// ---------------------------------------------------------------
if (! function_exists('condition_badge')) {
    function condition_badge(string $condition): string
    {
        $map = [
            'baik'         => ['success', 'Baik'],
            'rusak_ringan' => ['warning text-dark', 'Rusak Ringan'],
            'rusak_berat'  => ['danger', 'Rusak Berat'],
        ];

        [$class, $label] = $map[$condition] ?? ['secondary', ucfirst($condition)];

        return '<span class="badge bg-' . $class . '">' . $label . '</span>';
    }
}

// ---------------------------------------------------------------
// Format tanggal ke Indonesia
// ---------------------------------------------------------------
if (! function_exists('tgl_indo')) {
    function tgl_indo(string|null $date, bool $withTime = false): string
    {
        if (empty($date)) {
            return '-';
        }

        $bulan = [
            1  => 'Januari',   2  => 'Februari', 3  => 'Maret',
            4  => 'April',     5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',      8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',   11 => 'November',  12 => 'Desember',
        ];

        $ts  = strtotime($date);
        $str = date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);

        if ($withTime) {
            $str .= ' ' . date('H:i', $ts);
        }

        return $str;
    }
}

// ---------------------------------------------------------------
// Hitung selisih hari antara dua tanggal
// ---------------------------------------------------------------
if (! function_exists('hari_terlambat')) {
    function hari_terlambat(string $returnDatePlan): int
    {
        $plan  = new DateTime($returnDatePlan);
        $today = new DateTime(date('Y-m-d'));
        $diff  = $today->diff($plan);

        return $diff->invert ? $diff->days : 0;
    }
}

// ---------------------------------------------------------------
// Truncate string
// ---------------------------------------------------------------
if (! function_exists('str_limit')) {
    function str_limit(string|null $str, int $limit = 50, string $end = '...'): string
    {
        if (empty($str)) {
            return '-';
        }

        return mb_strlen($str) > $limit ? mb_substr($str, 0, $limit) . $end : $str;
    }
}

// ---------------------------------------------------------------
// List of asset categories
// ---------------------------------------------------------------
if (! function_exists('asset_categories')) {
    function asset_categories(): array
    {
        return [
            'Building Assets',
            'Utility Assets',
            'Clinical Assets',
            'Operational Assets',
            'ICT Assets',
            'Safety & Security Assets',
            'Transportation Assets',
            'Environmental Assets',
        ];
    }
}

// ---------------------------------------------------------------
// Badge classes for asset categories
// ---------------------------------------------------------------
if (! function_exists('category_badge_class')) {
    function category_badge_class(string $category): string
    {
        $map = [
            'Building Assets'          => 'bg-indigo-100 text-indigo-700',
            'Utility Assets'           => 'bg-amber-100 text-amber-700',
            'Clinical Assets'          => 'bg-teal-100 text-teal-700',
            'Operational Assets'       => 'bg-purple-100 text-purple-700',
            'ICT Assets'               => 'bg-blue-100 text-blue-700',
            'Safety & Security Assets' => 'bg-red-100 text-red-700',
            'Transportation Assets'    => 'bg-rose-100 text-rose-700',
            'Environmental Assets'     => 'bg-emerald-100 text-emerald-700',
        ];

        return $map[$category] ?? 'bg-gray-100 text-gray-600';
    }
}
