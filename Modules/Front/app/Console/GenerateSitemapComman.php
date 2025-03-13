<?php

namespace Modules\Front\app\Console;

use Illuminate\Support\Str;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Console\Command;
use Modules\User\Entities\User;
use Modules\Service\app\Models\Service;

class GenerateSitemapComman extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for the website';

    public function handle()
    {
        $sitemap = Sitemap::create();
        $sitemap->add(Url::create(config('app.url')));
        $doctors  = User::doctors()->all();
        $Services = Service::all();

        foreach ($doctors as $doctor) {
            $sitemap->add(route('front.doctor.profile',['doctor_id' =>$doctor->id , 'doctor_name' => $doctor->fullName]));
        }
        foreach ($Services as $service) {
            $sitemap->add(route('front.services',['service_id'=>$service->id , 'service_name'=>$service->title]));
        }
        $sitemap->add(route('front.aboutUs'));
        $sitemap->add(route('front.contactUs'));
        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('Sitemap generated successfully!');
    }
}
