<?php

namespace App\Models;

use Database\Factories\KnowledgeArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Ai\Embeddings;

class KnowledgeArticle extends Model
{
    /** @use HasFactory<KnowledgeArticleFactory> */
    use HasFactory;

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
