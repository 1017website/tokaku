<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Sajikan file dari disk 'public' (storage/app/public) lewat PHP.
     *
     * Dipakai sebagai pengganti symlink /storage yang sering kena 403 di
     * shared hosting (FollowSymLinks dimatikan). URL: /file/{path}
     */
    public function show(string $path)
    {
        // Cegah path traversal (../) dan path absolut.
        $path = ltrim($path, '/');
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        $size = $disk->size($path);
        $lastModified = $disk->lastModified($path);

        // Caching: izinkan browser cache 30 hari (file gambar jarang berubah).
        $headers = [
            'Content-Type'   => $mime,
            'Content-Length' => $size,
            'Cache-Control'  => 'public, max-age=2592000',
            'Last-Modified'  => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
        ];

        // 304 Not Modified bila browser sudah punya versi terbaru.
        $ifModifiedSince = request()->header('If-Modified-Since');
        if ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified) {
            return response('', 304, $headers);
        }

        return new StreamedResponse(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, $headers);
    }
}
