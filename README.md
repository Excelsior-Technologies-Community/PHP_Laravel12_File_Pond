# 📂 PHP Laravel 12 - FilePond File Upload System

This project demonstrates a **modern file upload system** built using **Laravel 12** and **FilePond**.

Users can **drag & drop files**, upload them asynchronously, and store file records in the database.
The UI is styled using **Tailwind CSS** for a clean and modern interface.

---

# 🚀 Features

* **Laravel 12 Backend**
* **FilePond Drag & Drop Upload**
* **Asynchronous File Upload**
* **Unique Folder Storage System**
* **Database File Record Storage**
* **Modern UI with Tailwind CSS**

---

# 🛠️ Tech Stack

| Technology     | Purpose                 |
| -------------- | ----------------------- |
| Laravel 12     | Backend Framework       |
| FilePond JS    | Drag & Drop File Upload |
| Tailwind CSS   | UI Styling              |
| MySQL / SQLite | Database                |

---

# ⚙️ Installation Guide

## 1️⃣ Create Laravel Project

```bash
composer create-project laravel/laravel PHP_Laravel12_File_Pond

cd PHP_Laravel12_File_Pond
```

---

# 🗄️ Database Setup

Update your `.env` file with database credentials.

```env
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=
```

---

# 📦 Create Migration

Create a migration for storing uploaded files.

```bash
php artisan make:migration create_file_uploads_table
```

Open the migration file and add the following columns:

```php
Schema::create('file_uploads', function (Blueprint $table) {
    $table->id();
    $table->string('filename');
    $table->string('folder');
    $table->timestamps();
});
```

Run migration:

```bash
php artisan migrate
```

Create a symbolic storage link:

```bash
php artisan storage:link
```

---

# 📄 Model

Create the model:

```bash
php artisan make:model FileUpload
```

File:

```
app/Models/FileUpload.php
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $fillable = ['filename', 'folder'];
}
```

---

# 🎮 Controller

Create controller:

```bash
php artisan make:controller FileUploadController
```

File:

```
app/Http/Controllers/FileUploadController.php
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileUpload;

class FileUploadController extends Controller
{
    public function index()
    {
        $files = FileUpload::latest()->get();
        return view('welcome', compact('files'));
    }

    public function store(Request $request)
    {
        if ($request->hasFile('avatar')) {

            $file = $request->file('avatar');

            $filename = $file->getClientOriginalName();

            $folder = uniqid() . '-' . now()->timestamp;

            $file->storeAs('avatars/' . $folder, $filename);

            FileUpload::create([
                'filename' => $filename,
                'folder' => $folder
            ]);

            return $folder;
        }

        return '';
    }
}
```

---

# 🛣️ Routes

Open:

```
routes/web.php
```

Add:

```php
use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FileUploadController::class, 'index']);

Route::post('/upload', [FileUploadController::class, 'store']);
```

---

# 🎨 Blade View

Create the view file:

```
resources/views/welcome.blade.php
```

Add the **FilePond + Tailwind UI code** you used for drag & drop file upload.

This interface allows users to:

* Drag & Drop files
* Upload files asynchronously
* View uploaded files

---

# 📸 System Architecture

**Client Side**

* FilePond UI captures the file
* Drag & Drop interface improves UX

**Upload Process**

* FilePond sends the file asynchronously to the Laravel controller

**Storage**

* Files are stored in:

```
storage/app/avatars/{unique-folder}
```

**Database**

File metadata is saved in:

```
file_uploads table
```

Columns:

* filename
* folder
* timestamps

---

# ▶️ Run the Project

Start the Laravel development server.

```bash
php artisan serve
```

Open in browser:

```
http://localhost:8000
```

Your **Drag & Drop File Upload System** is now ready.

---
# Output
<img width="890" height="374" alt="image" src="https://github.com/user-attachments/assets/b7e4b57d-e13b-4586-8e28-95123832297d" />


# 👨‍💻 Developed By

**Manav Sanchela**

---

⭐ If you found this project helpful, consider giving it a **star on GitHub**.
