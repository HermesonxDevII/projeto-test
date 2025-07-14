<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CustomLiveWireUploadController
{
    public function __invoke(Request $request)
    {
        // Verifica se o usuário está autenticado via seu helper
        abort_unless(getAuthenticatedParticipant(), 401);

        $disk = FileUploadConfiguration::disk();

        $filePaths = $this->validateAndStore($request->file('files'), $disk);

        return ['paths' => $filePaths];
    }

    protected function validateAndStore($files, $disk)
    {
        Validator::make(['files' => $files], [
            'files.*' => FileUploadConfiguration::rules(),
        ])->validate();

        $fileHashPaths = collect($files)->map(function ($file) use ($disk) {
            $filename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);

            return $file->storeAs('/'.FileUploadConfiguration::path(), $filename, [
                'disk' => $disk,
            ]);
        });

        return $fileHashPaths->map(fn ($path) =>
            str_replace(FileUploadConfiguration::path('/'), '', $path)
        );
    }
}
