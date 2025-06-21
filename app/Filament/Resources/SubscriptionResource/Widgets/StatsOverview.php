<?php

namespace App\Filament\Resources\SubscriptionResource\Widgets;

use App\Models\Subscription;
use Filament\Resources\Form;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Forms\Components\TextInput;

class StatsOverview extends BaseWidget
{

    protected function getCards(): array
    {
        if (!auth()->user()->hasRole('super-admin')) {
            $f = auth()->user();
            return [
                Card::make('Approved subscriptions', Subscription::whereIn('cycle_id', function ($query) {
                    $query->select('id')->from('cycles')->where('end_date', '>=', now());
                })->where('is_approve',1)->where('user_id', auth()->user()->id)->count()),
                // Card::make("cost price for  : $f->name", function () {
                //     $users = DB::table('subscriptions')
                //         ->join('bundles', 'subscriptions.bundle_id', '=', 'bundles.id')
                //         ->select(DB::raw('SUM(price)'))
                //         ->where('user_id', auth()->user()->id)
                //         ->where('is_approve', 1)
                //         ->whereIn('cycle_id', function ($query) {
                //             $query->select('id')->from('cycles')->where('end_date', '>=', now());
                //         })
                //         ->get();
                //     $pattern = "/[^0-9]/"; // matches any character that is not a letter
                //     $replacement = "";
                //     $cleanString = preg_replace($pattern, $replacement, $users);
                //     return number_format(floatval($cleanString));
                // }),
            ];
        } else {
            return [
                Card::make('Approved Subscriptions', Subscription::whereIn('cycle_id', function ($query) {
                            $query->select('id')->from('cycles')->where('end_date', '>=', now());
                        })->where('is_approve', 1)->count()),
                            Card::make(' Unapproved Subscriptions', Subscription::whereIn('cycle_id', function ($query) {
                            $query->select('id')->from('cycles')->where('end_date', '>=', now());
                        })
                    ->where('is_approve', 0)->count()),                        
                Card::make("Total cost", function () {
                    $users = DB::table('subscriptions')
                        ->join('bundles', 'subscriptions.bundle_id', '=', 'bundles.id')
                        // ->select('u.id', 'u.name', DB::raw('COUNT(posts.id) as post_count'))
                        ->select(DB::raw('SUM(price) as Final_Total'))
                        ->where('is_approve', 1)
                            ->whereIn('cycle_id', function ($query) {
                            $query->select('id')->from('cycles')->where('end_date', '>=', now());
                        })
                        ->get();
                    $pattern = "/[^0-9]/"; // matches any character that is not a letter
                    $replacement = "";
                    $cleanString = preg_replace($pattern, $replacement, $users);
                    return number_format(floatval($cleanString));
                }),
            ];
        }
    }

}
