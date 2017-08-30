<?php
declare(strict_types = 1);

namespace Biaoqianwo\Html2Pdf;

class Html2Pdf
{
    private $wkhtmltopdf;

    /**
     * @param string $wkhtmltopdf
     */
    public function __construct(string $wkhtmltopdf)
    {
        $this->wkhtmltopdf = $wkhtmltopdf;
    }

    /**
     * @param string $html
     * @param array $options
     * @return string
     * @throws \RuntimeException
     */
    public function getOutputFromHtml(string $html, array $options = null) : string
    {
        $guid = uniqid('html2pdf_', false);

        $input = $this->generateFile($guid, 'html');
        file_put_contents($input, $html);

        $output = $this->generateFile($guid, 'pdf');
        $source = $this->generatePdf($input, $output, $options);

        $this->cleanFile($input);
        $this->cleanFile($output);

        if (!$source) {
            throw new \RuntimeException('Pdf generation failed');
        }

        return $source;
    }

    /**
     * @param string $input
     * @param string $output
     * @param array $options
     * @return string
     */
    private function generatePdf(string $input, string $output, array $options = null) : string
    {
        $header = isset($options['header']) ? $this->getHeader($options['header']) : '';
        $footer = isset($options['footer']) ? $this->getFooter($options['footer']) : '';

        $cmd = sprintf(
            '%s ' .
            '--print-media-type ' .
            '--lowquality ' .
            '--quiet ' .
            '--load-error-handling ignore ' .
            '--load-media-error-handling ignore ' .
            '%s %s %s %s',
            $this->wkhtmltopdf,
            $header,
            $footer,
            escapeshellarg($input),
            escapeshellarg($output)
        );

        shell_exec($cmd);

        return file_get_contents($output);
    }

    /**
     * @param string $file
     * @return bool
     */
    private function cleanFile(string $file) : bool
    {
        return unlink($file);
    }

    /**
     * @param string $fileName
     * @param string $extension
     * @return string
     */
    private function generateFile(string $fileName, string $extension) : string
    {
        return "/tmp/{$fileName}.{$extension}";
    }

    /**
     * @param string $header
     * @return string
     */
    private function getHeader(string $header) : string
    {
        return sprintf(
            '--header-font-size 7 --header-center %s',
            escapeshellarg($header)
        );
    }

    /**
     * @param string $footer
     * @return string
     */
    private function getFooter(string $footer) : string
    {
        return sprintf(
            '--footer-font-size 7 --footer-left %s --footer-right "[page]"',
            escapeshellarg($footer)
        );
    }
}