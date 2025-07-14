<?php

namespace App\Http\Controllers;

use Livewire\Drawer\Utils;

class CustomLivewirePreviewFileController
{
    public function __invoke($filename)
    {
        abort_unless(getAuthenticatedParticipant(), 401);

        return Utils::pretendPreviewResponseIsPreviewFile($filename);
    }
}
