<section class="visit-section" id="kontak" aria-labelledby="visit-title">
    <div class="show-container visit-layout">
        <div class="visit-copy">
            <span class="section-kicker">Kunjungi YZA</span>
            <h2 id="visit-title">Kenali mobilnya.<br><em>Temui tim kami.</em></h2>
            <p>Lihat kendaraan pilihan Anda dan diskusikan kebutuhan otomotif bersama tim YZA.</p>
            <dl class="visit-details">
                <div><dt><x-landing-icon name="pin" /> Lokasi kunjungan</dt><dd>{{ $contactAddress ?: 'Lokasi kunjungan dikonfirmasi bersama tim YZA.' }}</dd></div>
                <div><dt><x-landing-icon name="clock" /> Jadwal kunjungan</dt><dd>Sesuai janji temu. Hubungi tim untuk konfirmasi waktu.</dd></div>
                <div><dt><x-landing-icon name="phone" /> WhatsApp YZA</dt><dd><a href="{{ yza_whatsapp_url() }}" target="_blank" rel="noopener">{{ $contactPhone }}</a></dd></div>
            </dl>
            <div class="visit-actions">
                <a class="show-ghost" href="{{ yza_whatsapp_url('Halo YZA Automotive, saya ingin mengetahui lokasi dan jadwal kunjungan showroom.') }}" target="_blank" rel="noopener">Hubungi Tim YZA <span aria-hidden="true">↗</span></a>
                @if($contactAddress)<a class="visit-map-link" href="{{ $contactMapUrl }}" target="_blank" rel="noopener">Buka lokasi di Maps ↗</a>@endif
            </div>
        </div>
        <div class="appointment-wrap" id="janji-temu">
            <form class="appointment-form" id="appointment-form">
                <div class="appointment-form-heading"><div><span class="section-kicker">Janji temu</span><h3>Jadwalkan Kunjungan Anda</h3><p>Isi rencana kunjungan untuk dilanjutkan ke WhatsApp.</p></div></div>
                <div class="appointment-fields">
                    <div class="form-group"><label for="appointment-nama">Nama Lengkap</label><input class="form-control" id="appointment-nama" name="nama" autocomplete="name" required placeholder="Nama lengkap Anda"></div>
                    <div class="form-group"><label for="appointment-hp">Nomor WhatsApp</label><input class="form-control" id="appointment-hp" name="no_hp" type="tel" autocomplete="tel" required placeholder="08xxxxxxxxxx"></div>
                    <div class="form-group full"><label for="appointment-layanan">Jenis Kebutuhan</label><select class="form-control" id="appointment-layanan" name="layanan" required><option value="">Pilih kebutuhan Anda</option>@foreach(['Konsultasi Mobil','Test Drive','Service','Spare Part','Car Wash','Used Car','Trade-In','Lainnya'] as $service)<option value="{{ $service }}">{{ $service }}</option>@endforeach</select></div>
                    <div class="form-group"><label for="appointment-tanggal">Tanggal Janji</label><input class="form-control" id="appointment-tanggal" name="tanggal" type="date" min="{{ now()->format('Y-m-d') }}" required></div>
                    <div class="form-group"><label for="appointment-jam">Jam Janji</label><input class="form-control" id="appointment-jam" name="jam" type="time" required></div>
                    <div class="form-group full"><label for="appointment-catatan">Catatan Tambahan <span>(opsional)</span></label><textarea class="form-control" id="appointment-catatan" name="catatan" rows="2" placeholder="Contoh: kendaraan yang ingin dilihat atau kebutuhan servis."></textarea></div>
                </div>
                <button class="show-cta appointment-submit" type="submit">Lanjut ke WhatsApp <span aria-hidden="true">↗</span></button>
                <p class="appointment-microcopy">Jadwal dikonfirmasi oleh tim YZA. Pesan tidak dikirim otomatis.</p>
            </form>
        </div>
    </div>
</section>
