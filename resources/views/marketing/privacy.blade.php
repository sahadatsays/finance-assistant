@extends('layouts.marketing')

@section('content')
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-heading">Privacy Policy</h1>
            <p class="mt-4 text-sm text-gray-500">Last updated: {{ date('F j, Y') }}</p>

            <div class="prose prose-gray mt-10 max-w-none space-y-8 text-gray-600">
                <section>
                    <h2 class="text-xl font-semibold text-heading">1. Introduction</h2>
                    <p>{{ config('app.name') }} (&ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our&rdquo;) operates a multi-tenant personal finance SaaS platform. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website and application.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">2. Information We Collect</h2>
                    <ul class="list-disc space-y-2 pl-5">
                        <li><strong>Account data:</strong> name, email address, password (hashed), and profile preferences.</li>
                        <li><strong>Financial data:</strong> accounts, transactions, budgets, goals, and attachments you enter.</li>
                        <li><strong>Usage data:</strong> log data, device information, IP address, and feature usage.</li>
                        <li><strong>Cookies:</strong> session and preference cookies for authentication and site functionality.</li>
                    </ul>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">3. How We Use Your Information</h2>
                    <p>We use collected information to provide and improve the service, authenticate users, process subscriptions, send transactional communications, ensure security, and comply with legal obligations.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">4. Data Security</h2>
                    <p>We implement encryption in transit, tenant-isolated data storage, access controls, and audit logging. No method of transmission over the Internet is 100% secure, but we strive to protect your data using industry-standard practices.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">5. Your Rights</h2>
                    <p>Depending on your jurisdiction, you may have the right to access, correct, delete, or export your personal data. Contact us to exercise these rights.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">6. Contact</h2>
                    <p>Questions about this policy? <a href="{{ route('marketing.contact') }}" class="text-brand hover:text-brand-dark">Contact us</a>.</p>
                </section>
            </div>
        </div>
    </section>
@endsection
