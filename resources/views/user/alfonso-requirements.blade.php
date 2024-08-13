@php
    $b2 = array_keys(array_filter($user->educationalInformation->alfonsoRequirements(), fn ($level) => $level == "B2"));
    $c1 = array_keys(array_filter($user->educationalInformation->alfonsoRequirements(), fn ($level) => $level == "C1"));
    $enrollmentYear = $user->educationalInformation->year_of_acceptance;
@endphp

@if($evaluation ?? false)
<blockquote>
    <p>A fenti mezőket csak abban az esetben kell kitölteni, ha collegiumi nyelvórán vettél részt.</p>
    <p><b>Figyelem:</b> A nyelvi vagy tanári szakokon tanuló, honoráciort végző, senior vagy mesterszakos hallgatókra más feltételek vonatkozhatnak, mely esetben egyéni elbírálás szükséges. Ilyenkor töltsd ki a megjegyzés mezőt.</p>
    <p>Ha már elvégezted az előírt követelményeket, és nem folytatod a nyelvtanulást, akkor nem szükséges semmit kitölteni.</p>
    <p>Vedd figyelembe, hogy a követelmények pontos számolásához az összes, a collegista státusz megszerzése előtti nyelvvizsgákat is fel kell tölteni, és ellenőrizd a felvétel évét a tanulmányi adatoknál.</p>
</blockquote>
@endif

@if($user->educationalInformation->alfonsoExempted())
    <p>Mivel szenior vagy mesterszak alatt felvételt nyert hallgató vagy, nem kell részt venned az ALFONSÓ-programban.</p>
@else
    <p>Az <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok">
        ALFONSÓ program 2. § (4)</a>
        szerint {{ $enrollmentYear + 3 }} szeptemberéig kell elérned a B2 szintet a(z) {{ implode(", ", array_map(fn($k) => __('role.'.$k), $b2)) }} nyelvek egyikéből
        @if(count($c1) == 1)
        , vagy {{ $enrollmentYear + 2}} szeptemberéig kell elérned a C1 szintet a(z) {{ implode(", ", array_map(fn($k) => __('role.'.$k), $c1)) }} nyelvből
        @elseif(count($c1) > 1)
        , vagy {{ $enrollmentYear + 2}} szeptemberéig kell elérned a C1 szintet a(z) {{ implode(", ", array_map(fn($k) => __('role.'.$k), $c1)) }} nyelvek egyikéből
        @endif
        .
    </p>
    <p>

            A követelményeket
            @if($user->educationalInformation->alfonsoCompleted())
                <span class="green-text">teljesítetted</span>.
            @else
                @if($user->educationalInformation->alfonsoCanBeCompleted())
                    <span class="coli-text text-orange">még nem teljesítetted</span>.
                @else
                    <span class="red-text">nem teljesítetted</span>.
                @endif
            @endif
    </p>
@endif


