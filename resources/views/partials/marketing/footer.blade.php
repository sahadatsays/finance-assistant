<footer class="border-t border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <h3 class="text-sm font-semibold text-heading">Product</h3>
                <ul class="mt-4 space-y-3">
                    <li><a href="{{ route('marketing.features') }}" class="text-sm text-gray-600 hover:text-brand">Features</a></li>
                    <li><a href="{{ route('marketing.pricing') }}" class="text-sm text-gray-600 hover:text-brand">Pricing</a></li>
                    <li><a href="{{ route('register') }}" class="text-sm font-medium text-brand hover:text-brand-dark">Start Free</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-heading">Resources</h3>
                <ul class="mt-4 space-y-3">
                    <li><a href="{{ route('marketing.blog') }}" class="text-sm text-gray-600 hover:text-brand">Blog</a></li>
                    <li><a href="{{ route('marketing.help') }}" class="text-sm text-gray-600 hover:text-brand">Help Center</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-heading">Company</h3>
                <ul class="mt-4 space-y-3">
                    <li><a href="{{ route('marketing.about') }}" class="text-sm text-gray-600 hover:text-brand">About</a></li>
                    <li><a href="{{ route('marketing.contact') }}" class="text-sm text-gray-600 hover:text-brand">Contact</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-heading">Legal</h3>
                <ul class="mt-4 space-y-3">
                    <li><a href="{{ route('marketing.privacy') }}" class="text-sm text-gray-600 hover:text-brand">Privacy Policy</a></li>
                    <li><a href="{{ route('marketing.terms') }}" class="text-sm text-gray-600 hover:text-brand">Terms of Service</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-8 sm:flex-row">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} {{ config('app.name') }}. Built for individuals, households &amp; teams.</p>
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>Secure</span>
                <span>&middot;</span>
                <span>Multi-tenant</span>
                <span>&middot;</span>
                <span>GDPR-ready</span>
            </div>
        </div>
    </div>
</footer>
