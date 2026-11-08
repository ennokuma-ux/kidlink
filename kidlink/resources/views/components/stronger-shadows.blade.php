{{--
    Stronger card shadows, shared by every layout.

    The page background is a light blue-grey, so Tailwind's default shadows
    (about 5% black) vanish and white cards blend into it. These tint the shadow
    with the app's navy and add a second, wider layer so cards visibly lift off
    the page. Overriding the theme keeps hover:shadow-* and friends working.

    Must be included right after the Tailwind script in <head>.
--}}
<script>
    tailwind.config = {
        theme: {
            extend: {
                boxShadow: {
                    sm: '0 1px 3px rgba(24,36,56,.18), 0 4px 12px rgba(24,36,56,.14)',
                    DEFAULT: '0 2px 5px rgba(24,36,56,.18), 0 6px 16px rgba(24,36,56,.16)',
                    md: '0 3px 8px rgba(24,36,56,.20), 0 10px 22px rgba(24,36,56,.16)',
                    lg: '0 6px 14px rgba(24,36,56,.22), 0 16px 32px rgba(24,36,56,.18)',
                    xl: '0 10px 20px rgba(24,36,56,.24), 0 24px 44px rgba(24,36,56,.20)',
                },
            },
        },
    };
</script>
