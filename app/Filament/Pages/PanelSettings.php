<?php

namespace App\Filament\Pages;

use App\Helper\ColorHelper;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Inerba\DbConfig\AbstractPageSettings;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Phiki\Phast\Text;

class PanelSettings extends AbstractPageSettings
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static ?string $title = 'Panel';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-group'; // Uncomment if you want to set a custom navigation icon

    // protected ?string $subheading = ''; // Uncomment if you want to set a custom subheading

    // protected static ?string $slug = 'panel-settings'; // Uncomment if you want to set a custom slug

    protected string $view = 'filament.pages.panel-settings';

    protected function settingName(): string
    {
        return 'panel';
    }

    /**
     * Provide default values.
     *
     */

    protected function getPanelID(): string
    {
        return Filament::getCurrentOrDefaultPanel()->getId();
    }

    public function getDefaultData(): array
    {
        return [
            'panel_id' => $this->getPanelID(),
            'panel_route_'.$this->getPanelID() => $this->getPanelID(),
            'panel_show_logo_'.$this->getPanelID() => 'hide',
            'panel_spa_'.$this->getPanelID() => false,
            'panel_spa_prefetch_'.$this->getPanelID() => false,
            'panel_spa_exceptions_'.$this->getPanelID() => [],
            'panel_enable_login_'.$this->getPanelID() => true,
            'panel_font_'.$this->getPanelID() => 'Poppins',
            'panel_color_isRGB_'.$this->getPanelID() => false,
            'panel_enable_mfa_'.$this->getPanelID() => false,
            'panel_mfa_type_'.$this->getPanelID() => 'app',
            'panel_mfa_app_recoverable_'.$this->getPanelID() => false,
            'panel_mfa_app_recovery_code_regeneratable_'.$this->getPanelID() => false,
            'panel_mfa_app_code_window_'.$this->getPanelID() => 4,
            'panel_mfa_app_recovery_code_count_'.$this->getPanelID() => 8,
            'panel_mfa_email_code_expiry_'.$this->getPanelID() => 2,
            'panel_mfa_is_required_'.$this->getPanelID() => 'no',

        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make('Panel Branding')->schema([
                    TextInput::make('panel_id')
                        ->label('Panel ID')
                        ->disabled(),
                    TextInput::make('panel_name_'.$this->getPanelID())
                        ->label('Panel Name')
                        ->required(),
                    TextInput::make('panel_route_'.$this->getPanelID())
                        ->label('Panel Route')
                        ->required(),
                    Select::make('panel_show_logo_'.$this->getPanelID())
                        ->label('Panel Show Logo')
                        ->native(false)
                        ->options([
                            'show' => 'Show Logo',
                            'hide' => 'Hide Logo'
                        ])
                        ->live()
                        ->required(),
                    FileUpload::make('panel_logo_light_'.$this->getPanelID())
                        ->label('Panel Logo')
                        ->visible(fn($get): bool => $get('panel_show_logo_'.$this->getPanelID()) === 'show')
                        ->required(fn($get): bool => $get('panel_show_logo_'.$this->getPanelID()) === 'show')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('panel-assets')
                        ->columnSpan(1)
                        ->helperText('Upload a logo for panel'),

                    FileUpload::make('panel_logo_dark_'.$this->getPanelID())
                        ->label('Panel Dark Mode Logo')
                        ->visible(fn($get): bool => $get('panel_show_logo_'.$this->getPanelID()) === 'show')
                        ->required(fn($get): bool => $get('panel_show_logo_'.$this->getPanelID()) === 'show')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('panel-assets')
                        ->columnSpan(1)
                        ->helperText('Upload a logo for dark mode panel'),

                    TextInput::make('panel_logo_height_'.$this->getPanelID())
                        ->label('Panel Logo Height')
                        ->helperText('Use value like `30px`, `2rem`, `20%`, etc. This value applies as style attribute - height: 30px;')
                        ->columnSpanFull()
                        ->visible(fn($get): bool => $get('panel_show_logo_'.$this->getPanelID()) === 'show')
                        ->required(fn($get): bool => $get('panel_show_logo_'.$this->getPanelID()) === 'show'),

                    FileUpload::make('panel_favicon_'.$this->getPanelID())
                        ->label('Panel Favicon')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('panel-assets')
                        ->columnSpanFull()
                        ->helperText('Upload a favicon for panel'),

                    TextInput::make('panel_font_' . $this->getPanelID())
                        ->label('Panel Font')
                        ->placeholder('e.g. Inter, Poppins, or custom font name')
                        ->datalist([
                                'Barlow' => 'Barlow',
                                'DM Sans' => 'DM Sans',
                                'Hind' => 'Hind',
                                'IBM Plex Sans' => 'IBM Plex Sans',
                                'Inter' => 'Inter',
                                'Lato' => 'Lato',
                                'Manrope' => 'Manrope',
                                'Mulish' => 'Mulish',
                                'Nunito' => 'Nunito',
                                'Open Sans' => 'Open Sans',
                                'Poppins' => 'Poppins',
                                'Public Sans' => 'Public Sans',
                                'Quicksand' => 'Quicksand',
                                'Roboto' => 'Roboto',
                                'Rubik' => 'Rubik',
                                'Source Sans Pro' => 'Source Sans Pro',
                                'Work Sans' => 'Work Sans',
                            ]
                        )
                        ->columnSpanFull()
                        ->helperText('Enter a Google Font name or type a custom one'),

                    Repeater::make('panel_color_'.$this->getPanelID())
                        ->label('Panel Color')
                        ->schema([
                            TextInput::make('panel_color_name_'.$this->getPanelID())
                                ->label('Panel Color Name')
                                ->required(),
                            ColorPicker::make('panel_color_'.$this->getPanelID())
                                ->label('Pick Panel Color')
                                ->rgb()
                                ->regex('/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    $shades = ColorHelper::generateOklchFromRGBShades($state);
                                    $set('panel_color_shade_' . $this->getPanelID(), $shades ?? '// Invalid RGB');
                                    })
                                ->required(),
                            Textarea::make('panel_color_shade_'.$this->getPanelID())
                                ->label('Panel Color Shade')
                                ->rows(3)
                                ->placeholder('Choose a color to generate shade')
                                ->columnSpanFull()
                        ])->columns(2)->columnSpanFull(),

                    Toggle::make('panel_color_isRGB_'.$this->getPanelID())
                        ->label('Use Panel Color as RGB')
                        ->helperText('If toggled `OFF` Oklch Color Shades will be used, else Only RGB will be used'),

                    Toggle::make('panel_default')
                        ->label('Is this default panel?')
                        ->helperText('Toggle `ON` to set this panel as default')
                        ->dehydrateStateUsing(fn(bool $state, $get) => $state ? $get('panel_id') : null)


                ])->columns(2),

                Components\Section::make('Panel Navigation')->schema([

                    Toggle::make('panel_spa_'.$this->getPanelID())
                        ->label('Enable Panel SPA')
                        ->live(),

                    Toggle::make('panel_spa_prefetch_'.$this->getPanelID())
                        ->label('Panel Spa Prefetching')
                        ->visible(fn($get) => $get('panel_spa_'.$this->getPanelID())),

                    TagsInput::make('panel_spa_exceptions_'.$this->getPanelID())
                        ->label('Panel Spa Exceptions')
                        ->visible(fn($get) => $get('panel_spa_'.$this->getPanelID()))
                        ->columnSpanFull(),
                ])->columns(2),

                Components\Section::make('Panel Authentication')->schema([
                    Toggle::make('panel_enable_login_'.$this->getPanelID())
                        ->label('Enable Login'),
                    Toggle::make('panel_enable_registration_'.$this->getPanelID())
                        ->label('Enable Registration'),
                    Toggle::make('panel_enable_profile_'.$this->getPanelID())
                        ->live()
                        ->label('Enable Profile'),
                    Toggle::make('panel_enable_mfa_'.$this->getPanelID())
                        ->visible(fn($get) => $get('panel_enable_profile_'.$this->getPanelID()))
                        ->live()
                        ->label('Enable MFA'),
                    Select::make('panel_mfa_type_'.$this->getPanelID())
                        ->label('MFA Type')
                        ->visible(fn($get) => $get('panel_enable_mfa_'.$this->getPanelID()))
                        ->options([
                            'app' => 'Authenticator App',
                            'email' => 'Email',
                        ])
                    ->live()
                    ->required(fn($get) => $get('panel_enable_mfa_'.$this->getPanelID())),
                    TextInput::make('panel_mfa_app_brand_name_'.$this->getPanelID())
                        ->label('MFA Authenticator Brand Name')
                        ->helperText('Optional: Leave to use app name as authenticator brand name')
                        ->visible(fn($get): bool => $get('panel_enable_mfa_'.$this->getPanelID()) && $get('panel_mfa_type_'.$this->getPanelID()) === 'app'),

                    Toggle::make('panel_mfa_app_recoverable_'.$this->getPanelID())
                        ->label('MFA Authenticator Recoverable')
                        ->live()
                        ->visible(fn($get): bool => $get('panel_enable_mfa_'.$this->getPanelID()) && $get('panel_mfa_type_'.$this->getPanelID()) === 'app'),

                    Toggle::make('panel_mfa_app_recovery_code_regeneratable_'.$this->getPanelID())
                        ->label('Allow to regenerate recovery code')
                        ->live()
                        ->visible(fn($get): bool => $get('panel_enable_mfa_'.$this->getPanelID()) && $get('panel_mfa_app_recoverable_'.$this->getPanelID()) && $get('panel_mfa_type_'.$this->getPanelID()) === 'app'),

                    TextInput::make('panel_mfa_app_recovery_code_count_'.$this->getPanelID())
                        ->label('MFA Authenticator Recovery Code Count')
                        ->numeric()
                        ->minValue(8)
                        ->maxValue(16)
                        ->visible(fn($get): bool => $get('panel_enable_mfa_'.$this->getPanelID()) && $get('panel_mfa_app_recoverable_'.$this->getPanelID()) && $get('panel_mfa_type_'.$this->getPanelID()) === 'app'),

                    TextInput::make('panel_mfa_app_code_window_'.$this->getPanelID())
                        ->label('MFA Authenticator Code Window')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(8)
                        ->visible(fn($get): bool => $get('panel_enable_mfa_'.$this->getPanelID()) && $get('panel_mfa_type_'.$this->getPanelID()) === 'app'),

                    TextInput::make('panel_mfa_email_code_expiry_'.$this->getPanelID())
                        ->label('MFA Email Expiry (in minutes)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(8)
                        ->visible(fn($get): bool => $get('panel_enable_mfa_'.$this->getPanelID()) && $get('panel_mfa_type_'.$this->getPanelID()) === 'email'),

                    Select::make('panel_mfa_is_required_'.$this->getPanelID())
                        ->label('MFA Is Required')
                        ->options([
                            'no' => 'No',
                            'yes' => 'Yes',
                        ])
                        ->columnSpanFull()
                        ->visible(fn($get) => $get('panel_enable_mfa_'.$this->getPanelID()))
                        ->helperText('Force to enable MFA, even for those panels in which MFA is not set yet!')

                ])->columns(2)
            ])
            ->statePath('data');
    }
}
