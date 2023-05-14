@php
    $b2 = array_keys(array_filter($user->educationalInformation->alfonsoRequirements(), fn ($level) => $level == "B2"));
    $c1 = array_keys(array_filter($user->educationalInformation->alfonsoRequirements(), fn ($level) => $level == "C1"));
    $enrollmentYear = $user->educationalInformation->year_of_acceptance;
@endphp

<p>Az <a href="https://eotvos.elte.hu/collegium/mukodes/szabalyzatok"
    style="text-decoration:underline">
    ALFONSÓ program</a>
    szerint {{ $enrollmentYear + 2}} szeptemberéig kell elérned a B2 szintet a(z) {{ implode(", ", array_map(fn($k) => __('role.'.$k), $b2)) }} nyelvek egyikéből.
    @if(count($c1) == 1)
    , vagy {{ $enrollmentYear + 3}} szeptemberéig kell elérned a C1 szintet a(z) {{ implode(", ", array_map(fn($k) => __('role.'.$k), $c1)) }} nyelvből.
    @elseif(count($c1) > 1)
    , vagy {{ $enrollmentYear + 3}} szeptemberéig kell elérned a C1 szintet a(z) {{ implode(", ", array_map(fn($k) => __('role.'.$k), $c1)) }} nyelvek egyikéből.
    @endif
</p>
<blockquote>
    Figyelem: A nyelvi szakokon tanuló hallgatókra más feltételek vonatkozhatnak, mely esetben egyéni elbírálás szükséges.
</blockquote>
<p>A követelményeket
    @if($user->educationalInformation->alfonsoCompleted())
        <span class="green-text"> teljesítetted</span>.
    @else
        <span class="coli-text text-orange"> még nem teljesítetted</span>.
    @endif
</p>



