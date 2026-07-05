<?php

/**
 * QR Code Helper
 *
 * Wrapper ringan untuk chillerlan/php-qrcode v4.
 * Cara pakai di Controller:
 *   helper('qrcode');
 *   $path = qr_generate_asset(42, 'AST-202501-001');
 *   // atau langsung output ke browser:
 *   qr_output_svg('https://example.com/admin/inventory/42');
 *
 * Instalasi library:
 *   composer require chillerlan/php-qrcode:^4.3
 */

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// ---------------------------------------------------------------
// 1. Generate & simpan file PNG untuk satu aset
//    Return: nama file (string) atau null jika gagal
// ---------------------------------------------------------------
if (! function_exists('qr_generate_asset')) {
    function qr_generate_asset(int $assetId, string $assetCode): ?string
    {
        $saveDir = FCPATH . 'uploads/qrcodes/';

        // Buat direktori jika belum ada
        if (! is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        // Konten QR: URL publik scan aset
        $content  = base_url('qr/' . $assetCode);
        $filename = 'qr_' . $assetCode . '.png';
        $savePath = $saveDir . $filename;

        // Jika file sudah ada dan belum kedaluwarsa (< 30 hari), pakai cache
        if (file_exists($savePath) && (time() - filemtime($savePath)) < 2592000) {
            return $filename;
        }

        try {
            $options = new QROptions([
                'version'    => 5,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_M,   // Error Correction Medium
                'scale'      => 6,               // 6px per modul
                'imageBase64'=> false,
                'addQuietzone'   => true,
                'quietzoneSize'  => 4,
                // Warna: hitam di atas putih
                'moduleValues' => [
                    // finder & alignment
                    0x04 => [255, 255, 255],
                    0x00 => [255, 255, 255],
                    0x11 => [0, 0, 0],
                    0x12 => [0, 0, 0],
                ],
            ]);

            (new QRCode($options))->render($content, $savePath);

            return $filename;

        } catch (\Throwable $e) {
            log_message('error', '[qr_generate_asset] ' . $e->getMessage());
            return null;
        }
    }
}

// ---------------------------------------------------------------
// 2. Generate & simpan SVG (lebih ringan, scalable)
//    Return: nama file .svg atau null
// ---------------------------------------------------------------
if (! function_exists('qr_generate_svg')) {
    function qr_generate_svg(int $assetId, string $assetCode): ?string
    {
        $saveDir  = FCPATH . 'uploads/qrcodes/';
        if (! is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $content  = base_url('qr/' . $assetCode);
        $filename = 'qr_' . $assetCode . '.svg';
        $savePath = $saveDir . $filename;

        if (file_exists($savePath) && (time() - filemtime($savePath)) < 2592000) {
            return $filename;
        }

        try {
            $options = new QROptions([
                'version'     => 5,
                'outputType'  => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel'    => QRCode::ECC_M,
                'addQuietzone'=> true,
                'quietzoneSize' => 4,
                'svgAddXmlHeader' => true,
            ]);

            (new QRCode($options))->render($content, $savePath);

            return $filename;

        } catch (\Throwable $e) {
            log_message('error', '[qr_generate_svg] ' . $e->getMessage());
            return null;
        }
    }
}

// ---------------------------------------------------------------
// 3. Return string Base64 PNG — embed langsung di <img> tag
//    Berguna untuk halaman print / preview tanpa akses file sistem
// ---------------------------------------------------------------
if (! function_exists('qr_base64')) {
    function qr_base64(string $content, int $scale = 5): string
    {
        try {
            $options = new QROptions([
                'version'     => 5,
                'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'    => QRCode::ECC_M,
                'scale'       => $scale,
                'imageBase64' => true,
                'addQuietzone'   => true,
                'quietzoneSize'  => 4,
            ]);

            return (new QRCode($options))->render($content);

        } catch (\Throwable $e) {
            log_message('error', '[qr_base64] ' . $e->getMessage());
            return '';
        }
    }
}

// ---------------------------------------------------------------
// 4. Output langsung ke browser sebagai image/png (untuk route /qr/{id})
//    Panggil di akhir controller action, tanpa return view
// ---------------------------------------------------------------
if (! function_exists('qr_output_png')) {
    function qr_output_png(string $content, int $scale = 6): void
    {
        try {
            $options = new QROptions([
                'version'      => 5,
                'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'     => QRCode::ECC_M,
                'scale'        => $scale,
                'imageBase64'  => false,
                'addQuietzone' => true,
                'quietzoneSize'=> 4,
            ]);

            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=86400');
            echo (new QRCode($options))->render($content);

        } catch (\Throwable $e) {
            log_message('error', '[qr_output_png] ' . $e->getMessage());
            http_response_code(500);
        }
    }
}

// ---------------------------------------------------------------
// 5. Helper: kembalikan URL public QR code yang sudah di-generate
// ---------------------------------------------------------------
if (! function_exists('qr_url')) {
    function qr_url(string $filename): string
    {
        return base_url('uploads/qrcodes/' . $filename);
    }
}

// ---------------------------------------------------------------
// 6. Helper: hapus file QR (saat aset dihapus)
// ---------------------------------------------------------------
if (! function_exists('qr_delete')) {
    function qr_delete(string $filename): bool
    {
        $path = FCPATH . 'uploads/qrcodes/' . $filename;

        if ($filename && file_exists($path)) {
            return unlink($path);
        }

        return false;
    }
}
