<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use App\Models\Group;
use App\Models\Season;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Génère le sitemap du site';

    public function handle()
    {
        $this->info('Génération du sitemap...');

        // Supprimer l'ancien sitemap s'il existe
        $sitemapPath = public_path('sitemap.xml');
        if (File::exists($sitemapPath)) {
            File::delete($sitemapPath);
            $this->info('Ancien sitemap supprimé.');
        }

        $sitemap = SitemapGenerator::create(config('app.url'))
            ->hasCrawled(function (Url $url) {
                if ($url->segment(1) === '') {
                    return $url->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(1.0);
                }
                return $url->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8);
            });

        // Pages statiques
        $sitemap->getSitemap()
            ->add(Url::create('/')->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(1.0))
            ->add(Url::create('/feedback')->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)->setPriority(0.8));

        // Groupes
        Group::all()->each(function (Group $group) use ($sitemap) {
            $sitemap->getSitemap()->add(Url::create("/groups/{$group->id}")
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7));

            // Saisons des groupes
            $group->seasons()->get()->each(function (Season $season) use ($sitemap, $group) {
                $sitemap->getSitemap()->add(Url::create("/groups/{$group->id}/seasons/{$season->id}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.6));
            });
        });

        $sitemap->writeToFile($sitemapPath);

        $this->info('Sitemap généré avec succès !');
    }
}
