@php
    $footerLoginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/');
@endphp

<section class="catalog-footer-shell" aria-label="Bantuan dan informasi YZA Automotive">
    <div class="catalog-footer-wrap">
        <section class="catalog-assistance" aria-labelledby="catalog-assistance-title">
            <div class="catalog-assistance-copy">
                <span class="catalog-footer-kicker">YZA ASSISTANCE</span>
                <h2 id="catalog-assistance-title">BUTUH BANTUAN MEMILIH MOBIL?</h2>
                <p>Tim YZA Automotive siap membantu Anda menemukan kendaraan yang sesuai dengan kebutuhan dan preferensi Anda.</p>
            </div>
            <a class="catalog-footer-cta" href="{{ url('/#kontak') }}">
                <span>HUBUNGI KAMI</span>
                <span aria-hidden="true">→</span>
            </a>
        </section>

        <footer class="catalog-footer">
            <div class="catalog-footer-accent" aria-hidden="true"></div>
            <div class="catalog-footer-grid">
                <div class="catalog-footer-brand">
                    <a class="catalog-footer-logo" href="{{ url('/') }}" aria-label="YZA Automotive">
                        <img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive">
                    </a>
                    <strong>YZA <em>AUTOMOTIVE</em></strong>
                    <span class="catalog-footer-tagline">DRIVE YOUR BETTER TOMORROW</span>
                    <p>Solusi kendaraan baru, bekas, performance, servis, dan trade-in dalam satu pengalaman otomotif.</p>
                    <a class="catalog-footer-contact" href="{{ yza_whatsapp_url() }}" target="_blank" rel="noopener">
                        <span aria-hidden="true">◌</span> WhatsApp YZA
                    </a>
                </div>

                <nav class="catalog-footer-links" aria-label="Katalog">
                    <h3>KATALOG</h3>
                    <a href="{{ route('mobil.index') }}">Semua Mobil</a>
                    <a href="{{ route('mobil.index', ['kondisi' => 'baru']) }}">Mobil Baru</a>
                    <a href="{{ route('mobil.index', ['kondisi' => 'bekas']) }}">Mobil Bekas</a>
                    <a href="{{ route('mobil.index', ['sport' => 1]) }}">Performance</a>
                    <a href="{{ url('/#janji-temu') }}">Test Drive</a>
                </nav>

                <nav class="catalog-footer-links" aria-label="Layanan">
                    <h3>LAYANAN</h3>
                    <a href="{{ url('/#servis') }}">Servis</a>
                    <a href="{{ url('/#jual-mobil') }}">Trade-In</a>
                    <a href="{{ url('/#jual-mobil') }}">Jual Mobil</a>
                    <a href="{{ url('/#janji-temu') }}">Janji Temu</a>
                </nav>

                <nav class="catalog-footer-links" aria-label="YZA Automotive">
                    <h3>YZA</h3>
                    <a href="{{ url('/#tentang') }}">Tentang Kami</a>
                    <a href="{{ url('/#kontak') }}">Kontak</a>
                    <a href="{{ url('/#yza-member') }}">YZA Member</a>
                    @guest
                        <a href="{{ $footerLoginUrl }}">Masuk</a>
                    @endguest
                </nav>
            </div>

            <div class="catalog-footer-bottom">
                <span>© {{ now()->year }} YZA Automotive. All rights reserved.</span>
                <a href="{{ yza_whatsapp_url() }}" target="_blank" rel="noopener">WhatsApp YZA&nbsp; →</a>
            </div>
        </footer>
    </div>
</section>
