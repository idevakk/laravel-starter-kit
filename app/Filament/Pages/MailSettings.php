<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Inerba\DbConfig\AbstractPageSettings;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailSettings extends AbstractPageSettings
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static ?string $title = 'Mail';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-envelope-open'; // Uncomment if you want to set a custom navigation icon

    // protected ?string $subheading = ''; // Uncomment if you want to set a custom subheading

    // protected static ?string $slug = 'mail-settings'; // Uncomment if you want to set a custom slug

    protected string $view = 'filament.pages.mail-settings';

    protected function settingName(): string
    {
        return 'mail';
    }

    /**
     * Provide default values.
     *
     * @return array<string, mixed>
     */
    public function getDefaultData(): array
    {
        return [
            'mail_driver' => 'smtp',
            'mail_encryption' => 'tls',
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label('Test Connection')
                ->color('gray')
                ->icon('heroicon-o-paper-airplane')
                ->action(fn() => $this->testSMTPConnection()),

            ...parent::getHeaderActions(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make('Mail Settings')->schema([
                    TextInput::make('mail_driver')
                        ->label('Mail Driver')
                        ->placeholder('smtp')
                        ->required(),
                    TextInput::make('mail_host')
                        ->label('Mail Host')
                        ->placeholder('mail.example.com')
                        ->required(),
                    TextInput::make('mail_port')
                        ->label('Mail Port')
                        ->integer()
                        ->minValue(1)
                        ->maxValue(65535)
                        ->placeholder('587')
                        ->required(),
                    TextInput::make('mail_username')
                        ->label('Mail Username')
                        ->placeholder('username')
                        ->required(),
                    TextInput::make('mail_password')
                        ->label('Mail Password')
                        ->placeholder('password')
                        ->required(),
                    Select::make('mail_encryption')
                        ->label('Mail Encryption')
                        ->options([
                            'tls' => 'TLS',
                            'ssl' => 'SSL'
                        ])
                        ->native(false)
                        ->required(),

                    TextInput::make('mail_from_name')
                        ->label('Mail From Name')
                        ->placeholder('John Doe')
                        ->required(),
                    TextInput::make('mail_from_address')
                        ->label('Mail From Address')
                        ->placeholder('john@example.com')
                        ->required(),
                ]),
            ])
            ->statePath('data');
    }

    private function testSMTPConnection(): void
    {
        $settings = $this->data;

        // Required keys for testing connection
        $requiredFields = [
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
        ];

        // Check for missing or empty required fields
        $missingFields = collect($requiredFields)->filter(function ($field) use ($settings) {
            return empty($settings[$field]);
        });

        if ($missingFields->isNotEmpty()) {
            Notification::make()
                ->title('Missing required mail settings: ' . $missingFields->implode(', '))
                ->danger()
                ->send();
            return;
        }

        try {
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $settings['mail_host'],
                (int) $settings['mail_port'],
                strtolower($settings['mail_encryption']) === 'ssl'
            );

            $transport->setUsername($settings['mail_username']);
            $transport->setPassword($settings['mail_password']);

            $transport->start(); // Attempt connection

            Notification::make()
                ->title('SMTP connection successful!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('SMTP connection failed: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
