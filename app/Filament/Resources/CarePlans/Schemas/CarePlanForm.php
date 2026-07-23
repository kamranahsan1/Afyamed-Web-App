<?php

namespace App\Filament\Resources\CarePlans\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CarePlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                        if (blank($get('slug')) && filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('category')
                    ->options([
                        'membership' => 'Membership',
                        'clinical' => 'Clinical',
                        'wellness' => 'Wellness',
                    ])
                    ->required()
                    ->default('clinical'),
                Textarea::make('summary')
                    ->rows(2)
                    ->columnSpanFull(),
                TextInput::make('tagline')
                    ->maxLength(255)
                    ->columnSpanFull(),
                RichEditor::make('body')
                    ->columnSpanFull(),
                Repeater::make('benefits')
                    ->label('Member benefits')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(2)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(0)
                    ->collapsible()
                    ->columnSpanFull(),
                Repeater::make('member_events')
                    ->label('Membership events / perks')
                    ->helperText('Rules applied to members, e.g. 1 complimentary consultation per month.')
                    ->schema([
                        TextInput::make('code')
                            ->helperText('Machine key, e.g. monthly_complimentary_consult')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options([
                                'complimentary' => 'Complimentary / free',
                                'discount' => 'Discount',
                                'priority' => 'Priority access',
                                'bundle' => 'Bundle / delivery',
                                'feature' => 'Feature unlock',
                            ])
                            ->required()
                            ->default('feature'),
                        TextInput::make('frequency')
                            ->helperText('e.g. monthly, once, ongoing')
                            ->maxLength(50),
                        TextInput::make('quantity')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('e.g. 1 free consult per month'),
                        TextInput::make('discount_percent')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Toggle::make('active')
                            ->default(true),
                    ])
                    ->defaultItems(0)
                    ->collapsible()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->required()
                    ->default('draft'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->required()
                    ->default(0),
            ]);
    }
}
