@extends('layouts.app')

@section('title', __('game.color_vision_stage_title'))

@section('content')
    <x-stage-select game="color-vision" topic="colors" lang="color_vision" bg="colors"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
