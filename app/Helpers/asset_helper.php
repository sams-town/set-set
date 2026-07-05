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
