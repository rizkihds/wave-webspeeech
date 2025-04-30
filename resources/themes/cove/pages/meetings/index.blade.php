<?php
    use App\Models\Meeting;
    use Filament\Forms\{Form, Concerns\InteractsWithForms, Contracts\HasForms};
    use Filament\Forms\Components\{Textarea, TextInput, DatePicker};
    use Filament\Notifications\Notification;
    use Filament\Tables;
    use Filament\Tables\{Table, Concerns\InteractsWithTable, Contracts\HasTable, Actions\Action, Actions\CreateAction, Actions\DeleteAction, Actions\EditAction, Actions\ViewAction, Columns\TextColumn};
    use Livewire\Volt\Component;
    use function Laravel\Folio\{middleware, name};

    middleware('auth');
    name('meetings');

    new class extends Component implements HasForms, Tables\Contracts\HasTable
    {
        use InteractsWithForms, InteractsWithTable;

        public ?array $data = [];
        public $description;

        public function table(Table $table): Table
        {
            return $table
                ->query(Meeting::query()->where('user_id', auth()->id()))
                ->columns([
                    TextColumn::make('name')
                        ->label('Title')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('description')
                        ->limit(50)
                        ->searchable(),
                    TextColumn::make('start_date')
                        ->date()
                        ->sortable(),
                    TextColumn::make('end_date')
                        ->date()
                        ->sortable(),
                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->defaultSort('created_at', 'desc')
                ->actions([
                    ViewAction::make()
                    ->url(fn (Meeting $record): string => route('meetings.view', ['id' => $record])),
                    EditAction::make()
                        ->slideOver()
                        ->modalWidth('md')
                        ->form([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->maxLength(1000),
                            DatePicker::make('start_date'),
                            DatePicker::make('end_date')
                                ->after('start_date'),
                        ]),
                    DeleteAction::make()
                        ->after(function () {
                            Notification::make()
                                ->success()
                                ->title('Voice-to-text deleted')
                                ->send();
                        })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    })
                    ->after(function () {
                        Notification::make()
                            ->success()
                            ->title('Data Has Loaded.')
                            ->send();
                    }),
                ])
                ->filters([
                    // Add any filters you want here
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ]);
        }

        public function form(Form $form): Form
        {
            return $form
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->maxLength(1000),
                    DatePicker::make('start_date'),
                    DatePicker::make('end_date')
                        ->after('start_date'),
                ])
                ->statePath('data');
        }

        public function create(): void
        {
            $data = $this->form->getState();
            $meeting = auth()->user()->meetings()->create($data);
            $this->form->fill();
            $this->dispatch('close-modal', id: 'create-meeting');

            Notification::make()
                ->success()
                ->title('Voice-to-text created successfully')
                ->send();
        }
    }
?>

<x-layouts.app>
    @volt('meetings')
        <x-app.side_actions-layout
            title="Voice-to-text" description="Check out your voice-to-text below"
            urls="/dashboard" prevmessage="Back To Dashboard" iconz="phosphor-air-traffic-control-duotone">
            <div class="flex items-center justify-between mb-5">
                <x-filament::modal id="create-meeting" width="md" :slide-over="true">
                    <x-slot name="trigger">
                        <x-button>New Voice-to-text</x-button>
                    </x-slot>
                    <x-slot name="header">
                        <h2 class="text-lg font-medium">Create Voice-to-text</h2>
                    </x-slot>
                    <form wire:submit="create" class="space-y-6">
                        {{ $this->form }}
                        <div class="flex justify-end mt-6">
                            <x-button type="submit" wire:target="create">
                                Create Voice-to-text
                            </x-button>
                        </div>
                    </form>
                </x-filament::modal>
            </div>
            <div class="overflow-x-auto border rounded-lg">
                {{ $this->table }}
            </div>
        </x-app.side_actions-layout>
    @endvolt
</x-layouts.app>
