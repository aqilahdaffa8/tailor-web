<?php

namespace App\Http\Requests;

class StoreTutorialRequest extends TutorialFormRequest
{
    protected function filePdfWajib(): bool
    {
        return true;
    }
}
