<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Ai\Embeddings;

class KnowledgeArticle extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }

    public function generateEmbedding()
    {
        $text = "{$this->title}\n\n{$this->content}";

        $response = Embeddings::for([$text])
            ->dimensions(1536)
            ->generate();

        $this->update(['embedding' => $response->embeddings[0]]);
    }
}
