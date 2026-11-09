@extends('layouts.app')

@section('title', __('game.animals_stage_title'))

@section('content')
    <x-stage-select game="animals" topic="animals" lang="animals" bg="animals"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
