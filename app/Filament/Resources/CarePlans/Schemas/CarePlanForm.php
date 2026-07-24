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
                        'membership' => 'Membership (subscription / paid plan)',
                        'clinical' => 'Clinical (condition / doctor care plan)',
                        'wellness' => 'Wellness (lifestyle / prevention)',
                    ])
                    ->helperText('Membership = what paying members unlock. Clinical = illness/condition plans. Wellness = healthy lifestyle plans.')
                    ->required()
                    ->default('clinical'),
                Textarea::make('summary')
                    ->label('Short summary (member-friendly)')
                    ->helperText('1–2 lines shown in lists and cards.')
                    ->rows(2)
                    ->columnSpanFull(),
                TextInput::make('tagline')
                    ->label('Tagline')
                    ->helperText('Marketing line under the title.')
                    ->maxLength(255)
                    ->columnSpanFull(),
                RichEditor::make('body')
                    ->label('Full description')
                    ->helperText('Longer explanation for members.')
                    ->columnSpanFull(),
                Repeater::make('benefits')
                    ->label('Benefits (what the member gets)')
                    ->helperText('Show these as ✔ points in the app. Example: Free first consult, Family dashboard.')
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
                    ->label('Events / automatic rules')
                    ->helperText('Rules the system can apply later. Example: 1 free consult every month, 20% doctor discount. Clinical/Wellness can leave this empty.')
                    ->schema([
                        TextInput::make('code')
                            ->label('Code (system key)')
                            ->helperText('Example: monthly_complimentary_consult')
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
                                'complimentary' => 'Free / complimentary',
                                'discount' => 'Discount %',
                                'priority' => 'Priority access',
                                'bundle' => 'Monthly bundle / delivery',
                                'feature' => 'Feature unlock',
                            ])
                            ->required()
                            ->default('feature'),
                        TextInput::make('frequency')
                            ->helperText('once / monthly / ongoing')
                            ->maxLength(50),
                        TextInput::make('quantity')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('How many times (e.g. 1 per month)'),
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
