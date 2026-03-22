<?php

namespace App\Console\Commands;

use App\Models\HelpArticle;
use Database\Seeders\Support\HelpArticleBody;
use Illuminate\Console\Command;

class SeedHelpPremiersPas extends Command
{
    protected $signature = 'help:seed-premiers-pas';

    protected $description = 'Met à jour le contenu HTML des 5 articles « Premiers pas » du centre d’aide';

    public function handle(): int
    {
        $ok = 0;
        $placeholder = '<p></p>';

        foreach (HelpArticleBody::PREMIERS_PAS_SLUGS as $slug) {
            $data = HelpArticleBody::forSlug($slug, $placeholder);

            $article = HelpArticle::query()->where('slug', $slug)->first();
            if (! $article) {
                $this->error("✗ Article introuvable : {$slug}");

                continue;
            }

            $article->update([
                'resume' => $data['resume'],
                'contenu' => $data['contenu'],
            ]);
            $this->info("✓ {$slug}");
            $ok++;
        }

        $this->newLine();

        if ($ok !== count(HelpArticleBody::PREMIERS_PAS_SLUGS)) {
            $this->warn('Mise à jour partielle.');

            return Command::FAILURE;
        }

        $this->info('5 articles « Premiers pas » mis à jour avec succès.');

        return Command::SUCCESS;
    }
}
