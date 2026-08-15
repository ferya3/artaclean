<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function index(SeoService $seo): View
    {
        $seo->title(__('seo.downloads_title'))
            ->description(__('seo.downloads_description'))
            ->canonical(route('downloads.index'));

        return view('pages.downloads', [
            'downloads' => Download::query()
                ->public()
                ->with('product')
                ->orderBy('type')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn (Download $download) => $download->type->value),
        ]);
    }

    public function show(Download $download): StreamedResponse|RedirectResponse
    {
        abort_unless($download->is_public, 404);

        // Gated files send the visitor through the lead form first; the form
        // hands the file back once it has the contact details.
        if ($download->requires_lead && ! session()->has('download.unlocked.'.$download->id)) {
            return redirect()->route('contact', ['download' => $download->id]);
        }

        abort_unless(Storage::exists($download->file_path), 404);

        Download::withoutTimestamps(fn () => $download->increment('download_count'));

        return Storage::download(
            $download->file_path,
            str($download->title)->slug().'.'.pathinfo($download->file_path, PATHINFO_EXTENSION)
        );
    }
}
