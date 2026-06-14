<?php

namespace App\Http\Requests\Admin\Website;

use App\Enums\Website\ContentStatus;
use App\Models\Platform\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPlatformAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('featured_image_id') === '') {
            $this->merge(['featured_image_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(BlogPost::class)],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'category' => ['required', 'string', Rule::in(config('marketing.blog_categories', []))],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'read_time_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'featured_image_id' => ['nullable', 'integer', 'exists:media_assets,id'],
        ];
    }
}
