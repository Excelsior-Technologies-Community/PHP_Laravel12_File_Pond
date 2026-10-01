@php
use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
        content="{{ csrf_token() }}">

    <title>Laravel 12 FilePond - Advanced File Manager</title>

    {{-- FilePond CSS --}}
    <link
        href="https://unpkg.com/filepond@4.32.7/dist/filepond.min.css"
        rel="stylesheet">

    {{-- FilePond File Type Validation CSS --}}
    <link
        href="https://unpkg.com/filepond-plugin-file-validate-type@1.2.9/dist/filepond-plugin-file-validate-type.min.css"
        rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
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


<header class="bg-white border-b border-slate-200 mb-8">

    <div class="max-w-7xl mx-auto px-4 py-6">

        <h1 class="text-2xl font-bold text-slate-800">
            Advanced File Manager
        </h1>

        <p class="text-slate-500 text-sm mt-1">
            Laravel 12 + FilePond Upload Management
        </p>

    </div>

</header>


<main class="max-w-7xl mx-auto px-4">


@if(session('success'))

<div class="mb-6 bg-green-50 border border-green-200
            text-green-700 px-4 py-3 rounded-lg">

    {{ session('success') }}

</div>

@endif


@if(session('error'))

<div class="mb-6 bg-red-50 border border-red-200
            text-red-700 px-4 py-3 rounded-lg">

    {{ session('error') }}

</div>

@endif


{{-- Statistics --}}

<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">


    <div class="bg-white rounded-xl shadow-sm
                border border-slate-200 p-6">

        <p class="text-sm text-slate-500">
            Total Files
        </p>

        <p class="text-3xl font-bold text-slate-800 mt-2">
            {{ $totalFiles }}
        </p>

    </div>


    <div class="bg-white rounded-xl shadow-sm
                border border-slate-200 p-6">

        <p class="text-sm text-slate-500">
            Uploaded Today
        </p>

        <p class="text-3xl font-bold text-green-600 mt-2">
            {{ $todayUploads }}
        </p>

    </div>


    <div class="bg-white rounded-xl shadow-sm
                border border-slate-200 p-6">

        <p class="text-sm text-slate-500">
            Total Storage
        </p>

        <p class="text-3xl font-bold text-purple-600 mt-2">

            {{ number_format($totalStorage / 1024 / 1024, 2) }}

            <span class="text-base">
                MB
            </span>

        </p>

    </div>


    <div class="bg-white rounded-xl shadow-sm
                border border-slate-200 p-6">

        <p class="text-sm text-slate-500">
            Filtered Files
        </p>

        <p class="text-3xl font-bold text-blue-600 mt-2">
            {{ $filteredCount }}
        </p>

        <p class="text-xs text-slate-400 mt-1">

            {{ number_format($filteredStorage / 1024 / 1024, 2) }}
            MB

        </p>

    </div>


</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">


{{-- Upload --}}

<div>


<div class="bg-white p-6 rounded-xl
            shadow-sm border border-slate-200">

    <h2 class="text-lg font-semibold text-slate-700 mb-4">
        Upload New Files
    </h2>


    <input
        type="file"
        class="filepond"
        name="avatar"
        multiple>


    <p class="mt-3 text-xs text-slate-400 text-center">

        Maximum 10 MB per file · Maximum 10 files

    </p>

</div>


{{-- Statistics --}}

<div class="bg-white p-6 rounded-xl
            shadow-sm border border-slate-200 mt-6">

    <h3 class="font-semibold text-slate-700 mb-4">
        File Type Statistics
    </h3>


    @forelse($fileTypeStats as $extension => $count)

        <div class="flex justify-between items-center
                    py-2 border-b border-slate-100">

            <span class="bg-slate-100 px-2 py-1
                         rounded text-xs font-semibold uppercase">

                {{ $extension }}

            </span>

            <span class="text-sm font-semibold">
                {{ $count }}
            </span>

        </div>

    @empty

        <p class="text-sm text-slate-400">
            No file statistics available.
        </p>

    @endforelse

</div>


{{-- Recent --}}

