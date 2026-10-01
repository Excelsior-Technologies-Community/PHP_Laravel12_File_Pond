<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class FileUploadController extends Controller
{
    /**
     * File manager dashboard.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filter values
        |--------------------------------------------------------------------------
        */

        $search = $request->input('search', '');
        $type = $request->input('type', '');
        $size = $request->input('size', '');
        $date = $request->input('date', '');
        $sort = $request->input('sort', 'newest');

        /*
        |--------------------------------------------------------------------------
        | Main file query
        |--------------------------------------------------------------------------
        */

        $query = FileUpload::query();

        /*
        |--------------------------------------------------------------------------
        | Filename search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'original_filename',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'filename',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | File type filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {
            $query->where(function ($q) use ($type) {
                $q->whereRaw(
                    "LOWER(SUBSTRING_INDEX(original_filename, '.', -1)) = ?",
                    [strtolower($type)]
                )->orWhere(
                    'mime_type',
                    strtolower($type)
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | File size filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('size')) {
            switch ($size) {
                case 'small':
                    // Less than 1 MB
                    $query->where(
                        'size',
                        '<',
                        1024 * 1024
                    );
                    break;

                case 'medium':
                    // 1 MB to 5 MB
                    $query->whereBetween(
                        'size',
                        [
                            1024 * 1024,
                            5 * 1024 * 1024,
                        ]
                    );
                    break;

                case 'large':
                    // Greater than 5 MB
                    $query->where(
                        'size',
                        '>',
                        5 * 1024 * 1024
                    );
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Date filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            switch ($date) {
                case 'today':
                    $query->whereDate(
                        'created_at',
                        today()
                    );
                    break;

                case '7days':
                    $query->where(
                        'created_at',
                        '>=',
                        now()->subDays(7)
                    );
                    break;

                case '30days':
                    $query->where(
                        'created_at',
                        '>=',
                        now()->subDays(30)
                    );
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($sort) {
            case 'oldest':
                $query->orderBy(
                    'created_at',
                    'asc'
                );
                break;

            case 'name_asc':
                $query->orderBy(
                    'original_filename',
                    'asc'
                );
                break;

            case 'name_desc':
                $query->orderBy(
                    'original_filename',
                    'desc'
                );
                break;

            case 'largest':
                $query->orderBy(
                    'size',
                    'desc'
                );
                break;

            case 'smallest':
                $query->orderBy(
                    'size',
                    'asc'
                );
                break;

            case 'newest':
            default:
                $query->orderBy(
                    'created_at',
                    'desc'
                );
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Filtered statistics
        |--------------------------------------------------------------------------
        */

        $filteredCount = (clone $query)->count();

        $filteredStorage = (clone $query)->sum('size');

        /*
        |--------------------------------------------------------------------------
        | Paginated files
        |--------------------------------------------------------------------------
        */

        $files = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Overall dashboard statistics
        |--------------------------------------------------------------------------
        */

        $totalFiles = FileUpload::count();

        $todayUploads = FileUpload::whereDate(
            'created_at',
            today()
        )->count();

        $totalStorage = FileUpload::sum('size');

        /*
        |--------------------------------------------------------------------------
        | Recent uploads
        |--------------------------------------------------------------------------
        */

        $recentUploads = FileUpload::latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | File type statistics
        |--------------------------------------------------------------------------
        */

        $fileTypeStats = FileUpload::selectRaw(
            "LOWER(SUBSTRING_INDEX(original_filename, '.', -1)) as extension,
             COUNT(*) as total"
        )
            ->whereNotNull('original_filename')
            ->where(
                'original_filename',
                '!=',
                ''
            )
            ->groupBy('extension')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | File types for filter dropdown
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | The Blade file expects $fileTypes.
        |
        */

        $fileTypes = FileUpload::selectRaw(
            "LOWER(SUBSTRING_INDEX(original_filename, '.', -1)) as extension"
        )
            ->whereNotNull('original_filename')
            ->where(
                'original_filename',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy('extension')
            ->pluck('extension');

        /*
        |--------------------------------------------------------------------------
        | Send data to Blade
        |--------------------------------------------------------------------------
        */

        return view('welcome', compact(
            'files',
            'totalFiles',
            'todayUploads',
            'totalStorage',
            'recentUploads',
            'fileTypeStats',
            'fileTypes',
            'filteredCount',
            'filteredStorage',
            'search',
            'type',
            'size',
            'date',
            'sort'
        ));
    }

    /**
     * Upload a file.
     */
    public function store(Request $request)
    {
        $request->validate([
            'avatar' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,webp,pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,zip',
            ],
        ]);

        $file = $request->file('avatar');

        $originalFilename = $file->getClientOriginalName();

        $mimeType = $file->getMimeType();

        $size = $file->getSize();

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        /*
        |--------------------------------------------------------------------------
        | Create safe stored filename
        |--------------------------------------------------------------------------
        */

        $baseName = pathinfo(
            $originalFilename,
            PATHINFO_FILENAME
        );

        $safeName = Str::slug($baseName);

        if (!$safeName) {
            $safeName = 'file';
        }

        $storedFilename =
            $safeName .
            '-' .
            Str::random(12) .
            '.' .
            $extension;

        /*
        |--------------------------------------------------------------------------
        | Create unique folder
        |--------------------------------------------------------------------------
        */

        $folder = uniqid() . '-' . time();

        /*
        |--------------------------------------------------------------------------
        | Store physical file
        |--------------------------------------------------------------------------
        */

        $path = $file->storeAs(
            "avatars/{$folder}",
            $storedFilename,
            'public'
        );

        /*
        |--------------------------------------------------------------------------
        | Store database record
        |--------------------------------------------------------------------------
        */

        FileUpload::create([
            'filename' => $storedFilename,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'size' => $size,
            'folder' => $folder,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'folder' => $folder,
            'filename' => $storedFilename,
            'path' => $path,
        ]);
    }

    /**
     * Download a single file.
     */
    public function download(FileUpload $file)
    {
        $disk = Storage::disk('public');

        $path = "avatars/{$file->folder}/{$file->filename}";

        if (!$disk->exists($path)) {
            return redirect()
                ->route('files.index')
                ->with(
                    'error',
                    'Original file no longer exists.'
                );
        }

        return $disk->download(
            $path,
            $file->original_filename ?: $file->filename
        );
    }

    /**
     * Preview a file.
     */
    public function preview(FileUpload $file)
    {
        $disk = Storage::disk('public');

        $path = "avatars/{$file->folder}/{$file->filename}";

        if (!$disk->exists($path)) {
            return redirect()
                ->route('files.index')
                ->with(
                    'error',
                    'Original file no longer exists.'
                );
        }

        $mime = $file->mime_type;

        if (!$mime) {
            $mime = $disk->mimeType($path);
        }

        $previewable = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
            'text/csv',
        ];

        /*
        |--------------------------------------------------------------------------
        | Non-previewable files download automatically
        |--------------------------------------------------------------------------
        */

        if (!in_array($mime, $previewable)) {
            return $disk->download(
                $path,
                $file->original_filename ?: $file->filename
            );
        }

        return response()->file(
            $disk->path($path),
            [
                'Content-Type' => $mime,
            ]
        );
    }

    /**
     * Delete one file.
     */
    public function destroy(FileUpload $file)
    {
        $disk = Storage::disk('public');

        $path = "avatars/{$file->folder}/{$file->filename}";

        /*
        |--------------------------------------------------------------------------
        | Delete physical file
        |--------------------------------------------------------------------------
        */

        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        /*
        |--------------------------------------------------------------------------
        | Remove empty folder
        |--------------------------------------------------------------------------
        */

        $folderPath = "avatars/{$file->folder}";

        if ($disk->exists($folderPath)) {
            $remaining = $disk->files($folderPath);

            if (count($remaining) === 0) {
                $disk->deleteDirectory($folderPath);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Delete database record
        |--------------------------------------------------------------------------
        */

        $file->delete();

        return redirect()
            ->route('files.index')
            ->with(
                'success',
                'File deleted successfully.'
            );
    }

    /**
     * Bulk delete selected files.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'file_ids' => [
                'required',
                'array',
            ],

            'file_ids.*' => [
                'integer',
                'exists:file_uploads,id',
            ],
        ]);

        $files = FileUpload::whereIn(
            'id',
            $request->file_ids
        )->get();

        $disk = Storage::disk('public');

        foreach ($files as $file) {
            $path =
                "avatars/{$file->folder}/{$file->filename}";

            /*
            |--------------------------------------------------------------------------
            | Delete physical file
            |--------------------------------------------------------------------------
            */

            if ($disk->exists($path)) {
                $disk->delete($path);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete empty folder
            |--------------------------------------------------------------------------
            */

            $folderPath =
                "avatars/{$file->folder}";

            if ($disk->exists($folderPath)) {
                $remaining = $disk->files($folderPath);

                if (count($remaining) === 0) {
                    $disk->deleteDirectory(
                        $folderPath
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Delete database record
            |--------------------------------------------------------------------------
            */

            $file->delete();
        }

        return redirect()
            ->route('files.index')
            ->with(
                'success',
                count($files) .
                    ' files deleted successfully.'
            );
    }

    /**
     * Duplicate a file.
     */
    public function duplicate(FileUpload $file)
    {
        $disk = Storage::disk('public');

        $sourcePath =
            "avatars/{$file->folder}/{$file->filename}";

        /*
        |--------------------------------------------------------------------------
        | Check source file
        |--------------------------------------------------------------------------
        */

        if (!$disk->exists($sourcePath)) {
            return redirect()
                ->route('files.index')
                ->with(
                    'error',
                    'Original file no longer exists.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate duplicate filename
        |--------------------------------------------------------------------------
        */

        $extension = pathinfo(
            $file->filename,
            PATHINFO_EXTENSION
        );

        $baseName = pathinfo(
            $file->filename,
            PATHINFO_FILENAME
        );

        $newFolder =
            uniqid() .
            '-' .
            time();

        $newFilename =
            $baseName .
            '-copy-' .
            Str::random(8) .
            '.' .
            $extension;

        $destination =
            "avatars/{$newFolder}/{$newFilename}";

        /*
        |--------------------------------------------------------------------------
        | Create destination folder
        |--------------------------------------------------------------------------
        */

        $disk->makeDirectory(
            "avatars/{$newFolder}"
        );

        /*
        |--------------------------------------------------------------------------
        | Copy physical file
        |--------------------------------------------------------------------------
        */

        $disk->copy(
            $sourcePath,
            $destination
        );

        /*
        |--------------------------------------------------------------------------
        | Create database record
        |--------------------------------------------------------------------------
        */

        $originalName =
            $file->original_filename
            ?: $file->filename;

        $copyOriginalName =
            pathinfo(
                $originalName,
                PATHINFO_FILENAME
            ) .
            ' Copy.' .
            $extension;

        FileUpload::create([
            'filename' => $newFilename,
            'original_filename' => $copyOriginalName,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'folder' => $newFolder,
        ]);

        return redirect()
            ->route('files.index')
            ->with(
                'success',
                'File duplicated successfully.'
            );
    }

    /**
     * Export filtered files as CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = $this->filteredQuery($request);

        $files = $query->get();

        return response()->streamDownload(
            function () use ($files) {
                $handle = fopen(
                    'php://output',
                    'w'
                );

                /*
                |--------------------------------------------------------------------------
                | CSV header
                |--------------------------------------------------------------------------
                */

                fputcsv($handle, [
                    'ID',
                    'Original Filename',
                    'Stored Filename',
                    'Type',
                    'Size',
                    'Folder',
                    'Created At',
                ]);

                /*
                |--------------------------------------------------------------------------
                | CSV rows
                |--------------------------------------------------------------------------
                */

                foreach ($files as $file) {
                    fputcsv($handle, [
                        $file->id,
                        $file->original_filename,
                        $file->filename,
                        $file->mime_type,
                        $file->size,
                        $file->folder,
                        $file->created_at,
                    ]);
                }

                fclose($handle);
            },
            'file-manager-export.csv'
        );
    }

    /**
     * Download selected files as ZIP.
     */
    public function downloadZip(Request $request)
    {
        $request->validate([
            'file_ids' => [
                'required',
                'array',
            ],

            'file_ids.*' => [
                'integer',
                'exists:file_uploads,id',
            ],
        ]);

        $files = FileUpload::whereIn(
            'id',
            $request->file_ids
        )->get();

        /*
        |--------------------------------------------------------------------------
        | ZIP filename
        |--------------------------------------------------------------------------
        */

        $zipName =
            'file-manager-' .
            time() .
            '.zip';

        /*
        |--------------------------------------------------------------------------
        | Temporary ZIP location
        |--------------------------------------------------------------------------
        */

        $zipPath = storage_path(
            'app/' . $zipName
        );

        $zip = new ZipArchive();

        /*
        |--------------------------------------------------------------------------
        | Create ZIP
        |--------------------------------------------------------------------------
        */

        if (
            $zip->open(
                $zipPath,
                ZipArchive::CREATE |
                    ZipArchive::OVERWRITE
            ) !== true
        ) {
            return redirect()
                ->route('files.index')
                ->with(
                    'error',
                    'Unable to create ZIP file.'
                );
        }

        $disk = Storage::disk('public');

        /*
        |--------------------------------------------------------------------------
        | Add files to ZIP
        |--------------------------------------------------------------------------
        */

        foreach ($files as $file) {
            $path =
                "avatars/{$file->folder}/{$file->filename}";

            if ($disk->exists($path)) {
                $zip->addFile(
                    $disk->path($path),
                    $file->original_filename
                        ?: $file->filename
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Finish ZIP
        |--------------------------------------------------------------------------
        */

        $zip->close();

        return response()
            ->download(
                $zipPath,
                $zipName
            )
            ->deleteFileAfterSend(true);
    }

    /**
     * Build reusable filtered query.
     */
    private function filteredQuery(Request $request)
    {
        $query = FileUpload::query();

        /*
        |--------------------------------------------------------------------------
        | Filename search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'original_filename',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'filename',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | File type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {
            $type = strtolower(
                $request->type
            );

            $query->where(function ($q) use ($type) {
                $q->whereRaw(
                    "LOWER(SUBSTRING_INDEX(original_filename, '.', -1)) = ?",
                    [$type]
                )->orWhere(
                    'mime_type',
                    $type
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | File size
        |--------------------------------------------------------------------------
        */

        if ($request->filled('size')) {
            switch ($request->size) {
                case 'small':
                    $query->where(
                        'size',
                        '<',
                        1024 * 1024
                    );
                    break;

                case 'medium':
                    $query->whereBetween(
                        'size',
                        [
                            1024 * 1024,
                            5 * 1024 * 1024,
                        ]
                    );
                    break;

                case 'large':
                    $query->where(
                        'size',
                        '>',
                        5 * 1024 * 1024
                    );
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            switch ($request->date) {
                case 'today':
                    $query->whereDate(
                        'created_at',
                        today()
                    );
                    break;

                case '7days':
                    $query->where(
                        'created_at',
                        '>=',
                        now()->subDays(7)
                    );
                    break;

                case '30days':
                    $query->where(
                        'created_at',
                        '>=',
                        now()->subDays(30)
                    );
                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($request->get(
                'sort',
                'newest'
            )) {
            case 'oldest':
                $query->orderBy(
                    'created_at',
                    'asc'
                );
                break;

            case 'name_asc':
                $query->orderBy(
                    'original_filename',
                    'asc'
                );
                break;

            case 'name_desc':
                $query->orderBy(
                    'original_filename',
                    'desc'
                );
                break;

            case 'largest':
                $query->orderBy(
                    'size',
                    'desc'
                );
                break;

            case 'smallest':
                $query->orderBy(
                    'size',
                    'asc'
                );
                break;

            case 'newest':
            default:
                $query->orderBy(
                    'created_at',
                    'desc'
                );
                break;
        }

        return $query;
    }

    public function bulkDownload(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one file.');
        }

        $files = FileUpload::whereIn('id', $ids)->get();

        if ($files->isEmpty()) {
            return back()->with('error', 'No valid files were selected.');
        }

        $zipFileName = 'files-' . now()->format('Y-m-d-H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Unable to create ZIP file.');
        }

        $disk = Storage::disk('public');
        $addedFiles = 0;

        foreach ($files as $file) {
            $path = "avatars/{$file->folder}/{$file->filename}";

            if ($disk->exists($path)) {
                $zip->addFile(
                    $disk->path($path),
                    $file->original_filename ?: $file->filename
                );

                $addedFiles++;
            }
        }

        $zip->close();

        if ($addedFiles === 0) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            return back()->with('error', 'None of the selected files exist on disk.');
        }

        return response()
            ->download($zipPath, $zipFileName)
            ->deleteFileAfterSend(true);
    }
}
