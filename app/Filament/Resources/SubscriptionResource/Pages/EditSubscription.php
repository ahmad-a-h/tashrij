<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected $messages = [
        'data.phone_number.unique' => 'This phone number already have a subscription in this cycle.',
    ];
    


        protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
