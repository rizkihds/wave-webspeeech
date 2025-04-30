<?php
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\DatePicker;
    use Filament\Forms\Concerns\InteractsWithForms;
    use Filament\Forms\Contracts\HasForms;
    use Filament\Forms\Form;
    use Filament\Notifications\Notification;
    use Livewire\Volt\Component;
    use function Laravel\Folio\{middleware, name};
    use App\Models\Meeting;

    middleware('auth');
    name('meetings.create');

    new class extends Component implements HasForms
    {
        use InteractsWithForms;

        public ?array $data = [];

        public function mount(): void
        {
            $this->form->fill();
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

            auth()->user()->meetings()->create($data);

            $this->form->fill();

            Notification::make()
                ->success()
                ->title('Project created successfully')
                ->send();

            $this->redirect('/meetings');
        }
    }
?>

<x-layouts.app>
    @volt('meetings.create')
        <x-app.container class="max-w-xl">
            <div class="flex items-center justify-between mb-5">
                <x-app.heading title="Create Meeting" description="Fill out the form below to create a new meeting" :border="true" />
            </div>
            <form wire:submit="create" class="space-y-6">
                {{ $this->form }}
                <div class="flex justify-end gap-x-3">
                    <x-button tag="a" href="/meetings" color="secondary">Cancel</x-button>
                    <x-button type="submit" class="text-white bg-primary-600 hover:bg-primary-500">
                        Create Meeting
                    </x-button>
                </div>
            </form>
        </x-app.container>
    @endvolt
</x-layouts.app>
