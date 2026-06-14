<?php

namespace App\Http\Resources\Admin;

use App\Models\Platform\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BlogPost */
class BlogPostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'category' => $this->category,
            'status' => $this->status->value,
            'read_time_minutes' => $this->read_time_minutes,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'featured_image_id' => $this->featured_image_id,
            'featured_image_url' => $this->featuredImage?->url(),
            'author' => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ] : null,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'public_url' => route('marketing.blog.show', $this->slug),
        ];
    }
}
