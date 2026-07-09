<?php
require_once __DIR__ . '/check_isolir.php';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Diisolir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            box-sizing: border-box;
        }

        * {
            font-family: "Plus Jakarta Sans", sans-serif;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.3;
            }
            100% {
                transform: scale(0.8);
                opacity: 0.8;
            }
        }

        @keyframes float {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes shake {
            0%,
            100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(-5deg);
            }
            75% {
                transform: rotate(5deg);
            }
        }

        .pulse-ring {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .shake-animation {
            animation: shake 0.5s ease-in-out infinite;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .warning-gradient {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
    </style>
</head>
<body class="h-full">
    <div id="app-wrapper" class="h-full w-full gradient-bg overflow-auto">
        <div class="fixed top-20 left-10 h-32 w-32 rounded-full bg-red-500 opacity-10 blur-3xl"></div>
        <div class="fixed bottom-20 right-10 h-40 w-40 rounded-full bg-orange-500 opacity-10 blur-3xl"></div>

        <div class="relative z-10 flex min-h-full flex-col items-center justify-center p-4 sm:p-8">
            <div class="glass-card w-full max-w-lg rounded-3xl p-6 text-center sm:p-10">
                <div class="relative mb-6 inline-block">
                    <div class="pulse-ring absolute inset-0 rounded-full bg-red-500 opacity-30"></div>
                    <div class="warning-gradient float-animation relative mx-auto flex h-24 w-24 items-center justify-center rounded-full sm:h-28 sm:w-28">
                        <svg class="shake-animation h-12 w-12 text-white sm:h-14 sm:w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>

                <p id="company-name" class="mb-2 text-sm font-medium uppercase tracking-widest text-gray-400 sm:text-base">
                    Madignet Cloud
                </p>
                <h1 id="main-title" class="mb-4 text-2xl font-bold text-white sm:text-3xl lg:text-4xl">
                    Layanan Internet Diisolir
                </h1>
                <div class="mx-auto mb-6 h-1 w-20 rounded-full bg-gradient-to-r from-red-500 to-orange-500"></div>
                <p id="main-message" class="mb-8 text-base leading-relaxed text-gray-300 sm:text-lg">
                    Akses <strong class="text-red-400">Mikhmon </strong> Anda telah dihentikan sementara karena tagihan belum dibayar.
                    Silakan lakukan pembayaran lalu konfirmasi ke admin untuk mengaktifkan kembali layanan Anda.
                </p>

                <div class="mb-6 rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="mb-4 flex items-center justify-center gap-3">
                        <svg class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="font-semibold text-white">Metode Pembayaran</span>
                    </div>

                    <div class="space-y-3 text-left">
                        <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-blue-500/30 bg-blue-600/20">
                                    <img src="https://raw.githubusercontent.com/DedhyPraditya/logo/main/BRI.png" alt="Logo BRI" class="h-10 w-10 rounded-lg bg-white object-contain p-1">
                                </div>
                                <div>
                                    <p class="text-sm font-medium tracking-wider text-gray-200">4985 0101 4052 536</p>
                                    <p class="text-xs text-gray-400">a.n. IPA MUSDALIPA</p>
                                </div>
                            </div>
                            <button type="button" onclick="copyText('498501014052536', 'Nomor Rekening BRI berhasil disalin!')" class="rounded-lg border border-white/10 bg-white/10 px-3 py-1 text-xs text-gray-300 transition hover:bg-white/20">
                                Salin
                            </button>
                        </div>

                        <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-orange-500/30 bg-orange-500/20">
                                    <img src="https://raw.githubusercontent.com/DedhyPraditya/logo/main/SeaBank.png" alt="Logo SeaBank" class="h-10 w-10 rounded-lg bg-white object-contain p-1">
                                </div>
                                <div>
                                    <p class="text-sm font-medium tracking-wider text-gray-200">9019 5782 3460</p>
                                    <p class="text-xs text-gray-400">a.n. IPA MUSDALIPA</p>
                                </div>
                            </div>
                            <button type="button" onclick="copyText('901957823460', 'Nomor Rekening SeaBank berhasil disalin!')" class="rounded-lg border border-white/10 bg-white/10 px-3 py-1 text-xs text-gray-300 transition hover:bg-white/20">
                                Salin
                            </button>
                        </div>

                        <div class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-blue-400/30 bg-blue-400/20">
                                    <img src="https://raw.githubusercontent.com/DedhyPraditya/logo/main/dana.png" alt="Logo DANA" class="h-10 w-10 rounded-lg bg-white object-contain p-1">
                                </div>
                                <div>
                                    <p class="text-sm font-medium tracking-wider text-gray-200">0823 9943 0312</p>
                                    <p class="text-xs text-gray-400">a.n. IPA MUSDALIPA</p>
                                </div>
                            </div>
                            <button type="button" onclick="copyText('082399430312', 'Nomor DANA berhasil disalin!')" class="rounded-lg border border-white/10 bg-white/10 px-3 py-1 text-xs text-gray-300 transition hover:bg-white/20">
                                Salin
                            </button>
                        </div>
                    </div>
                </div>

                <a href="https://wa.me/6282399430312?text=Halo%20Admin,%20saya%20sudah%20melakukan%20pembayaran%20tagihan%20internet.%20Mohon%20untuk%20dikonfirmasi%20dan%20diaktifkan%20kembali.%20Terima%20kasih." target="_blank" class="mb-4 flex w-full items-center justify-center gap-2 rounded-2xl bg-green-600 px-4 py-3 font-semibold text-white shadow-lg shadow-green-600/20 transition duration-300 hover:bg-green-700">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.713-1.457L0 24zm6.59-4.846c1.6.95 3.488 1.451 5.42 1.452 5.352 0 9.709-4.357 9.713-9.713.002-2.595-1.005-5.034-2.837-6.87C17.11 2.188 14.67 1.18 12.08 1.18 6.725 1.18 2.367 5.538 2.364 10.9c-.001 1.924.499 3.804 1.447 5.414l-.995 3.637 3.73-.978zm11.587-4.566c-.3-.149-1.774-.875-2.046-.975-.272-.1-.471-.149-.669.149-.198.297-.768.975-.941 1.173-.173.198-.347.223-.647.074-.3-.149-1.265-.466-2.41-1.487-.893-.797-1.495-1.78-1.67-2.08-.173-.297-.018-.458.13-.606.134-.133.3-.347.449-.52.149-.173.198-.297.298-.495.1-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.774-.726 2.022-1.429.247-.695.247-1.29.173-1.414-.074-.124-.272-.198-.57-.347z" />
                    </svg>
                    Konfirmasi via WhatsApp
                </a>

                <div class="mb-8 rounded-2xl border border-red-500/30 bg-red-500/10 p-5">
                    <div class="mb-3 flex items-center justify-center gap-3">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-semibold text-red-400">Informasi Penting</span>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-400">
                        Layanan akan aktif kembali secara otomatis setelah pembayaran dikonfirmasi oleh admin.
                        <strong class="text-red-400">Harap sertakan bukti transfer saat melakukan konfirmasi via WhatsApp.</strong>
                    </p>
                </div>
            </div>

            <p class="mt-8 text-xs text-gray-600">
                &copy; 2026 <span id="footer-company">Madignet Cloud</span>. All rights reserved.
            </p>
        </div>
    </div>

    <script>
    function copyText(text, message) {
        navigator.clipboard.writeText(text)
            .then(function () {
                alert(message);
            })
            .catch(function () {
                alert('Gagal menyalin teks. Silakan salin manual.');
            });
    }
    </script>
</body>
</html>