<div class="bg-white p-6 rounded-xl
            shadow-sm border border-slate-200 mt-6">

    <h3 class="font-semibold text-slate-700 mb-4">
        Recent Uploads
    </h3>


    @forelse($recentUploads as $recent)

        <div class="py-3 border-b border-slate-100">

            <p class="text-sm font-medium truncate">

                {{ $recent->original_filename ?: $recent->filename }}

            </p>

            <p class="text-xs text-slate-400">

                {{ $recent->created_at->diffForHumans() }}

            </p>

        </div>

    @empty

        <p class="text-sm text-slate-400">
            No recent uploads.
        </p>

    @endforelse

</div>


</div>


{{-- Management --}}

<div class="lg:col-span-2">


<div class="bg-white rounded-xl
            shadow-sm border border-slate-200 overflow-hidden">


{{-- Header --}}

<div class="px-6 py-4 border-b border-slate-100">

    <div class="flex flex-col md:flex-row
                md:items-center md:justify-between gap-3">

        <div>

            <h3 class="font-semibold text-slate-800">
                File Management
            </h3>

            <p class="text-xs text-slate-400 mt-1">
                Search, filter, sort and manage files.
            </p>

        </div>


        {{-- FIXED: files.export.csv --}}

        <a
            href="{{ route('files.export.csv', request()->query()) }}"
            class="px-4 py-2 bg-green-600
                   hover:bg-green-700 text-white
                   rounded-lg text-sm font-medium text-center">

            Export CSV

        </a>

    </div>

</div>


{{-- Filters --}}

<div class="p-6 bg-slate-50 border-b border-slate-100">


<form
    method="GET"
    action="{{ route('files.index') }}"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">


<input
    type="text"
    name="search"
    value="{{ $search }}"
    placeholder="Search filename..."
    class="w-full rounded-lg border
           border-slate-300 px-4 py-2.5 text-sm">


<select
    name="type"
    class="w-full rounded-lg border
           border-slate-300 px-4 py-2.5 text-sm bg-white">

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


<select
    name="size"
    class="w-full rounded-lg border
           border-slate-300 px-4 py-2.5 text-sm bg-white">

    <option value="">
        All File Sizes
    </option>

    <option
        value="small"
        {{ $size === 'small' ? 'selected' : '' }}>

        Small &lt; 1 MB

    </option>

    <option
        value="medium"
        {{ $size === 'medium' ? 'selected' : '' }}>

        Medium 1–5 MB

    </option>

    <option
        value="large"
        {{ $size === 'large' ? 'selected' : '' }}>

        Large &gt; 5 MB

    </option>

</select>


<select
    name="date"
    class="w-full rounded-lg border
           border-slate-300 px-4 py-2.5 text-sm bg-white">

    <option value="">
        Any Date
    </option>

    <option
        value="today"
        {{ $date === 'today' ? 'selected' : '' }}>

        Today

    </option>

    <option
        value="7days"
        {{ $date === '7days' ? 'selected' : '' }}>

        Last 7 Days

    </option>

    <option
        value="30days"
        {{ $date === '30days' ? 'selected' : '' }}>

        Last 30 Days

    </option>

</select>


<select
    name="sort"
    class="w-full rounded-lg border
           border-slate-300 px-4 py-2.5 text-sm bg-white">

    <option
        value="newest"
        {{ $sort === 'newest' ? 'selected' : '' }}>

        Newest First

    </option>

    <option
        value="oldest"
        {{ $sort === 'oldest' ? 'selected' : '' }}>

        Oldest First

    </option>

    <option
        value="name_asc"
        {{ $sort === 'name_asc' ? 'selected' : '' }}>

        Name A-Z

    </option>

    <option
        value="name_desc"
        {{ $sort === 'name_desc' ? 'selected' : '' }}>

        Name Z-A

    </option>

    <option
        value="largest"
        {{ $sort === 'largest' ? 'selected' : '' }}>

        Largest First

    </option>

    <option
        value="smallest"
        {{ $sort === 'smallest' ? 'selected' : '' }}>

        Smallest First

    </option>

</select>


<div class="flex gap-2">

    <button
        type="submit"
        class="px-4 py-2.5 bg-blue-600
               hover:bg-blue-700 text-white
               rounded-lg text-sm font-medium">

        Apply Filters

    </button>


    <a
        href="{{ route('files.index') }}"
        class="px-4 py-2.5 bg-white
               border border-slate-300
               rounded-lg text-sm font-medium">

        Reset

    </a>

