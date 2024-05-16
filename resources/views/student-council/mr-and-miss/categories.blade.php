@extends('layouts.app')

@section('title')
    <a href="#!" class="breadcrumb">Választmány</a>
    <a href="{{ route('mr_and_miss.vote') }}" class="breadcrumb"
        style="cursor: pointer">Mr. és Miss Eötvös</a>
    <a href="#!" class="breadcrumb">Kategóriák</a>
@endsection

@section('student_council_module')
    active
@endsection

@section('content')
    @foreach ($errors as $error)
        <blockquote class="error">{{ $error }}</blockquote>
    @endforeach
    <form action="{{ route('mr_and_miss.categories.create') }}" method="post">
        @csrf
        <div class="card">
            <div class="card-content">
                <div class="row">
                    <x-input.select id="mr" :elements="['Mr.', 'Miss']" without-placeholder without-label  />
                    <x-input.text id="title" placeholder="Kategória" without-label  />
                </div>
                <x-input.button floating class="right" icon="add" />
            </div>
        </div>
    </form>
    <form action="{{ route('mr_and_miss.categories.edit') }}" method="post">
        @csrf
        <div class="card">
            <div class="card-content">
                <div class="row">
                    @foreach ($categories as $category)
                        <x-input.checkbox :id="$category->id" name="enabled_categories[]" :value="$category->id" :checked="$category->hidden == 0" :text="$category->title" />
                    @endforeach
                </div>
                <x-input.button floating class="right" icon="save" />
            </div>
        </div>
    </form>
@endsection
