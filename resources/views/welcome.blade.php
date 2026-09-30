@php
use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel 12 FilePond - File Manager</title>

    <link
        href="https://unpkg.com/filepond/dist/filepond.css"
        rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.css"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .filepond--panel-root {
            background-color: #f3f4f6;
        }

        .filepond--drop-label {
            color: #4b5563;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen pb-20">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 mb-8">
        <div class="max-w-7xl mx-auto px-4 py-6">

            <h1 class="text-2xl font-bold text-slate-800">
                File Manager
            </h1>

            <p class="text-slate-500 text-sm mt-1">
                Laravel 12 + FilePond File Upload Management System
            </p>

        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4">

        <!-- Success Message -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
        @endif


        <!-- Analytics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

            <!-- Total Files -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-slate-500">
                            Total Files
                        </p>

                        <p class="text-3xl font-bold text-slate-800 mt-2">
                            {{ $totalFiles }}
                        </p>
                    </div>

                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>

                        </svg>
                    </div>

                </div>
            </div>


            <!-- Today's Uploads -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-slate-500">
                            Uploaded Today
                        </p>

                        <p class="text-3xl font-bold text-slate-800 mt-2">
                            {{ $todayUploads }}
                        </p>
                    </div>

                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V3m0 0L8 7m4-4l4 4">
                            </path>

                        </svg>
                    </div>

                </div>
            </div>


            <!-- Storage -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-slate-500">
                            Storage Used
                        </p>

                        <p class="text-3xl font-bold text-slate-800 mt-2">
                            {{ number_format($totalStorage / 1024 / 1024, 2) }}
                            <span class="text-base font-medium">MB</span>
                        </p>
                    </div>

                    <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v12a2 2 0 002 2h10a2 2 0 002-2V8">
                            </path>

                        </svg>
                    </div>

                </div>
            </div>

        </div>


        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">


            <!-- Upload Section -->

            <div class="lg:col-span-1">

                <!-- Upload New File -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 sticky top-5">

                    <h2 class="text-lg font-semibold text-slate-700 mb-4">
                        Upload New File
                    </h2>

                    <input
                        type="file"
                        class="filepond"
                        name="avatar"
                        multiple>

                    <p class="mt-3 text-xs text-slate-400 text-center leading-5">
                        Drag & drop your files here or browse.<br>
                        Maximum 10 MB per file · Maximum 10 files<br>
                        JPG, PNG, GIF, WEBP, PDF, DOC, XLS, PPT, TXT, CSV & ZIP
                    </p>

                </div>


                <!-- Upload Security -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mt-6">

                    <h3 class="font-semibold text-slate-700 mb-4">
                        Upload Security
                    </h3>

                    <div class="space-y-3">

                        <!-- File Type Validation -->
                        <div class="flex items-center gap-3">

                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-green-600 text-sm">✓</span>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-slate-700">
                                    File Type Validation
                                </p>

                                <p class="text-xs text-slate-400">
                                    Only approved file types are accepted.
                                </p>
                            </div>

                        </div>


                        <!-- Size Protection -->
                        <div class="flex items-center gap-3">

                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-green-600 text-sm">✓</span>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-slate-700">
                                    Size Protection
                                </p>

                                <p class="text-xs text-slate-400">
                                    Maximum 10 MB per file.
                                </p>
                            </div>

                        </div>


                        <!-- Safe File Naming -->
                        <div class="flex items-center gap-3">

                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-green-600 text-sm">✓</span>
                            </div>

                            <div>
                                <p class="text-sm font-medium text-slate-700">
                                    Safe File Naming
                                </p>

                                <p class="text-xs text-slate-400">
                                    Uploaded files receive unique server-side names.
                                </p>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- File Type Statistics -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mt-6">

                    <h3 class="font-semibold text-slate-700 mb-4">
                        File Type Statistics
                    </h3>

                    @forelse($fileTypeStats as $extension => $count)

                    <div class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">

                        <div class="flex items-center gap-2">

                            <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-semibold uppercase">
                                {{ $extension }}
                            </span>

                        </div>

                        <span class="text-sm font-semibold text-slate-600">
                            {{ $count }}
                        </span>

                    </div>

                    @empty

                    <p class="text-sm text-slate-400">
                        No file statistics available.
                    </p>

                    @endforelse

                </div>

            </div>


            <!-- File Management Section -->
            <div class="lg:col-span-2">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-100">

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                            <div>

                                <h3 class="font-semibold text-slate-800">
                                    File Management
                                </h3>

                                <p class="text-xs text-slate-400 mt-1">
                                    Search, filter, download and manage uploaded files.
                                </p>

                            </div>

                            <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-medium">
                                {{ $files->total() }} Files
                            </span>

                        </div>

                    </div>


                    <!-- Search & Filter -->
                    <div class="p-6 bg-slate-50 border-b border-slate-100">

                        <form
                            method="GET"
                            action="{{ route('files.index') }}"
                            class="grid grid-cols-1 md:grid-cols-3 gap-3">

                            <!-- Search -->
                            <div class="md:col-span-2">

                                <input
                                    type="text"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="Search by filename..."
                                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">

                            </div>


                            <!-- Type Filter -->
                            <div>

                                <select
                                    name="type"
                                    class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm bg-white">

                                    <option value="">
                                        All File Types
                                    </option>

                                    @foreach($fileTypes as $fileType)

                                    <option
                                        value="{{ $fileType }}"
                                        {{ $type === $fileType ? 'selected' : '' }}>
                                        {{ strtoupper($fileType) }}
                                    </option>

                                    @endforeach

                                </select>

                            </div>


                            <!-- Buttons -->
                            <div class="md:col-span-3 flex gap-2">

                                <button
                                    type="submit"
                                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                                    Search & Filter
                                </button>

                                <a
                                    href="{{ route('files.index') }}"
                                    class="px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-lg text-sm font-medium">
                                    Reset
                                </a>

                            </div>

                        </form>

                    </div>


                    <!-- Files Table -->
                    <div class="overflow-x-auto">

                        <table class="w-full text-left">

                            <thead class="bg-slate-50 border-b border-slate-100">

                                <tr>

                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">
                                        File Info
                                    </th>

                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">
                                        Type
                                    </th>

                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">
                                        Folder
                                    </th>

                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-slate-100">

                                @forelse($files as $file)

                                @php
                                $extension = strtolower(
                                pathinfo($file->filename, PATHINFO_EXTENSION)
                                );

                                $filePath = 'avatars/' . $file->folder . '/' . $file->filename;

                                $fileSize = Storage::disk('public')->exists($filePath)
                                ? Storage::disk('public')->size($filePath)
                                : 0;
                                @endphp

                                <tr class="hover:bg-slate-50 transition-colors">

                                    <!-- File Info -->
                                    <td class="px-6 py-4">

                                        <div class="flex items-center">

                                            <div class="h-10 w-10 flex-shrink-0 rounded bg-blue-50 flex items-center justify-center">

                                                <svg
                                                    class="h-6 w-6 text-blue-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24">

                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />

                                                </svg>

                                            </div>

                                            <div class="ml-4">

                                                <div class="text-sm font-medium text-slate-900 max-w-xs truncate">
                                                    {{ $file->filename }}
                                                </div>

                                                <div class="text-xs text-slate-400">
                                                    Uploaded {{ $file->created_at->diffForHumans() }}
                                                </div>

                                                <div class="text-xs text-slate-400 mt-1">
                                                    {{ number_format($fileSize / 1024, 2) }} KB
                                                </div>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Type -->
                                    <td class="px-6 py-4">

                                        <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-semibold uppercase">
                                            {{ $extension ?: 'FILE' }}
                                        </span>

                                    </td>


                                    <!-- Folder -->
                                    <td class="px-6 py-4 text-sm text-slate-500 font-mono">

                                        {{ Str::limit($file->folder, 15) }}

                                    </td>


                                    <!-- Actions -->
                                    <td class="px-6 py-4">

                                        <div class="flex justify-end items-center gap-2">

                                            <!-- Download -->
                                            <a
                                                href="{{ route('files.download', $file) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                                Download
                                            </a>


                                            <!-- Delete -->
                                            <form
                                                method="POST"
                                                action="{{ route('files.destroy', $file) }}"
                                                onsubmit="return confirm('Are you sure you want to delete this file?');">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700">
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center text-slate-400 italic">
                                        No files found.

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    <!-- Pagination -->
                    @if($files->hasPages())

                    <div class="px-6 py-4 border-t border-slate-100">

                        {{ $files->links() }}

                    </div>

                    @endif

                </div>


                <!-- Recent Uploads -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 mt-6 p-6">

                    <h3 class="font-semibold text-slate-700 mb-4">
                        Recent Upload Activity
                    </h3>

                    <div class="space-y-3">

                        @forelse($recentUploads as $recent)

                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 last:border-0">

                            <div>

                                <p class="text-sm font-medium text-slate-800">
                                    {{ $recent->filename }}
                                </p>

                                <p class="text-xs text-slate-400">
                                    {{ $recent->created_at->format('d M Y, h:i A') }}
                                </p>

                            </div>

                            <span class="text-xs text-green-600 font-medium">
                                Uploaded
                            </span>

                        </div>

                        @empty

                        <p class="text-sm text-slate-400">
                            No recent uploads.
                        </p>

                        @endforelse

                    </div>

                </div>

            </div>

        </div>

    </main>



    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>

    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>

    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>

    <script>
        FilePond.registerPlugin(
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType
        );

        const inputElement =
            document.querySelector('input[type="file"]');

        const pond = FilePond.create(inputElement, {

            allowMultiple: true,

            allowReorder: true,

            allowProcess: true,

            maxFiles: 10,

            maxFileSize: '10MB',

            acceptedFileTypes: [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'application/pdf',
                'text/plain',
                'text/csv',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip'
            ],

            labelIdle: 'Drag & Drop your files or <span class="filepond--label-action">Browse</span>',

            labelMaxFileSizeExceeded: 'File is too large',

            labelMaxFileSize: 'Maximum file size is {filesize}',

            labelFileTypeNotAllowed: 'File type is not allowed',

            fileValidateTypeLabelExpectedTypes: 'Allowed file types: {allButLastType} or {lastType}',

            labelFileProcessing: 'Uploading',

            labelFileProcessingComplete: 'Upload complete',

            labelFileProcessingError: 'Upload failed',

            imagePreviewHeight: 170,

            imageCropAspectRatio: '1:1',

            imageResizeTargetWidth: 200,

            imageResizeTargetHeight: 200,

            stylePanelLayout: 'compact',

            styleLoadIndicatorPosition: 'center bottom',

            styleProgressIndicatorPosition: 'right bottom',

            styleButtonRemoveItemPosition: 'left bottom',

            styleButtonProcessItemPosition: 'right bottom'
        });

        FilePond.setOptions({

            server: {

                url: '/upload',

                process: {

                    method: 'POST',

                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },

                    onload: (response) => {
                        console.log(
                            'Upload successful:',
                            response
                        );

                        return response;
                    },

                    onerror: (response) => {
                        console.error(
                            'Upload failed:',
                            response
                        );

                        return response;
                    }
                }
            }
        });
    </script>



</body>

</html>