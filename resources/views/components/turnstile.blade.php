@props([
    'resetEvent' => null,
])

@if (config('services.turnstile.site_key'))
    <div
        x-data="{ widgetId: null }"
        x-init="
            let sitekey = '{{ config('services.turnstile.site_key') }}';
            let widget = $refs.turnstileWidget;
            let wire = typeof $wire !== 'undefined' ? $wire : null;

            function renderWidget() {
                if (widgetId !== null) turnstile.remove(widgetId);
                widgetId = turnstile.render(widget, {
                    sitekey: sitekey,
                    appearance: 'interaction-only',
                    callback: function(token) { if (wire) wire.set('captchaToken', token); },
                    'expired-callback': function() { if (wire) wire.set('captchaToken', ''); },
                    'timeout-callback': function() { if (wire) wire.set('captchaToken', ''); },
                });
            }

            if (typeof turnstile !== 'undefined') {
                renderWidget();
            } else if (!document.querySelector('script[src*=&quot;challenges.cloudflare.com/turnstile&quot;]')) {
                let script = document.createElement('script');
                script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileLoad';
                window.onTurnstileLoad = function() { renderWidget(); };
                document.head.appendChild(script);
            } else {
                let poll = setInterval(function() {
                    if (typeof turnstile !== 'undefined') { clearInterval(poll); renderWidget(); }
                }, 100);
            }

            @if ($resetEvent)
                if (wire) {
                    wire.on('{{ $resetEvent }}', () => {
                        if (widgetId !== null && typeof turnstile !== 'undefined') {
                            turnstile.reset(widgetId);
                        }
                    });
                }
            @endif
        "
    >
        <div x-ref="turnstileWidget"></div>
    </div>
@else
    @unless (isset($__livewire))
        <input type="hidden" name="cf-turnstile-response" value="no-turnstile">
    @endunless
@endif
