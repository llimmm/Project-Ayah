@props(['jenis' => 'pasar'])
{{-- Ilustrasi bergaya clay (gradasi lembut + bayangan), digambar sendiri: bukit Sumbawa dan bahan pokok. --}}
@if ($jenis === 'pasar')
<svg viewBox="0 0 600 552" role="img" aria-label="Ilustrasi bukit dan bahan pokok di pasar">
    <defs>
        <radialGradient id="cl-bukit1" cx="35%" cy="25%" r="85%"><stop offset="0" stop-color="#c8e6da"/><stop offset=".6" stop-color="#a4d4c5"/><stop offset="1" stop-color="#78b3a1"/></radialGradient>
        <radialGradient id="cl-bukit2" cx="40%" cy="20%" r="90%"><stop offset="0" stop-color="#ffd0b0"/><stop offset=".6" stop-color="#ffb084"/><stop offset="1" stop-color="#e8875a"/></radialGradient>
        <radialGradient id="cl-bukit3" cx="45%" cy="20%" r="90%"><stop offset="0" stop-color="#f6d58a"/><stop offset=".6" stop-color="#e8b94a"/><stop offset="1" stop-color="#c4932a"/></radialGradient>
        <radialGradient id="cl-matahari" cx="38%" cy="32%" r="70%"><stop offset="0" stop-color="#fff3c9"/><stop offset=".5" stop-color="#ffd166"/><stop offset="1" stop-color="#f0a83a"/></radialGradient>
        <radialGradient id="cl-cabai" cx="35%" cy="25%" r="80%"><stop offset="0" stop-color="#ff8a7c"/><stop offset=".55" stop-color="#ff4d4d"/><stop offset="1" stop-color="#c0262d"/></radialGradient>
        <radialGradient id="cl-bawang" cx="35%" cy="30%" r="80%"><stop offset="0" stop-color="#e9c2f0"/><stop offset=".55" stop-color="#c58ad6"/><stop offset="1" stop-color="#8f569f"/></radialGradient>
        <radialGradient id="cl-telur" cx="35%" cy="25%" r="80%"><stop offset="0" stop-color="#fffdf6"/><stop offset=".6" stop-color="#f6e6c8"/><stop offset="1" stop-color="#d8bf95"/></radialGradient>
        <radialGradient id="cl-karung" cx="35%" cy="25%" r="85%"><stop offset="0" stop-color="#fbf1dd"/><stop offset=".6" stop-color="#ead8b5"/><stop offset="1" stop-color="#c9b083"/></radialGradient>
        <radialGradient id="cl-daun" cx="35%" cy="30%" r="80%"><stop offset="0" stop-color="#8fd19e"/><stop offset="1" stop-color="#3f8f52"/></radialGradient>
        <filter id="cl-bayang" x="-20%" y="-20%" width="140%" height="160%"><feDropShadow dx="0" dy="10" stdDeviation="10" flood-color="#5a3b16" flood-opacity=".18"/></filter>
    </defs>
    <circle cx="455" cy="130" r="62" fill="url(#cl-matahari)" filter="url(#cl-bayang)"/>
    <path d="M-20 380 C 80 230, 190 230, 290 330 S 470 250, 620 330 L 620 560 L -20 560 Z" fill="url(#cl-bukit1)"/>
    <path d="M-20 430 C 110 330, 230 360, 330 420 S 520 360, 620 400 L 620 560 L -20 560 Z" fill="url(#cl-bukit2)"/>
    <path d="M-20 480 C 140 430, 300 450, 420 470 S 560 455, 620 470 L 620 560 L -20 560 Z" fill="url(#cl-bukit3)"/>
    {{-- karung beras --}}
    <g filter="url(#cl-bayang)">
        <path d="M120 470 C 104 400, 118 330, 150 312 L 230 312 C 262 330, 276 400, 260 470 C 230 492, 150 492, 120 470 Z" fill="url(#cl-karung)"/>
        <path d="M150 312 C 160 292, 220 292, 230 312 C 214 322, 166 322, 150 312 Z" fill="#d9c294"/>
        <ellipse cx="190" cy="300" rx="34" ry="12" fill="#f4ead6"/>
        <rect x="160" y="378" width="60" height="34" rx="10" fill="#ffffff" opacity=".75"/>
        <text x="190" y="401" text-anchor="middle" font-family="Inter, sans-serif" font-size="16" font-weight="600" fill="#1a3a3a">BERAS</text>
    </g>
    {{-- telur --}}
    <g filter="url(#cl-bayang)">
        <ellipse cx="308" cy="452" rx="30" ry="38" fill="url(#cl-telur)"/>
        <ellipse cx="352" cy="462" rx="27" ry="34" fill="url(#cl-telur)"/>
    </g>
    {{-- bawang merah --}}
    <g filter="url(#cl-bayang)">
        <path d="M410 470 C 372 466, 368 412, 410 392 C 452 412, 448 466, 410 470 Z" fill="url(#cl-bawang)"/>
        <path d="M410 392 C 406 376, 412 364, 420 356" stroke="#7a9b3e" stroke-width="6" fill="none" stroke-linecap="round"/>
        <path d="M460 474 C 432 470, 430 432, 460 418 C 490 432, 488 470, 460 474 Z" fill="url(#cl-bawang)"/>
    </g>
    {{-- cabai --}}
    <g filter="url(#cl-bayang)">
        <path d="M478 430 C 500 380, 540 360, 566 372 C 548 384, 526 420, 512 462 C 504 480, 470 466, 478 430 Z" fill="url(#cl-cabai)"/>
        <path d="M560 370 C 566 356, 578 348, 590 346" stroke="url(#cl-daun)" stroke-width="8" fill="none" stroke-linecap="round"/>
        <ellipse cx="562" cy="372" rx="12" ry="8" fill="url(#cl-daun)"/>
    </g>
    {{-- awan --}}
    <g opacity=".95" filter="url(#cl-bayang)">
        <ellipse cx="140" cy="120" rx="58" ry="26" fill="#fffaf0"/>
        <ellipse cx="180" cy="104" rx="38" ry="28" fill="#fffaf0"/>
        <ellipse cx="110" cy="110" rx="28" ry="20" fill="#fffaf0"/>
    </g>
