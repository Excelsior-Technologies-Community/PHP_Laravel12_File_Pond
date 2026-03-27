<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileUpload;

class FileUploadController extends Controller
{
    public function index() {
    // Badhi files ne latest upload mujab levani
    $files = \App\Models\FileUpload::latest()->get();
    return view('welcome', compact('files'));
}
    public function store(Request $request) {
    if ($request->hasFile('avatar')) {
        $file = $request->file('avatar');
        $filename = $file->getClientOriginalName();
        $folder = uniqid() . '-' . now()->timestamp;
        
        // File ne storage ma save karo
        $file->storeAs('avatars/' . $folder, $filename);

        // Database ma entry karo
        FileUpload::create([
            'filename' => $filename,
            'folder' => $folder
        ]);

        return $folder;
    }
    return '';
}
}