<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('assets/favicon/favicon.svg') }}" />
        <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}" />
        <meta name="apple-mobile-web-app-title" content="وزارة التربية والتعليم" />
        <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}" />

        <!-- Scripts -->
        @vite(['resources/css/app.css'])

        <style>
            /** Page geometry (native paged-media pagination). */
            @page {
                size: {{ $size }}{{ $landscape ? ' landscape' : '' }};
                margin: {{ $margin }};

                /*
                 * Claim every margin box so the browser cannot inject its own
                 * headers/footers (URL, date, title, page count). Defining our
                 * own margin content overrides the dialog's "Headers and footers"
                 * option, which is the closest achievable result to disabling it.
                 */
                @top-left { content: ""; }
                @top-center { content: ""; }
                @top-right { content: ""; }
                @bottom-center { content: ""; }

                @bottom-left {
                    content: "{{ __('صفحة') }} " counter(page) " / " counter(pages);
                    font-size: 8.5pt;
                    color: #111;
                    direction: rtl;
                }

                @bottom-right {
                    @if (filled($printedBy))
                        content: "{{ __('طبع بواسطة') }}: {{ str_replace(['"', '\\'], '', $printedBy) }}";
                    @endif
                    font-size: 8.5pt;
                    color: #111;
                    direction: rtl;
                }
            }

            html, body {
                margin: 0;
                padding: 0;
            }

            body {
                direction: rtl;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            main {
                margin: 0;
                min-width: initial !important;
            }

            /** Fixed-layout sheets (used by designed multi-page forms). */
            .sheet {
                position: relative;
                box-sizing: border-box;
                overflow: hidden;
            }

            body.A4 .sheet           { width: 210mm; min-height: 297mm }
            body.A4.landscape .sheet { width: 297mm; min-height: 210mm }
            body.A5 .sheet           { width: 148mm; min-height: 210mm }
            body.A5.landscape .sheet { width: 210mm; min-height: 148mm }

            .sheet.padding-8mm  { padding: 8mm }
            .sheet.padding-10mm { padding: 10mm }
            .sheet.padding-15mm { padding: 15mm }

            /** Flowing reports: paper surface that the data table lives in. */
            .print-surface {
                box-sizing: border-box;
            }

            @media print {
                /*
                 * Force every color/background to print regardless of the
                 * dialog's "Background graphics" option. This is the closest
                 * achievable result to enabling it by default.
                 */
                *,
                *::before,
                *::after {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                .sheet {
                    width: auto;
                    min-height: 0;
                    overflow: visible;
                    break-after: page;
                    page-break-after: always;
                }

                main > .sheet:last-child {
                    break-after: auto;
                    page-break-after: auto;
                }

                .print-surface {
                    width: auto;
                    margin: 0;
                    /*
                     * Keep the table a hair inside the page content box so the
                     * collapsed outer border (painted half outside the table box)
                     * is never clipped by the non-printable @page margin area.
                     */
                    padding: 0 1mm;
                    background: transparent;
                    box-shadow: none;
                }
            }

            @media screen {
                body {
                    background: #e2e8f0;
                    padding: 8mm 0;
                }

                .sheet {
                    background: #fff;
                    box-shadow: 0 1mm 3mm rgba(15, 23, 42, .25);
                    margin: 0 auto 8mm;
                }

                .print-surface {
                    width: 210mm;
                    margin: 0 auto;
                    padding: 14mm 12mm 16mm;
                    background: #fff;
                    box-shadow: 0 1mm 3mm rgba(15, 23, 42, .25);
                }

                body.landscape .print-surface {
                    width: 297mm;
                }
            }
        </style>
    </head>

    <body class="font-sans {{ $size }} {{ $landscape ? 'landscape' : '' }}">
        <main>
            {{ $content }}
        </main>

        @env('production')
            <script>
                window.addEventListener('DOMContentLoaded', () => {
                    window.print();

                    window.addEventListener('afterprint', () => {
                        window.close();
                    });

                    window.addEventListener('focus', () => {
                        setTimeout(() => {
                            window.close();
                        }, 500);
                    });
                });
            </script>
        @endenv
    </body>
</html>
