@extends('layouts.app')

@section('title', __('game.colors_stage_title'))

@section('content')
    <x-stage-select game="colors" topic="colors" lang="colors" bg="colors"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
