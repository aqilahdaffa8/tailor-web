<?php

namespace App\Http\Requests;

use App\Models\Tutorial;

class UpdateTutorialRequest extends TutorialFormRequest
{
    protected function filePdfWajib(): bool
    {
        $tutorial = $this->route('tutorial');

        return ! ($tutorial instanceof Tutorial) || $tutorial->tipe !== 'pdf' || ! $tutorial->file_or_url;
    }
}
