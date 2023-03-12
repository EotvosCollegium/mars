<table>
    <tbody>
        @if(!$semesters->contains(\App\Models\Semester::current()))
            @livewire('edit-status', ['user' => $user, 'semester' => \App\Models\Semester::current()])
        @endif
        @foreach ($semesters->sortByDesc('tag') as $semester)
            @livewire('edit-status', ['status' => $user->getStatus($semester)->status, 'comment' => $user->getStatus($semester)->comment, 'user' => $user, 'semester' => $semester])
        @endforeach
    </tbody>
</table>
@once
    @push('scripts')
        <script>
            $(document).ready(function(){
                $('.tooltipped').tooltip();
            });
        </script>
    @endpush
@endonce