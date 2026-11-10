<x-error-layout
    title="This link can't be opened directly | KidLink"
    tag="Can't open this way"
    heading="This address can't be opened directly"
    homeLabel="Back to home"
    homeClass="btn-quiet"
    :footnote="isset($reference) ? 'Reference: '.$reference.'. If this keeps happening, give this code to your administrator.' : null">
    <p>
        The address you opened is used by KidLink behind the scenes (for example to keep your session
        active) and is not a page you can visit. Nothing is wrong with your account or your data.
    </p>

    <h2>What you can do</h2>
    <ol>
        <li>Go back to the page you were on and refresh it.</li>
        <li>If you were redirected here after signing in, sign in again from the home page.</li>
    </ol>

    <x-slot name="actions">
        <a class="btn btn-primary" href="{{ url('/') }}" onclick="if (window.history.length > 1) { window.history.back(); return false; }">Go back</a>
    </x-slot>
</x-error-layout>