</svg>
@elseif ($jenis === 'buku')
<svg viewBox="0 0 260 220" role="img" aria-label="Ilustrasi buku panduan">
    <defs>
        <radialGradient id="cb-sampul" cx="30%" cy="25%" r="90%"><stop offset="0" stop-color="#d4c7f6"/><stop offset=".6" stop-color="#b8a4ed"/><stop offset="1" stop-color="#8c75cf"/></radialGradient>
        <radialGradient id="cb-kaca" cx="35%" cy="30%" r="80%"><stop offset="0" stop-color="#ffd8bf"/><stop offset="1" stop-color="#ff9a63"/></radialGradient>
        <filter id="cb-bayang" x="-20%" y="-20%" width="140%" height="160%"><feDropShadow dx="0" dy="10" stdDeviation="9" flood-color="#5a3b16" flood-opacity=".18"/></filter>
    </defs>
    <g filter="url(#cb-bayang)">
        <rect x="40" y="40" width="150" height="160" rx="18" fill="url(#cb-sampul)" transform="rotate(-6 115 120)"/>
        <rect x="60" y="62" width="100" height="10" rx="5" fill="#fffaf0" opacity=".85" transform="rotate(-6 115 120)"/>
        <rect x="60" y="82" width="70" height="10" rx="5" fill="#fffaf0" opacity=".6" transform="rotate(-6 115 120)"/>
        <path d="M70 160 L 95 130 L 115 145 L 150 105" stroke="#1a3a3a" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" transform="rotate(-6 115 120)"/>
    </g>
    <g filter="url(#cb-bayang)">
        <circle cx="190" cy="140" r="38" fill="url(#cb-kaca)"/>
        <circle cx="190" cy="140" r="22" fill="#fffaf0" opacity=".9"/>
        <rect x="214" y="168" width="40" height="16" rx="8" fill="#1a3a3a" transform="rotate(45 220 170)"/>
    </g>
</svg>
@elseif ($jenis === 'bukit')
<svg viewBox="0 0 1440 180" preserveAspectRatio="none" class="bukit" aria-hidden="true">
    <defs>
        <linearGradient id="ck-1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#bfe1d5"/><stop offset="1" stop-color="#8fc3b2"/></linearGradient>
        <linearGradient id="ck-2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffc9a6"/><stop offset="1" stop-color="#f3995f"/></linearGradient>
        <linearGradient id="ck-3" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f1cd73"/><stop offset="1" stop-color="#d9a637"/></linearGradient>
    </defs>
    <path d="M0 90 C 180 20, 360 30, 520 80 S 860 10, 1040 60 S 1320 30, 1440 70 L 1440 180 L 0 180 Z" fill="url(#ck-1)"/>
    <path d="M0 130 C 220 80, 420 90, 640 120 S 1040 70, 1240 110 S 1400 100, 1440 110 L 1440 180 L 0 180 Z" fill="url(#ck-2)"/>
    <path d="M0 160 C 260 130, 560 140, 820 155 S 1260 135, 1440 150 L 1440 180 L 0 180 Z" fill="url(#ck-3)"/>
</svg>
@elseif ($jenis === 'logo')
<svg viewBox="0 0 40 40" aria-hidden="true">
    <defs><radialGradient id="cl-logo" cx="35%" cy="30%" r="80%"><stop offset="0" stop-color="#ff8ab2"/><stop offset=".6" stop-color="#ff4d8b"/><stop offset="1" stop-color="#d42e68"/></radialGradient></defs>
    <rect width="40" height="40" rx="12" fill="#1a3a3a"/>
    <path d="M9 27 L 16 19 L 21 23 L 31 12" stroke="url(#cl-logo)" stroke-width="4.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
    <circle cx="31" cy="12" r="3.4" fill="#e8b94a"/>
</svg>
@endif
