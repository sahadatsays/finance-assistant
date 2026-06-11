@extends('layouts.marketing')

@section('content')
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-heading">Terms of Service</h1>
            <p class="mt-4 text-sm text-gray-500">Last updated: {{ date('F j, Y') }}</p>

            <div class="prose prose-gray mt-10 max-w-none space-y-8 text-gray-600">
                <section>
                    <h2 class="text-xl font-semibold text-heading">1. Agreement to Terms</h2>
                    <p>By accessing or using {{ config('app.name') }}, you agree to be bound by these Terms of Service. If you do not agree, do not use the service.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">2. Service Description</h2>
                    <p>{{ config('app.name') }} provides personal and team finance management tools including account tracking, transactions, budgets, savings goals, reports, and related features on a subscription basis.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">3. Accounts</h2>
                    <p>You are responsible for maintaining the confidentiality of your account credentials and for all activity under your account. You must provide accurate registration information.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">4. Subscriptions &amp; Billing</h2>
                    <p>Paid plans are billed monthly according to the pricing displayed at the time of purchase. You may upgrade, downgrade, or cancel at any time subject to the terms of your selected plan.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">5. Acceptable Use</h2>
                    <p>You agree not to misuse the service, attempt unauthorized access, interfere with other users, or use the platform for unlawful purposes.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">6. Not Financial Advice</h2>
                    <p>{{ config('app.name') }} is a financial management tool only. We do not provide investment, tax, or legal advice. Consult qualified professionals for financial decisions.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">7. Limitation of Liability</h2>
                    <p>To the maximum extent permitted by law, {{ config('app.name') }} shall not be liable for indirect, incidental, or consequential damages arising from your use of the service.</p>
                </section>
                <section>
                    <h2 class="text-xl font-semibold text-heading">8. Contact</h2>
                    <p>Questions about these terms? <a href="{{ route('marketing.contact') }}" class="text-brand hover:text-brand-dark">Contact us</a>.</p>
                </section>
            </div>
        </div>
    </section>
@endsection
