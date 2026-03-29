<?php

namespace App\Console\Commands;

use App\Models\KnowledgeArticle;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('kb:seed')]
#[Description('Seed the knowledge base with support articles and generate embeddings')]
class SeedKnowledgeBase extends Command
{
    public function handle(): void
    {
        $articles = [
            [
                'title' => 'Return Policy',
                'category' => 'returns',
                'content' => 'Items can be returned within 30 days of delivery for a full refund. Items must be unused and in original packaging. Damaged items can be returned at any time with photo evidence. Refunds are processed within 5-7 business days after we receive the returned item.',
            ],
            [
                'title' => 'Shipping Information',
                'category' => 'shipping',
                'content' => 'Standard shipping takes 5-7 business days and is free on orders over $50. Express shipping takes 2-3 business days and costs $12.99. Next-day shipping is available for $24.99. All orders include tracking information sent via email.',
            ],
            [
                'title' => 'Order Cancellation',
                'category' => 'orders',
                'content' => 'Orders can be cancelled within 1 hour of placement if they have not been shipped. To cancel, contact support with your order number. Once an order has shipped, it cannot be cancelled but can be returned after delivery.',
            ],
            [
                'title' => 'Payment Methods',
                'category' => 'billing',
                'content' => 'We accept Visa, Mastercard, American Express, and PayPal. All transactions are securely processed. For failed payments, check your card details and try again. Contact your bank if the issue persists.',
            ],
            [
                'title' => 'Account Security',
                'category' => 'account',
                'content' => 'We recommend using a strong password and enabling two-factor authentication. If you suspect unauthorized access, change your password immediately and contact support. We will never ask for your password via email.',
            ],
            [
                'title' => 'Damaged Item Claims',
                'category' => 'returns',
                'content' => 'If you receive a damaged item, take photos of the damage and packaging. Contact support within 48 hours of delivery. We will arrange a free return shipping label and send a replacement or issue a full refund, whichever you prefer.',
            ],
            [
                'title' => 'Subscription Management',
                'category' => 'billing',
                'content' => 'You can manage your subscription from your account settings. Subscriptions can be paused for up to 3 months or cancelled at any time. Cancelled subscriptions remain active until the end of the current billing period.',
            ],
            [
                'title' => 'International Shipping',
                'category' => 'shipping',
                'content' => 'We ship to over 40 countries. International orders typically take 10-15 business days. Customs duties and taxes are the responsibility of the buyer. Tracking is available for all international shipments.',
            ],
        ];

        $this->info('Seeding knowledge base...');

        $bar = $this->output->createProgressBar(count($articles));

        foreach ($articles as $data) {
            $article = KnowledgeArticle::updateOrCreate(
                ['title' => $data['title']],
                $data
            );

            $article->generateEmbedding();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Knowledge base seeded with '.count($articles).' articles!');
    }
}
