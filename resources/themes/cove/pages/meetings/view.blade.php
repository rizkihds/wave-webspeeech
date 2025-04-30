<?php

    use function Laravel\Folio\{middleware, name};
    use Filament\Forms\{Form, Concerns\InteractsWithForms, Contracts\HasForms};
    use Filament\Forms\Components\{Textarea, TextInput, Hidden};
    // use Filament\Forms\Components\Component;
    use Filament\Notifications\Notification;
    use Livewire\Volt\Component;
    use Livewire\Attributes\Computed;
    use App\Models\Meeting;
    middleware('auth');
    name('meetings.view');


    new class extends Component implements HasForms
    {
        public ?array $data = [];

        use InteractsWithForms;
        public $description, $meeting_id, $interim_span;

        public $form;

        #[Computed]
        public function get_meeting()
        {
            return Meeting::where('id', request('id'))->firstOrFail();
        }

        // public function form(Form $form): Form
        // {
        //     return $form
        //         ->schema([
        //             Textarea::make('description')
        //             ->label(__('Meeting Notes'))
        //             ->id('final_span')
        //             ->rows(4)
        //             ->cols(75)
        //         ]);
        // }
        public function mount()
        {
            $this->form = (object)[
                'description' => '',
                'meeting_id' => '',
                'interim_span' => ''
            ];
        
            $this->meeting_id = request('id'); // <-- ini penting supaya hidden input keisi!
        }

        public function create()
        {
            // 1. Validasi data dulu
            $this->validate([
                'description' => 'required|string',
                'meeting_id' => 'required|exists:meetings,id',
            ]);

            // 2. Cari Meeting berdasarkan ID
            $meeting = Meeting::find($this->meeting_id);

            // 3. Update field tertentu (misal "notes" atau "content" tergantung tabel kamu)
            $meeting->update([
                'notes' => $this->description, // atau 'content' => $this->description, tergantung field
            ]);

            // 4. (Optional) Kirim notifikasi sukses
            Notification::make()
                ->success()
                ->title('Meeting updated successfully.')
                ->send();

            // 5. (Optional) Reset input di form supaya kosong lagi
            $this->reset('description', 'interim_span');
        }
    }
?>

<x-layouts.app>
    
    @volt('meetings.view')
        
        <x-app.side_actions-layout
            title="{{$this->get_meeting->name}} Meeting Demonstration"
                description="Meeting Areas"
                urls="/meetings"
                prevmessage="Back To List"
                iconz="phosphor-list-dashes-fill"
            >
            <h1 class="center" id="headline"></h1>
            <div id="info">
                <p id="info_start">Click on the microphone icon and begin speaking.</p>
                <p id="info_speak_now">Speak now.</p>
                <p id="info_no_speech">No speech was detected. You may need to adjust your
                    <a href="//support.google.com/chrome/bin/answer.py?hl=en&amp;answer=1407892">
                    microphone settings</a>.
                </p>
                <p id="info_no_microphone" style="display:none">
                    No microphone was found. Ensure that a microphone is installed and that
                    <a href="//support.google.com/chrome/bin/answer.py?hl=en&amp;answer=1407892">
                    microphone settings</a> are configured correctly.
                </p>
                <p id="info_allow">Click the "Allow" button above to enable your microphone.</p>
                <p id="info_denied">Permission to use microphone was denied.</p>
                <p id="info_blocked">Permission to use microphone is blocked. To change,
                    go to chrome://settings/contentExceptions#media-stream
                </p>
                <p id="info_upgrade">Web Speech API is not supported by this browser.
                    Upgrade to <a href="//www.google.com/chrome">Chrome</a>
                    version 25 or later.
                </p>
            </div>
            <!-- <div class="right">
                <button id="start_button" onclick="startButton(event)">
                    <img id="start_img" src="/storage/demo/mic.gif" alt="Start">
                </button>
            </div> -->
            <div id="results">
                <form wire:submit.prevent="create" class="space-y-6">
                    <textarea  wire:model.defer="description" id="final_span" class="final" rows="4" cols="75" name="description"></textarea>
                    <div class="flex justify-end mt-6">
                        <x-button id="start_button" onclick="startButton(event)">
                            <img id="start_img" src="/storage/demo/mic.gif" alt="Start">
                        </x-button>
                        <input type="hidden" name="meeting_id" wire:model.defer="meeting_id">
                        <input type="hidden" name="interim_span" wire:model.defer="interim_span" id="interim_span">
                        <button type="submit" id="end_button">
                            Stop Talk
                        </button>

                    </div>
                </form>
                <!-- <span id="final_span" class="final"></span> -->
                <!-- <textarea id="final_span" class="final"rows="4" cols="75" wire:model.meeting_detail="description"></textarea> -->
                <!-- <span id="interim_span" class="interim"></span> -->
                <!-- <p> -->
            </div>
            <div class="center">
                <div class="sidebyside" style="text-align:right">
                    <button id="copy_button" class="button" onclick="copyButton()">
                    Copy and Paste</button>
                    <div id="copy_info" class="info">
                    Press Control-C to copy text.<br>(Command-C on Mac.)
                    </div>
                </div>
                <div class="sidebyside">
                    <button id="docx_button" class="button" onclick="docxButton()">
                    Export To Docx</button>
                    <div id="docx_info" class="info">
                    Text export to docx format application
                    </div>
                </div>
                <p>
                <div id="div_language">
                    <select id="select_language" onchange="updateCountry()"></select>
                    &nbsp;&nbsp;
                    <select id="select_dialect"></select>
                </div>
            </div>
        </x-app.side_actions-layout>
    @endvolt



