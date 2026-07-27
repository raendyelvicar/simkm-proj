<?php

namespace App\Services;

use App\Repositories\SettingsRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Shared dompdf boilerplate for every Laporan PDF export, extracted from the pattern
 * first used in AssessmentController::exportPdf(). Also builds the organization
 * letterhead header (PDF export only — set by an admin at /admin/settings) and the
 * "pengesahan counselor" footer block the spec's Catatan Implementasi #3 requires on
 * every report PDF.
 */
class ReportPdfService
{
    private SettingsRepository $settings;

    public function __construct()
    {
        $this->settings = new SettingsRepository();
    }

    private const STYLE = '
        body{ font-family: DejaVu Sans, sans-serif; font-size:11px; color:#111; }
        h1{ text-align:center; color:#2563eb; margin-bottom:4px; font-size:18px; }
        h2{ color:#2563eb; font-size:13px; margin-top:20px; }
        .subtitle{ text-align:center; color:#555; margin-bottom:16px; }
        .meta{ width:100%; margin-bottom:12px; font-size:11px; }
        .meta td{ padding:2px 6px 2px 0; }
        .meta .label{ font-weight:bold; width:140px; }
        .table{ width:100%; border-collapse:collapse; margin-top:8px; }
        .table td, .table th{ padding:6px; border:1px solid #ddd; font-size:10px; text-align:left; }
        .table th{ background:#f5f5f5; }
        .table td.label{ width:22%; font-weight:bold; background:#fafafa; }
        .diary-entry{ border-bottom:1px solid #ddd; padding:8px 0 12px; }
        .diary-entry h2{ margin-top:6px; margin-bottom:6px; }
        .diary-row{ font-size:10px; padding:2px 0; }
        .diary-label{ font-weight:bold; }
        .org-header{ width:100%; margin-bottom:10px; padding-bottom:10px; border-bottom:2px solid #ddd; }
        .org-header td{ vertical-align:middle; }
        .org-logo-cell{ width:64px; }
        .org-logo-cell img{ height:56px; width:56px; object-fit:contain; }
        .org-name{ font-size:14px; font-weight:bold; color:#111; }
        .org-meta{ font-size:10px; color:#555; }
        .pengesahan{ margin-top:36px; width:100%; }
        .pengesahan td{ vertical-align:top; padding-top:40px; font-size:11px; }
        .pengesahan .sign-block{ text-align:center; width:220px; }
        .pengesahan .sign-line{ margin-top:48px; border-top:1px solid #333; padding-top:4px; }
        .footer-note{ margin-top:24px; font-size:9px; color:#888; text-align:center; }
    ';

    /** Renders $bodyHtml inside a standard A4 document shell and streams it as a download. */
    public function stream(string $title, string $bodyHtml, string $filename): void
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . self::STYLE . '</style></head><body>'
            . $this->orgHeaderHtml()
            . '<h1>' . htmlspecialchars($title) . '</h1>'
            . $bodyHtml
            . '</body></html>';

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        // Admin-scoped reports (e.g. Laporan Diary with no student/counselor filter) can
        // render hundreds of detail pages at once; dompdf's style/layout pass on that much
        // HTML routinely blows past PHP's default 128M/30s, aborting the export. Raise both
        // just for this render rather than editing php.ini globally.
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    // Org letterhead (name/address/phone/email/logo), set by an admin at /admin/settings.
    // The logo is inlined as a base64 data URI — dompdf runs with isRemoteEnabled=false,
    // and embedding avoids any local-file chroot/path resolution concerns entirely.
    private function orgHeaderHtml(): string
    {
        $name = $this->settings->get('org_name', '');
        $address = $this->settings->get('org_address', '');
        $phone = $this->settings->get('org_phone', '');
        $email = $this->settings->get('org_email', '');
        $logoPath = $this->settings->get('org_logo_path', '');

        if (!$name && !$address && !$phone && !$email && !$logoPath) {
            return '';
        }

        $logoCell = '';
        if ($logoPath) {
            $fullPath = __DIR__ . '/../../public' . $logoPath;
            if (is_file($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'image/png';
                $data = base64_encode(file_get_contents($fullPath));
                $logoCell = '<td class="org-logo-cell"><img src="data:' . $mime . ';base64,' . $data . '"></td>';
            }
        }

        $contact = implode(' · ', array_filter([
            $phone ? 'Telp: ' . $phone : '',
            $email ? 'Email: ' . $email : '',
        ]));

        $textLines = array_filter([
            $name ? '<div class="org-name">' . htmlspecialchars($name) . '</div>' : '',
            $address ? '<div class="org-meta">' . htmlspecialchars($address) . '</div>' : '',
            $contact ? '<div class="org-meta">' . htmlspecialchars($contact) . '</div>' : '',
        ]);

        return '<table class="org-header"><tr>' . $logoCell . '<td>' . implode('', $textLines) . '</td></tr></table>';
    }

    /**
     * Name Counselor / Date / Tanda Tangan block. Populated with the logged-in
     * counselor's name when available; callers fall back to a fixed default name
     * otherwise (see ReportController::DEFAULT_COUNSELOR_NAME). Falls back to a blank
     * placeholder line only if no name at all is passed in.
     */
    public function pengesahanBlock(?string $counselorName): string
    {
        $name = $counselorName ? htmlspecialchars($counselorName) : '________________________';
        $date = htmlspecialchars(date('d F Y'));

        return '
        <table class="pengesahan"><tr>
            <td></td>
            <td class="sign-block">
                Mengetahui,<br>
                <div class="sign-line">
                    <strong>' . $name . '</strong><br>
                    Konselor<br>
                    ' . $date . '
                </div>
            </td>
        </tr></table>
        <div class="footer-note">Dokumen ini bersifat rahasia dan dicetak otomatis oleh SIMKM.</div>';
    }
}
