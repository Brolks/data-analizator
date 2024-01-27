<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use App\Models\File;
use App\Services\SqlDumpProcessor;
use App\Services\AccessLogProcessor;

class FileUploadController extends Controller
{
    public function createForm()
    {
        $files = File::all(); // Получение всех записей из таблицы файлов

        return view('file-upload', compact('files'));
    }


    public function fileLoad($id)
    {
        $file = File::findOrFail($id);

        // Определение типа файла и выбор соответствующего процессора
        if ($file->file_type === 'sql') {
            $processor = new SqlDumpProcessor();
            $data = $processor->processSqlDumpPrint($file);
        } elseif ($file->file_type === 'apache_access' || $file->file_type === 'apache_errors') {
            $processor = new AccessLogProcessor();
            $data = $processor->processAccessLogPrint($file);
        } else {
            // Обработка неизвестного типа файла или вывод ошибки
            return response()->json(['error' => 'Неизвестный тип файла.'], 400);
        }

        return response()->json(['file' => $file, 'data' => $data]);
    }


    public function getFiles()
    {
        $files = File::all(); 
        return response()->json($files); 
    }

    public function fileUpload(Request $req)
    {
        $req->validate([
            'fileType' => 'required',
            'file' => 'required|file'
        ]);

        $fileModel = new File;

        if ($req->file()) {
            $fileType = $req->fileType;
            $file = $req->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Выбор папки в зависимости от типа файла
            $folder = $fileType === 'sql' ? 'sql_uploads' : 'apache_uploads';

            // Сохранение файла в выбранной папке
            $filePath = $file->storeAs($folder, $fileName, 'public');

            if ( $folder === 'apache_uploads') {
                $fileType = strpos($filePath, 'access') ? 'apache_access' : 'apache_errors';
            }

            $fileModel->name = $fileName;
            $fileModel->file_path = $filePath;
            $fileModel->file_type = $fileType;
            $fileModel->save();

            return response()->json(['success', 'Файл успешно загружен.'], 200);
        }
    }


    public function deleteFile($id)
    {
        $file = File::findOrFail($id);

        // Удаление файла из файловой системы
        Storage::disk('public')->delete($file->file_path);

        // Удаление записи о файле из базы данных
        $file->delete();

        return response()->json(['message' => 'Файл успешно удалён.'], 200);
    }
}