<style>
  * {
    font-family: Verdana, Arial, sans-serif;
  }
  a:link {
    color:#000;
    text-decoration: none;
  }
  a:visited {
    color:#000;
  }
  a:hover {
    color:#33F;
  }
  .button {
    background: -webkit-linear-gradient(top,#008dfd 0,#0370ea 100%);
    border: 1px solid #076bd2;
    border-radius: 3px;
    color: #fff;
    display: none;
    font-size: 13px;
    font-weight: bold;
    line-height: 1.3;
    padding: 8px 25px;
    text-align: center;
    text-shadow: 1px 1px 1px #076bd2;
    letter-spacing: normal;
  }
  .center {
    padding: 10px;
    text-align: center;
  }
  .final {
    color: black;
    padding-right: 3px; 
  }
  .interim {
    color: gray;
  }
  .info {
    font-size: 14px;
    text-align: center;
    color: #777;
    display: none;
  }
  .right {
    float: right;
  }
  .sidebyside {
    display: inline-block;
    width: 25%;
    min-height: 40px;
    text-align: left;
    vertical-align: top;
  }
  #headline {
    font-size: 40px;
    font-weight: 300;
  }
  #info {
    font-size: 20px;
    text-align: center;
    color: #777;
    visibility: hidden;
  }
  #results {
    font-size: 14px;
    font-weight: bold;
    border: 1px solid #ddd;
    padding: 15px;
    text-align: left;
    min-height: 150px;
  }
  #start_button {
    border: 0;
    background-color:transparent;
    padding: 0;
  }
