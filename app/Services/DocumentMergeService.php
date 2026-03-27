<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class DocumentMergeService
{
    public function buildPdf(array $uploadedFiles): array
    {
        $fpdi = new Fpdi();
        $pageCount = 0;
        $sourceNames = [];

        foreach ($uploadedFiles as $file) {
            /** @var UploadedFile $file */
            $sourceNames[] = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $path = $file->getRealPath();

            if ($mime === 'application/pdf') {
                $pagesInPdf = $fpdi->setSourceFile($path);
                for ($i = 1; $i <= $pagesInPdf; $i++) {
                    $templateId = $fpdi->importPage($i);
                    $size = $fpdi->getTemplateSize($templateId);
                    $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $fpdi->useTemplate($templateId);
                    $pageCount++;
                }
            } elseif (in_array($mime, ['image/jpeg', 'image/png'], true)) {
                $fpdi->AddPage();
                // FPDF image method signature: Image(file, x, y, w, h, type)
                // Let's maximize the image preserving aspect ratio to an A4 page (210x297mm)
                
                // Get image dimensions to scale appropriately
                $size = getimagesize($path);
                if ($size) {
                    $imgWidth = $size[0];
                    $imgHeight = $size[1];
                    $ratio = $imgWidth / $imgHeight;
                    
                    $targetWidth = 210;
                    $targetHeight = 297;
                    $targetRatio = $targetWidth / $targetHeight;
                    
                    if ($ratio > $targetRatio) {
                        $w = $targetWidth;
                        $h = $targetWidth / $ratio;
                    } else {
                        $h = $targetHeight;
                        $w = $targetHeight * $ratio;
                    }
                    
                    // Center the image
                    $x = (210 - $w) / 2;
                    $y = (297 - $h) / 2;
                    
                    // The FPDF image function doesn't actually throw on GD absence if we just pass the path and it can read dimensions.
                    $fpdi->Image($path, $x, $y, $w, $h);
                } else {
                    $fpdi->Image($path, 0, 0, 210);
                }
                
                $pageCount++;
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'merged_pdf_');
        $fpdi->Output('F', $tempPath);

        return [
            'temp_path' => $tempPath,
            'page_count' => $pageCount,
            'source_names' => $sourceNames,
        ];
    }
}
