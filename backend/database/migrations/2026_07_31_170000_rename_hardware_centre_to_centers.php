<?php

use App\Models\AppSetting;
use App\Models\BlogPost;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->rename('Hardware Centre', 'Hardware Centers');
    }

    public function down(): void
    {
        $this->rename('Hardware Centers', 'Hardware Centre');
    }

    private function rename(string $from, string $to): void
    {
        User::where('name', 'like', "%{$from}%")->get()->each(
            fn (User $user) => $user->update(['name' => str_replace($from, $to, $user->name)])
        );

        Warehouse::where('name', 'like', "%{$from}%")->get()->each(
            fn (Warehouse $warehouse) => $warehouse->update(['name' => str_replace($from, $to, $warehouse->name)])
        );

        $setting = AppSetting::current();
        if ($setting->business_name && str_contains($setting->business_name, $from)) {
            $setting->update(['business_name' => str_replace($from, $to, $setting->business_name)]);
        }

        BlogPost::query()->get()->each(fn (BlogPost $post) => $post->update([
            'title' => str_replace($from, $to, $post->title),
            'excerpt' => str_replace($from, $to, $post->excerpt),
            'body' => str_replace($from, $to, $post->body),
            'author' => str_replace($from, $to, $post->author),
        ]));
    }
};