</style>

    <script>
    var  user = "{{auth()->user()->name}}";
    var final_span = document.getElementById("final_span");
    var langs =
    [
        ['Afrikaans',       ['af-ZA']],
        ['Bahasa Indonesia',['id-ID']],
        ['Bahasa Melayu',   ['ms-MY']],
        ['Català',          ['ca-ES']],
        ['Čeština',         ['cs-CZ']],
        ['Deutsch',         ['de-DE']],
        ['English',         ['en-AU', 'Australia'],
                            ['en-CA', 'Canada'],
                            ['en-IN', 'India'],
                            ['en-NZ', 'New Zealand'],
                            ['en-ZA', 'South Africa'],
                            ['en-GB', 'United Kingdom'],
                            ['en-US', 'United States']],
        ['Español',         ['es-AR', 'Argentina'],
                            ['es-BO', 'Bolivia'],
                            ['es-CL', 'Chile'],
                            ['es-CO', 'Colombia'],
                            ['es-CR', 'Costa Rica'],
                            ['es-EC', 'Ecuador'],
                            ['es-SV', 'El Salvador'],
                            ['es-ES', 'España'],
                            ['es-US', 'Estados Unidos'],
                            ['es-GT', 'Guatemala'],
                            ['es-HN', 'Honduras'],
                            ['es-MX', 'México'],
                            ['es-NI', 'Nicaragua'],
                            ['es-PA', 'Panamá'],
                            ['es-PY', 'Paraguay'],
                            ['es-PE', 'Perú'],
                            ['es-PR', 'Puerto Rico'],
                            ['es-DO', 'República Dominicana'],
                            ['es-UY', 'Uruguay'],
                            ['es-VE', 'Venezuela']],
        ['Euskara',         ['eu-ES']],
        ['Français',        ['fr-FR']],
        ['Galego',          ['gl-ES']],
        ['Hrvatski',        ['hr_HR']],
        ['IsiZulu',         ['zu-ZA']],
        ['Íslenska',        ['is-IS']],
        ['Italiano',        ['it-IT', 'Italia'],
                            ['it-CH', 'Svizzera']],
        ['Magyar',          ['hu-HU']],
        ['Nederlands',      ['nl-NL']],
        ['Norsk bokmål',    ['nb-NO']],
        ['Polski',          ['pl-PL']],
        ['Português',       ['pt-BR', 'Brasil'],
                            ['pt-PT', 'Portugal']],
        ['Română',          ['ro-RO']],
        ['Slovenčina',      ['sk-SK']],
        ['Suomi',           ['fi-FI']],
        ['Svenska',         ['sv-SE']],
        ['Türkçe',          ['tr-TR']],
        ['български',       ['bg-BG']],
        ['Pусский',         ['ru-RU']],
        ['Српски',          ['sr-RS']],
        ['한국어',            ['ko-KR']],
        ['中文',             ['cmn-Hans-CN', '普通话 (中国大陆)'],
                            ['cmn-Hans-HK', '普通话 (香港)'],
                            ['cmn-Hant-TW', '中文 (台灣)'],
                            ['yue-Hant-HK', '粵語 (香港)']],
        ['日本語',           ['ja-JP']],
        ['Lingua latīna',   ['la']]
    ];

    for (var i = 0; i < langs.length; i++) {
        select_language.options[i] = new Option(langs[i][0], i);
    }

    select_language.selectedIndex = 1;
    
    updateCountry();

    select_dialect.selectedIndex = 0;
    
    showInfo('info_start');

    function updateCountry() {
        for (var i = select_dialect.options.length - 1; i >= 0; i--) {
            select_dialect.remove(i);
        }
        var list = langs[select_language.selectedIndex];
        for (var i = 1; i < list.length; i++) {
            select_dialect.options.add(new Option(list[i][1], list[i][0]));
        }
        select_dialect.style.visibility = list[1].length == 1 ? 'hidden' : 'visible';
    }

    var create_email = false;
    var final_transcript = '';
    var recognizing = false;
    var ignore_onend;
    var start_timestamp;

    var currentdate = new Date(); 
    var datetime = currentdate.getDate() + "/"
                + (currentdate.getMonth()+1)  + "/" 
                + currentdate.getFullYear() + " "  
                + currentdate.getHours() + ":"  
                + currentdate.getMinutes();
                // + currentdate.getSeconds();

    final_span.value = linebreak(user +' pada ' + datetime + ' :'+final_transcript );

    if (!('webkitSpeechRecognition' in window)) {
        upgrade();
    } else {
        start_button.style.display = 'inline-block';
        var recognition = new webkitSpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.maxAlternatives = 3;

        recognition.onstart = function() {
            recognizing = true;
            showInfo('info_speak_now');
            start_img.src = '/storage/demo/mic-animate.gif';
        };

        recognition.onerror = function(event) {
            if (event.error == 'no-speech') {
                start_img.src = '/storage/demo/mic.gif';
                showInfo('info_no_speech');
                ignore_onend = true;
            }
            if (event.error == 'audio-capture') {
                start_img.src = '/storage/demo/mic.gif';
                showInfo('info_no_microphone');
                ignore_onend = true;
            }
            if (event.error == 'not-allowed') {
                if (event.timeStamp - start_timestamp < 100) {
                    showInfo('info_blocked');
                } else {
                    showInfo('info_denied');
                }

                ignore_onend = true;
            }
        };

        recognition.onend = function() {
            recognizing = false;
            if (ignore_onend) {
                return;
            }
            start_img.src = '/storage/demo/mic.gif';
            if (!final_transcript) {
                showInfo('info_start');
                return;
            }
            showInfo('');
            if (window.getSelection) {
                window.getSelection().removeAllRanges();
                var range = document.createRange();
                range.selectNode(document.getElementById('final_span'));
                window.getSelection().addRange(range);
            }
            // if (create_email) {
            //     create_email = false;
            //     createEmail();
            // }
        };

        recognition.onresult = function(event) {
            var interim_transcript = '';

            for (var i = event.resultIndex; i < event.results.length; ++i) {
                let spoken = event.results[i][0].transcript.trim().toLowerCase();

                // Cek perintah stop
                if (spoken.includes("stop meeting")) {
                    recognizing = false;
                    recognition.stop();
                    start_img.src = '/storage/demo/mic.gif';
                    showInfo('info_start');
                    alert("Meeting dihentikan oleh perintah suara.");
                    return;
                }

                if (event.results[i].isFinal) {
                    final_transcript += punctuator(autoCorrect(spoken));
                } else {
                    interim_transcript += autoCorrect(spoken);
                }
            }

            final_span.value = linebreak(user + ' pada ' + datetime + ' :' + final_transcript);
            interim_span.value = linebreak(user + ' pada ' + datetime + ' :' + interim_transcript);

            if (final_transcript || interim_transcript) {
                showButtons('inline-block');
            }
        };
    }

    function punctuator(text) {
        text = text.trim();

        // Capitalize
        text = text.charAt(0).toUpperCase() + text.slice(1);

        // Tambah koma setelah kata penghubung umum
        text = text.replace(/\b(ya|jadi|lalu|terus|kemudian)\b/gi, '$1,');

        // Tambah tanda tanya jika kalimat tanya
        text = text.replace(/\b(apa|siapa|kapan|di mana|kenapa|mengapa|bagaimana)\b(.*?)(ya|tidak)?$/gi, function(match) {
            return match.trim().replace(/\.$/, '') + '?';
        });

        // Tambahkan titik jika belum ada tanda akhir
        if (!/[.!?]$/.test(text)) {
            text += '.';
        }

        return text.replace(/\s{2,}/g, ' ');
    }

    // Auto Correct (opsional)
    function autoCorrect(text) {
        const corrections = {
            'jerok': 'jeruk',
            'tehnik': 'teknik',
            'ngga': 'tidak',
            'gak': 'tidak',
            'udh': 'sudah'
            // Tambahkan sesuai kebutuhan
        };

        for (let wrong in corrections) {
            let right = corrections[wrong];
            let regex = new RegExp('\\b' + wrong + '\\b', 'gi');
            text = text.replace(regex, right);
        }

        return text;
    }


    function upgrade() {
        start_button.style.visibility = 'hidden';
        showInfo('info_upgrade');
    }

    var two_line = /\n\n/g;
    var one_line = /\n/g;

    function linebreak(s) {
        return s.replace(two_line, '<p></p>').replace(one_line, '<br>');
    }

    var first_char = /\S/;
        function capitalize(s) {
        return s.replace(first_char, function(m) { return m.toUpperCase(); });
    }

    // function createEmail() {
    //     var n = final_transcript.indexOf('\n');
    //     if (n < 0 || n >= 80) {
    //         n = 40 + final_transcript.substring(40).indexOf(' ');
    //     }
    //     var subject = encodeURI(final_transcript.substring(0, n));
    //     var body = encodeURI(final_transcript.substring(n + 1));
    //     window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
    // }

    function downloadInnerHtml(filename = 'meeting.doc') {
        // var elHtml = document.getElementById(elId).innerHTML;
        var link = document.createElement('a');

        var n = final_transcript.indexOf('\n');
        if (n < 0 || n >= 80) {
            n = 40 + final_transcript.substring(40).indexOf(' ');
        }

        console.log(n)

        link.setAttribute('download', filename);   
        link.setAttribute('href', 'data:' + 'text/doc' + ';charset=utf-8,' + encodeURIComponent(final_transcript));
        link.click(); 
    }

    function copyButton() {
        if (recognizing) {
            recognizing = false;
            recognition.stop();
        }
        copy_button.style.display = 'none';
        copy_info.style.display = 'inline-block';
        showInfo('');
    }

    // function emailButton() {
    //     if (recognizing) {
    //         create_email = true;
    //         recognizing = false;
    //         recognition.stop();
    //     } else {
    //         createEmail();
    //     }
    //     email_button.style.display = 'none';
    //     email_info.style.display = 'inline-block';
    //     showInfo('');
    // }

    function docxButton() {
        if (recognizing) {
            // create_email = true;
            recognizing = false;
            recognition.stop();
        } else {
            downloadInnerHtml();
        }
        docx_button.style.display = 'none';
        docx_info.style.display = 'inline-block';
        showInfo('');
    }

    function startButton(event) {
        if (recognizing) {
            recognition.stop();
            return;
        }
        
        final_transcript = '';
        recognition.lang = select_dialect.value;
        recognition.start();

        ignore_onend = false;

        final_span.value = linebreak(user +' pada ' + datetime + ' :'+final_transcript);
        // interim_span.innerHTML = linebreak(user +' pada ' + datetime + ' :'+interim_transcript);

        start_img.src = '/storage/demo/mic-slash.gif';
        showInfo('info_allow');
        showButtons('none');
        start_timestamp = event.timeStamp;
    }

    function showInfo(s) {
        if (s) {
            for (var child = info.firstChild; child; child = child.nextSibling) {
            if (child.style) {
                child.style.display = child.id == s ? 'inline' : 'none';
            }
            }
            info.style.visibility = 'visible';
        } else {
            info.style.visibility = 'hidden';
        }
    }

    var current_style;

    function showButtons(style) {
        if (style == current_style) {
            return;
        }
        current_style = style;
        copy_button.style.display = style;
        // email_button.style.display = style;
        docx_button.style.display = style;
        copy_info.style.display = 'none';
        // email_info.style.display = 'none';
        docx_info.style.display = 'none';
    }
</script>
</x-layouts.app>