</div>


</form>


<div class="mt-4 text-sm text-slate-500">

    <strong>{{ $filteredCount }}</strong>
    filtered files ·

    <strong>
        {{ number_format($filteredStorage / 1024 / 1024, 2) }} MB
    </strong>

</div>

</div>


{{-- Bulk Actions --}}

<form
    id="bulkForm"
    method="POST">

@csrf


<div class="px-6 py-4 bg-white
            border-b border-slate-100
            flex flex-wrap gap-2 items-center">

    <button
        type="button"
        onclick="selectAllFiles()"
        class="px-3 py-2 bg-slate-100
               rounded-lg text-xs font-medium">

        Select All

    </button>


    <button
        type="button"
        onclick="clearSelection()"
        class="px-3 py-2 bg-slate-100
               rounded-lg text-xs font-medium">

        Clear

    </button>


    <button
        type="submit"
        formaction="{{ route('files.bulk-delete') }}"
        onclick="return confirmBulkDelete()"
        class="px-3 py-2 bg-red-600
               text-white rounded-lg text-xs font-medium">

        Bulk Delete

    </button>


    <button
        type="submit"
        formaction="{{ route('files.bulk-download') }}"
        onclick="return checkSelection()"
        class="px-3 py-2 bg-purple-600
               text-white rounded-lg text-xs font-medium">

        Download ZIP

    </button>


    <span
        id="selectedCount"
        class="text-xs text-slate-500">

        0 selected

    </span>

</div>


{{-- Table --}}

<div class="overflow-x-auto">

<table class="w-full text-left">


<thead class="bg-slate-50 border-b border-slate-100">

<tr>

<th class="px-6 py-3">

    <input
        type="checkbox"
        id="selectAll"
        onclick="toggleAll(this)"
        class="h-4 w-4">

</th>


<th class="px-6 py-3 text-xs
           font-semibold text-slate-500 uppercase">

    File

</th>


<th class="px-6 py-3 text-xs
           font-semibold text-slate-500 uppercase">

    Type

</th>


<th class="px-6 py-3 text-xs
           font-semibold text-slate-500 uppercase">

    Size

</th>


<th class="px-6 py-3 text-xs
           font-semibold text-slate-500 uppercase">

    Actions

</th>

</tr>

</thead>


<tbody class="divide-y divide-slate-100">


@forelse($files as $file)


@php

$extension = $file->extension;

@endphp


<tr class="hover:bg-slate-50">


<td class="px-6 py-4">

    <input
        type="checkbox"
        name="ids[]"
        value="{{ $file->id }}"
        class="file-checkbox h-4 w-4"
        onchange="updateSelectedCount()">

</td>


<td class="px-6 py-4">

    <div class="max-w-xs">

        <div class="text-sm font-medium
                    text-slate-900 truncate">

            {{ $file->original_filename ?: $file->filename }}

        </div>


        <div class="text-xs text-slate-400">

            {{ $file->created_at->diffForHumans() }}

        </div>

    </div>

</td>


<td class="px-6 py-4">

    <span
        class="bg-slate-100 text-slate-600
               px-2 py-1 rounded text-xs
               font-semibold uppercase">

        {{ $extension }}

    </span>

</td>


<td class="px-6 py-4 text-sm text-slate-500">

    {{ $file->size_formatted }}

</td>


<td class="px-6 py-4">


<div class="flex flex-wrap gap-2">


@if(
    str_starts_with($file->mime_type ?? '', 'image/') ||
    ($file->mime_type ?? '') === 'application/pdf' ||
    str_starts_with($file->mime_type ?? '', 'text/')
)

<a
    href="{{ route('files.preview', $file) }}"
    target="_blank"
    class="px-3 py-1.5 bg-slate-600
           text-white rounded text-xs">

    Preview

</a>

@endif


<a
    href="{{ route('files.download', $file) }}"
    class="px-3 py-1.5 bg-blue-600
           text-white rounded text-xs">

    Download

</a>


<form
    method="POST"
    action="{{ route('files.duplicate', $file) }}">

    @csrf

    <button
        type="submit"
        class="px-3 py-1.5 bg-purple-600
               text-white rounded text-xs">

        Duplicate

    </button>

