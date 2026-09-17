<?php

namespace App\Presentation\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;

class RequestDataExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('organizer') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
