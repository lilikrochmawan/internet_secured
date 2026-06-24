<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan</title>
    <style>
        :root {
            color-scheme: dark;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, rgba(79, 70, 229, .22), transparent 25%),
                        radial-gradient(circle at right, rgba(59, 130, 246, .14), transparent 15%),
                        linear-gradient(180deg, #0b1124 0%, #090d1d 100%);
            color: #e5e7eb;
        }
        .page {
            width: min(720px, 100%);
            margin: 0 auto;
            padding: 24px;
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }
        .topbar a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 600;
        }
        .topbar h1 {
            margin: 0;
            font-size: 1.35rem;
        }
        .card {
            background: rgba(15, 23, 42, .72);
            border: 1px solid rgba(148, 163, 184, .12);
            border-radius: 28px;
            padding: 28px;
            backdrop-filter: blur(16px);
        }
        .subtitle {
            margin: 0 0 24px;
            color: #94a3b8;
            font-size: .95rem;
            line-height: 1.5;
        }
        .field {
            display: grid;
            gap: 10px;
            margin-bottom: 20px;
        }
        .field label {
            font-size: .9rem;
            font-weight: 600;
            color: #e2e8f0;
        }
        .field input[type="text"],
        .field textarea {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, .2);
            border-radius: 16px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, .06);
            color: #f8fafc;
            font: inherit;
        }
        .field textarea {
            min-height: 140px;
            resize: vertical;
        }
        .field input:focus,
        .field textarea:focus {
            outline: none;
            border-color: rgba(129, 140, 248, .65);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .18);
        }
        .field input[type="file"] {
            width: 100%;
            border: 1px dashed rgba(148, 163, 184, .35);
            border-radius: 16px;
            padding: 16px;
            background: rgba(255, 255, 255, .04);
            color: #cbd5e1;
        }
        .hint {
            color: #94a3b8;
            font-size: .85rem;
        }
        .error-list {
            margin: 0 0 20px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(220, 38, 38, .12);
            border: 1px solid rgba(248, 113, 113, .35);
            color: #fecaca;
            list-style: none;
        }
        .error-list li + li {
            margin-top: 6px;
        }
        .submit-button {
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 16px 20px;
            background: linear-gradient(90deg, #6366f1, #7c3aed);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }
        .submit-button:hover {
            filter: brightness(1.05);
        }
        @media (max-width: 600px) {
            .page {
                padding: 16px 12px;
            }
            .topbar h1 {
                font-size: 1.15rem;
            }
            .card {
                padding: 20px 16px;
                border-radius: 20px;
            }
            .subtitle {
                font-size: 0.88rem;
                margin-bottom: 20px;
            }
            .field label {
                font-size: 0.85rem;
            }
            .field input[type="text"],
            .field textarea,
            .field input[type="file"] {
                padding: 12px 14px;
                border-radius: 12px;
                font-size: 0.95rem;
            }
            .submit-button {
                padding: 14px 18px;
                border-radius: 12px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <h1>Buat Laporan</h1>
            <a href="{{ route('dashboard') }}">← Kembali</a>
        </div>

        <div class="card">
            <p class="subtitle">Isi formulir di bawah untuk mengirim laporan keluhan. Tim kami akan menindaklanjuti secepatnya.</p>

            @if ($errors->any())
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('keluhan.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="field">
                    <label for="judul_keluhan">Keluhan</label>
                    <input
                        type="text"
                        id="judul_keluhan"
                        name="judul_keluhan"
                        value="{{ old('judul_keluhan') }}"
                        maxlength="50"
                        placeholder="Contoh: Internet lambat"
                        required
                    >
                    <span class="hint">Maksimal 50 karakter.</span>
                </div>

                <div class="field">
                    <label for="isi_keluhan">Detail Keluhan</label>
                    <textarea
                        id="isi_keluhan"
                        name="isi_keluhan"
                        placeholder="Jelaskan detail permasalahan yang Anda alami"
                        required
                    >{{ old('isi_keluhan') }}</textarea>
                </div>

                <div class="field">
                    <label for="gambar">Upload Gambar</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <span class="hint">Opsional. Format JPG, PNG, GIF, atau WEBP (maks. 5 MB).</span>
                </div>

                <button type="submit" class="submit-button">Submit Laporan</button>
            </form>
        </div>
        @include('partials.bottom-nav')
    </div>
</body>
</html>