</form>


<form
    method="POST"
    action="{{ route('files.destroy', $file) }}"
    onsubmit="return confirm('Delete this file?');">

    @csrf
    @method('DELETE')

    <button
        type="submit"
        class="px-3 py-1.5 bg-red-600
               text-white rounded text-xs">

        Delete

    </button>

</form>


</div>

</td>

</tr>


@empty


<tr>

<td
    colspan="5"
    class="px-6 py-12
           text-center text-slate-400">

    No files found.

</td>

</tr>


@endforelse


</tbody>

</table>

</div>


</form>


{{-- Pagination --}}

@if($files->hasPages())

<div class="px-6 py-4 border-t border-slate-100">

    {{ $files->links() }}

</div>

@endif


</div>

</div>

</div>

</main>


{{-- FilePond JS --}}
<script src="https://unpkg.com/filepond@4.32.7/dist/filepond.min.js"></script>

{{-- FilePond File Size Validation --}}
<script src="https://unpkg.com/filepond-plugin-file-validate-size@2.2.8/dist/filepond-plugin-file-validate-size.min.js"></script>

{{-- FilePond File Type Validation --}}
<script src="https://unpkg.com/filepond-plugin-file-validate-type@1.2.9/dist/filepond-plugin-file-validate-type.min.js"></script>


<script>

FilePond.registerPlugin(
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType
);


const inputElement =
    document.querySelector('input[type="file"]');


const pond =
    FilePond.create(inputElement, {

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

        labelIdle:
            'Drag & Drop your files or <span class="filepond--label-action">Browse</span>',

        labelMaxFileSizeExceeded:
            'File is too large',

        labelMaxFileSize:
            'Maximum file size is {filesize}',

        labelFileTypeNotAllowed:
            'File type is not allowed',

        fileValidateTypeLabelExpectedTypes:
            'Allowed file types: {allButLastType} or {lastType}',

        labelFileProcessing:
            'Uploading',

        labelFileProcessingComplete:
            'Upload complete',

        labelFileProcessingError:
            'Upload failed',

        imagePreviewHeight: 170,

        imageCropAspectRatio: '1:1',

        imageResizeTargetWidth: 200,

        imageResizeTargetHeight: 200,

        stylePanelLayout: 'compact'

    });


FilePond.setOptions({

    server: {

        process: {

            url: '{{ route('files.upload') }}',

            method: 'POST',

            headers: {

                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'

            },

            onload: (response) => {

                console.log(
                    'Upload successful:',
                    response
                );

                setTimeout(function () {
                    window.location.reload();
                }, 500);

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


function getCheckboxes()
{
    return document.querySelectorAll(
        '.file-checkbox'
    );
}


function updateSelectedCount()
{
    const selected =
        document.querySelectorAll(
            '.file-checkbox:checked'
        ).length;

    document.getElementById(
        'selectedCount'
    ).innerText =
        selected + ' selected';
}


function toggleAll(master)
{
    getCheckboxes().forEach(
        checkbox => {

            checkbox.checked =
                master.checked;

        }
    );

    updateSelectedCount();
}


function selectAllFiles()
{
    getCheckboxes().forEach(
        checkbox => {

            checkbox.checked = true;

        }
    );

    document.getElementById(
        'selectAll'
    ).checked = true;

    updateSelectedCount();
}


function clearSelection()
{
    getCheckboxes().forEach(
        checkbox => {

            checkbox.checked = false;

        }
    );

    document.getElementById(
        'selectAll'
    ).checked = false;

    updateSelectedCount();
}


function checkSelection()
{
    const selected =
        document.querySelectorAll(
            '.file-checkbox:checked'
        ).length;

    if (selected === 0) {

        alert(
            'Please select at least one file.'
        );

        return false;
    }

    return true;
}


function confirmBulkDelete()
{
    const selected =
        document.querySelectorAll(
            '.file-checkbox:checked'
        ).length;

    if (selected === 0) {

        alert(
            'Please select at least one file.'
        );

        return false;
    }

    return confirm(
        'Delete ' +
        selected +
        ' selected file(s)?'
    );
}

</script>


</body>

</html>
