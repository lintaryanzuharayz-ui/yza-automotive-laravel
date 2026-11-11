<footer class="show-footer">
    <div class="show-container footer-grid">
        <div class="footer-brand" id="tentang">
            <a class="footer-brand-link" href="#beranda"><img class="footer-logo" src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive"><strong>YZA <em>Automotive</em></strong></a>
            <p>Pilihan kendaraan dan layanan otomotif dalam satu pengalaman bersama YZA.</p>
            <button class="landing-intro-open" type="button" data-intro-replay>Lihat pembuka YZA <span aria-hidden="true">↗</span></button>
        </div>
        <nav aria-label="Menu footer"><h3>Jelajahi YZA</h3><a href="#mobil">Koleksi Mobil</a><a href="#layanan">Layanan Otomotif</a><a href="#jual-mobil">Jual &amp; Tukar Tambah</a><a href="#test-drive">Test Drive</a></nav>
        <nav aria-label="Member dan kunjungan"><h3>Pengalaman YZA</h3><a href="#yza-member">Benefit Membership</a><a href="{{ $memberPrimaryHref }}">{{ $memberPrimaryLabel }}</a><a href="#testimoni">Ulasan Pelanggan</a><a href="#janji-temu">Jadwalkan Kunjungan</a></nav>
        <div class="footer-contact"><h3>Hubungi Kami</h3><a href="{{ yza_whatsapp_url() }}" target="_blank" rel="noopener">{{ $contactPhone }} <span aria-hidden="true">↗</span></a>@if($contactEmail)<a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>@endif<p>{{ $contactAddress ?: 'Lokasi dan jadwal kunjungan dikonfirmasi bersama tim YZA.' }}</p>@if($contactAddress)<a href="{{ $contactMapUrl }}" target="_blank" rel="noopener">Buka lokasi di Maps ↗</a>@endif</div>
    </div>
    <div class="show-container footer-bottom"><span>© {{ date('Y') }} YZA Automotive. Semua Hak Dilindungi.</span><a href="#beranda">Kembali ke atas ↑</a></div>
</footer>
