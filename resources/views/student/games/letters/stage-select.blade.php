@extends('layouts.app')

@section('title', __('game.letters_stage_title'))

@section('content')
    <x-stage-select game="letters" topic="letters" lang="letters" bg="letters" :tagline="__('letters.tagline')"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
