@php
    $parseKecepatan = function ($namaPaket) {
        return preg_match('/(\d+)/', (string) $namaPaket, $match) ? (int) $match[1] : null;
    };
    $formatHargaPaket = function ($harga) {
        $formatted = number_format((int) $harga, 0, ',', '.');
        $parts = explode('.', $formatted, 2);

        return [
            'utama' => $parts[0],
            'ribuan' => isset($parts[1]) ? '.' . $parts[1] : '',
        ];
    };
@endphp

<div class="paket-card-list">
    @forelse($paketList as $index => $itemPaket)
        @php
            $kecepatan = $parseKecepatan($itemPaket->nama_paket);
            $hargaPaket = $formatHargaPaket($itemPaket->harga);
        @endphp
        <article class="paket-card">
            <div class="paket-card-header">
                <h4 class="paket-card-title">{{ $itemPaket->nama_paket }}</h4>
                <div class="paket-card-divider" aria-hidden="true"></div>
                <div class="paket-card-speed">
                    <strong>{{ $kecepatan ?? '-' }}</strong>
                    <span>Mbps</span>
                </div>
            </div>
            <div class="paket-card-body">
                <ul class="paket-feature-list">
                    <li>
                        <span class="paket-feature-icon">✓</span>
                        <span>Kecepatan unggah hingga {{ $kecepatan ?? '-' }} Mbps</span>
                    </li>
                    <li>
                        <span class="paket-feature-icon">✓</span>
                        <span>Kecepatan unduh hingga {{ $kecepatan ?? '-' }} Mbps</span>
                    </li>
                    <li>
                        <span class="paket-feature-icon">✓</span>
                        <span>Kuota tidak terbatas</span>
                    </li>
                </ul>
            </div>
            <div class="paket-card-footer">
                <div class="paket-price">
                    <span class="paket-price-currency">Rp</span>
                    <span class="paket-price-main">{{ $hargaPaket['utama'] }}</span>
                    <span class="paket-price-suffix">
                        <span>{{ $hargaPaket['ribuan'] }}</span>
                        <span>/bulan</span>
                    </span>
                </div>
            </div>
        </article>
    @empty
        <div class="paket-card-empty">
            {{ $emptyMessage ?? 'Belum ada paket upgrade yang tersedia.' }}
        </div>
    @endforelse
</div>
