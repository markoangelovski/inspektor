php artisan migrate
php artisan migrate:fresh // drops everything, only use in dev

composer run dev

php artisan make:controller UserController
php artisan make:controller PostController
php artisan make:migration create_posts_table
php artisan make:model Post
php artisan make:component Nav // .blade.php file and php Class
php artisan make:component Nav/NavTop --view // only .blade.php file, nested within views/components/nav/

composer require laravel/horizon
php artisan horizon:install
composer.json:
"dev": [
"Composer\\Config::disableProcessTimeout",
"npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74,#a78bfa\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"php artisan horizon\" \"npm run dev\" --names=server,queue,logs,horizon,vite --kill-others"
],
php artisan horizon

php artisan make:job

php artisan livewire:make Projects/Index
php artisan livewire:make Projects/FormModal

php artisan make:class Services/ProjectService

Add to app/Providers/AppServiceProvider.php for deployment on Azure Web App
public function boot(): void
{
// Added for Azure Web App deployment https://chatgpt.com/c/6941ba71-d628-8321-a010-e2ec69b400c1
if (app()->environment('production')) {
URL::forceScheme('https');
}
}

4️⃣ Correct WebJob folder structure (precise)

Azure only detects WebJobs in this exact location:

/App_Data/
└── jobs/
└── continuous/
└── queue-worker/
├── run-queue.sh
└── settings.job (optional but recommended)

5️⃣ settings.job (optional but recommended)

Create:

{
"is_singleton": true
}

Why

Prevents accidental double workers

Protects Redis & DB

6️⃣ Make the script executable (very important)

In GitHub Actions before artifact upload:

- name: Make WebJob executable
  run: chmod +x App_Data/jobs/continuous/queue-worker/run-queue.sh

If you forget this:

WebJob uploads

WebJob does NOT run

No error shown

7️⃣ Deployment checklist (step-by-step)
1️⃣ Add files to repo
App_Data/jobs/continuous/queue-worker/run-queue.sh
App_Data/jobs/continuous/queue-worker/settings.job

2️⃣ Ensure permissions step exists in CI
chmod +x run-queue.sh

3️⃣ Deploy via GitHub Actions (already done)
4️⃣ Azure Portal → WebJobs

You should see:

queue-worker

Status: Running

8️⃣ Verify WebJob health
Via Kudu (Advanced Tools)
https://<app>.scm.azurewebsites.net

Navigate:

site/data/jobs/continuous/queue-worker

Check:

job_log.txt

webjob-queue.log

php artisan view:clear

Would you like me to help you implement a "Cleanup" command that removes Redis keys for runs that crashed and never reached the Finalizer? (Essential for keeping Redis Cloud costs down).

The php artisan scout command set in Laravel Scout provides essential tools for managing full-text search indexes for Eloquent models. Key commands include php artisan scout:import "App\YourModel" to batch import existing records into the search index, php artisan scout:status to view an overview of search indices, and php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider" to publish the configuration file. If indexing issues arise, clearing cached configuration files or running the queue worker (e.g., php artisan queue:work) is often required to ensure asynchronous jobs are processed correctly.

// Run specific docker-compose.yml file and force image rebuild
docker compose -f docker-compose.img.build.yml up --build
