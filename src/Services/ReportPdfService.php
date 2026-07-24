<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Shared dompdf boilerplate for every Laporan PDF export, extracted from the pattern
 * first used in AssessmentController::exportPdf(). Also builds the "pengesahan
 * counselor" footer block the spec's Catatan Implementasi #3 requires on every report PDF.
 */
class ReportPdfService
{
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
        .page-break{ page-break-after: always; }
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
            . '<h1>' . htmlspecialchars($title) . '</h1>'
            . $bodyHtml
            . '</body></html>';

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Name Counselor / Date / Tanda Tangan block. Populated with the logged-in
     * counselor's name when available, otherwise left as a blank placeholder line —
     * matching the spec's "atau placeholder jika masih manual" note.
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
                    Counselor<br>
                    ' . $date . '
                </div>
            </td>
        </tr></table>
        <div class="footer-note">Dokumen ini bersifat rahasia dan dicetak otomatis oleh SIMKM.</div>';
    }
}
