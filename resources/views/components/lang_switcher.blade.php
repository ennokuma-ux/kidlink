@props([])

{{--
    Language picker, top right of every authenticated layout.

    One form with one submit button each rather than links, because choosing a
    language writes session state. The chosen one is marked with aria-current
    and the dark pill so it reads as selected without relying on colour alone.
    Labels stay in their own language: "Filipino" must not turn into something
    else when Filipino is selected.
--}}
<form method="POST" action="{{ route('language.switch') }}" class="inline shrink-0">
    @csrf
    <div class="inline-flex items-center gap-0.5 rounded-lg border border-[#f1e7cc] bg-[#fffbf0] p-0.5"
         role="group" aria-label="{{ __('common.language') }}">
        <svg class="w-4 h-4 ml-1.5 text-[#8b949d]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
        </svg>
        @foreach (config('app.supported_locales') as $code => $label)
            @php($active = app()->getLocale() === $code)
            <button type="submit"
                    name="locale"
                    value="{{ $code }}"
                    title="{{ $label }}"
                    aria-label="{{ $label }}"
                    aria-current="{{ $active ? 'true' : 'false' }}"
                    class="px-2.5 py-1.5 text-xs font-semibold rounded-md transition {{ $active
                        ? 'bg-[#182438] text-white shadow-sm'
                        : 'text-[#5f6873] hover:bg-white hover:text-[#182438]' }}">
                {{ strtoupper($code) }}
            </button>
        @endforeach
    </div>
</form>
