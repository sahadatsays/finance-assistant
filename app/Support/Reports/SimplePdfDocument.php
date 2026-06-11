<?php

namespace App\Support\Reports;

class SimplePdfDocument
{
    /**
     * @param  list<string>  $lines
     */
    public static function fromLines(string $title, array $lines): string
    {
        $content = "BT\n/F1 12 Tf\n50 750 Td ({$title}) Tj\n/F1 10 Tf\n0 -20 Td\n";

        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= "({$escaped}) Tj\n0 -14 Td\n";
        }

        $content .= 'ET';
        $length = strlen($content);

        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $pdf .= "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $pdf .= "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n";
        $pdf .= "4 0 obj << /Length {$length} >> stream\n{$content}\nendstream endobj\n";
        $pdf .= "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        $offsets = [];
        $offsets[] = strlen($pdf);
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$offsets[0]}\n%%EOF";

        return $pdf;
    }
}
