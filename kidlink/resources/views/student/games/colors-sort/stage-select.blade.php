@extends('layouts.app')

@section('title', __('game.color_sort_stage_title'))

@section('content')
    <x-stage-select game="colors-sort" topic="colors" lang="color_sort" bg="colors"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
