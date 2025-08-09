<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'max:5120', // 5MB in KB
                'dimensions:min_width=100,min_height=100'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Image file is required',
            'image.file' => 'The uploaded file must be a valid file',
            'image.image' => 'The uploaded file must be an image',
            'image.max' => 'The image size must not exceed 5MB',
            'image.dimensions' => 'The image dimensions must be at least 100x100 pixels',
        ];
    }
}
