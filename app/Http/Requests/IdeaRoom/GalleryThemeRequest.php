<?php

namespace App\Http\Requests\IdeaRoom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GalleryThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $themeId = $this->route('theme')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('gallery_themes')->ignore($themeId)],
            'description' => ['nullable', 'string', 'max:500'],
            'background_image' => [$this->isMethod('POST') ? 'required' : 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'template_overlay' => ['nullable', 'image', 'mimes:png', 'max:5120'],
            'whiteboard_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'frame_slots' => ['required', 'string'],
            'writing_area' => ['nullable', 'string'],
            'aspect_ratio' => ['required', 'string', 'regex:/^\d+ \/ \d+$/'],
            'greyscale' => ['nullable'],
            'is_published' => ['nullable'],
            'is_default' => ['nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'background_image.required' => 'A background image is required when creating a new theme.',
            'frame_slots.required' => 'At least one frame slot must be defined.',
            'aspect_ratio.regex' => 'Aspect ratio must be in the format "16 / 9".',
        ];
    }
}
