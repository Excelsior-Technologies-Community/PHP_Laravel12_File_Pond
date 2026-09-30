<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * File dashboard with analytics,
     * search, filtering and pagination.
     */
    public function index(Request $request)
    {
        // Search and filter values
        $search = $request->input('search');
        $type = $request->input('type');

        // File query
        $query = FileUpload::latest();

        // Search by filename
        if ($search) {
            $query->where('filename', 'like', '%' . $search . '%');
        }

        // Filter by file extension
        if ($type) {
            $query->where(function ($query) use ($type) {
                $query->where('filename', 'like', '%.' . $type);
            });
        }

        // Pagination
        $files = $query->paginate(10)->withQueryString();

        // Total files
        $totalFiles = FileUpload::count();

        // Files uploaded today
        $todayUploads = FileUpload::whereDate(
            'created_at',
            today()
        )->count();

        // Recent uploads
        $recentUploads = FileUpload::latest()
            ->take(5)
            ->get();

        // Calculate total storage
        $totalStorage = 0;

        foreach (FileUpload::all() as $file) {
            $path = 'avatars/' . $file->folder . '/' . $file->filename;

            if (Storage::disk('public')->exists($path)) {
                $totalStorage += Storage::disk('public')->size($path);
            }
        }

        // File type statistics
        $fileTypeStats = FileUpload::all()
            ->groupBy(function ($file) {
                $extension = strtolower(
                    pathinfo($file->filename, PATHINFO_EXTENSION)
                );

                return $extension ?: 'unknown';
            })
            ->map(function ($files) {
                return $files->count();
            })
            ->sortDesc();

        // Available file types
        $fileTypes = FileUpload::all()
            ->map(function ($file) {
                $extension = strtolower(
                    pathinfo($file->filename, PATHINFO_EXTENSION)
                );

                return $extension ?: 'unknown';
            })
            ->unique()
            ->sort()
            ->values();

        return view('welcome', compact(
            'files',
            'search',
            'type',
            'totalFiles',
            'todayUploads',
            'totalStorage',
            'recentUploads',
            'fileTypeStats',
            'fileTypes'
        ));
    }


    /**
     * Upload file using FilePond.
     *
     * Security features:
     * - Required file validation
     * - MIME validation
     * - Extension validation
     * - Maximum file size
     * - Safe server-side filename
     * - Unique storage folder
     */
    public function store(Request $request)
    {
        // Server-side validation
        $validated = $request->validate([
            'avatar' => [
                'required',
                'file',
                'max:10240',

                /*
                 * Only allow commonly safe file types.
                 */
                'mimes:jpg,jpeg,png,gif,webp,pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,zip',
            ],
        ], [
            'avatar.required' => 'Please select a file to upload.',

            'avatar.file' => 'The uploaded item must be a valid file.',

            'avatar.max' => 'The file size must not exceed 10 MB.',

            'avatar.mimes' => 'This file type is not allowed.',
        ]);

        $file = $validated['avatar'];

        /*
         * Get original filename only for display/storage metadata.
         */
        $originalFilename = $file->getClientOriginalName();

        /*
         * Get extension from the validated uploaded file.
         */
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        /*
         * Generate a safe filename.
         *
         * Example:
         * original: my resume (final).pdf
         *
         * stored:
         * my_resume_final_a8f91c.pdf
         */
        $safeName = pathinfo(
            $originalFilename,
            PATHINFO_FILENAME
        );

        $safeName = Str::slug($safeName);

        /*
         * Fallback when filename contains no usable characters.
         */
        if (empty($safeName)) {
            $safeName = 'uploaded-file';
        }

        /*
         * Add unique identifier to avoid collisions.
         */
        $filename = $safeName . '-' . Str::random(12);

        if ($extension) {
            $filename .= '.' . $extension;
        }

        /*
         * Generate unique folder.
         */
        $folder = uniqid() . '-' . now()->timestamp;

        /*
         * Final storage path.
         */
        $storagePath = 'avatars/' . $folder;

        /*
         * Store using Laravel's public disk.
         */
        $file->storeAs(
            $storagePath,
            $filename,
            'public'
        );

        /*
         * Save metadata in database.
         */
        FileUpload::create([
            'filename' => $filename,
            'folder' => $folder,
        ]);

        /*
         * FilePond expects a response.
         */
        return $folder;
    }


    /**
     * Secure file download.
     */
    public function download(FileUpload $file)
    {
        $path = 'avatars/' .
            $file->folder .
            '/' .
            $file->filename;

        /*
         * Verify that the file exists.
         */
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }

        /*
         * Download through Laravel.
         */
        return Storage::disk('public')->download(
            $path,
            $file->filename
        );
    }


    /**
     * Delete file from storage and database.
     */
    public function destroy(FileUpload $file)
    {
        $path = 'avatars/' .
            $file->folder .
            '/' .
            $file->filename;

        /*
         * Delete physical file.
         */
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        /*
         * Remove folder if empty.
         */
        $folderPath = 'avatars/' . $file->folder;

        if (Storage::disk('public')->exists($folderPath)) {

            $remainingFiles = Storage::disk('public')
                ->files($folderPath);

            if (count($remainingFiles) === 0) {
                Storage::disk('public')
                    ->deleteDirectory($folderPath);
            }
        }

        /*
         * Delete database record.
         */
        $file->delete();

        return redirect()
            ->route('files.index')
            ->with(
                'success',
                'File deleted successfully.'
            );
    }
}

