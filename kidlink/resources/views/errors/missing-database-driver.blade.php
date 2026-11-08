<x-error-layout
    :title="__('errors.db_missing_title')"
    :tag="__('errors.config_problem')"
    :heading="__('errors.db_h1_missing_ext', ['ext' => $exception->extensionName()])"
    :home-class="'btn-quiet'"
>
    <p>
        {!! __('errors.db_config_used', [
            'conn' => '<code>'.$exception->connection.'</code>',
            'ext' => '<code>'.$exception->extensionName().'</code>',
        ]) !!}
        {{ __('errors.db_queries_fail') }}
    </p>

    <h2>{{ __('errors.currently_running') }}</h2>
    <pre>{{ $exception->runningBinary() }}
PHP {{ PHP_VERSION }} &#183; PDO drivers: {{ implode(', ', $availableDrivers) ?: __('errors.none') }}</pre>

    <h2>{{ __('errors.how_to_fix') }}</h2>
    <ol>
        <li>
            {!! __('errors.db_fix_launcher') !!}
            <pre>serve.cmd</pre>
        </li>
        @if($suggested)
            <li>
                {{ __('errors.db_fix_pick_binary', [
                    'ext' => '<code>'.$exception->extensionName().'</code>',
                    'version' => $suggested['version'],
                ]) }}
                <pre>"{{ $suggested['path'] }}" artisan serve --port=8000</pre>
            </li>
        @endif
        <li>
            {{ __('errors.db_fix_check_runtime') }}
            <pre>php artisan env:doctor</pre>
        </li>
    </ol>

    <p class="muted">
        {!! __('errors.db_static_note', ['ext' => '<code>'.$exception->extensionName().'</code>']) !!}
    </p>

    <p class="muted">
        {!! __('errors.db_skip_flag_note') !!}
    </p>
</x-error-layout>