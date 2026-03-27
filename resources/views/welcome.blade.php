<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel 12 FilePond - Pro UI</title>
    
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        /* FilePond custom style to match Tailwind */
        .filepond--panel-root { background-color: #f3f4f6; }
        .filepond--drop-label { color: #4b5563; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-20">

    <header class="bg-white border-b border-slate-200 mb-10">
        <div class="max-w-5xl mx-auto px-4 py-6">
            <h1 class="text-2xl font-bold text-slate-800">File Manager</h1>
            <p class="text-slate-500 text-sm">Manage your files efficiently</p>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 sticky top-10">
                    <h2 class="text-lg font-semibold text-slate-700 mb-4">Upload New File</h2>
                    <input type="file" class="filepond" name="avatar" multiple data-allow-reorder="true">
                    <p class="mt-3 text-xs text-slate-400 text-center text-balance">
                        Drag & drop tamari files ahi karo. Upload thaya pachi page refresh karjo.
                    </p>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-semibold text-slate-800">Recent Uploads</h3>
                        <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-xs font-medium">
                            {{ count($files) }} Files
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">File Info</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Folder ID</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($files as $file)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 rounded bg-blue-50 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-900">{{ $file->filename }}</div>
                                                <div class="text-xs text-slate-400">Uploaded {{ $file->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500 font-mono">
                                        {{ Str::limit($file->folder, 15) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ asset('storage/avatars/' . $file->folder . '/' . $file->filename) }}" 
                                           target="_blank" 
                                           class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            View File
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-slate-400 italic">
                                        Haji sudhi koi file upload nathi kari.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script>
        const inputElement = document.querySelector('input[type="file"]');
        const pond = FilePond.create(inputElement, {
            labelIdle: 'Drag & Drop your files or <span class="filepond--label-action">Browse</span>',
            imagePreviewHeight: 170,
            imageCropAspectRatio: '1:1',
            imageResizeTargetWidth: 200,
            imageResizeTargetHeight: 200,
            stylePanelLayout: 'compact',
            styleLoadIndicatorPosition: 'center bottom',
            styleProgressIndicatorPosition: 'right bottom',
            styleButtonRemoveItemPosition: 'left bottom',
            styleButtonProcessItemPosition: 'right bottom',
        });

        FilePond.setOptions({
            server: {
                url: '/upload',
                process: {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            }
        });
    </script>
</body>
</html>