<?php
declare(strict_types = 1);

namespace Biaoqianwo\Html2Pdf;

class Html2Image
{
    /** @var string */
    private $wkhtmltoimage;

    /**
     * @param string $wkhtmltox
     */
    public function __construct(string $wkhtmltox)
    {
        $this->wkhtmltoimage = $wkhtmltox;
    }

    /**
     * @param string $html
     * @param array $options
     * @return string
     * @throws \RuntimeException
     */
    public function getOutputFromHtml(string $html, array $options = array()) : string
    {
        $guid = uniqid('html2img_', false);

        $input = $this->generateFile($guid, 'html');
        file_put_contents($input, $html);

        $output = $this->generateFile($guid, 'jpg');
        $source = $this->generateImg($input, $output, $options);

        $this->cleanFile($input);
        $this->cleanFile($output);

        if (!$source) {
            throw new \RuntimeException('Image generation failed');
        }

        return $source;
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
     * @param string $input
     * @param string $output
     * @param array $options
     * @return string
     */
    private function generateImg(string $input, string $output, array $options = array())
    {
        $format = $options['format'] ?? 'jpg';
        $width = isset($options['width']) ? (int)$options['width'] : 600;
        $height = isset($options['height']) ? (int)$options['height'] : 800;

        $cmd = sprintf(
            '%s ' .
            '-q ' .
            '--format %s ' .
            '--width %d ' .
            '--height %d ' .
            '%s %s',
            $this->wkhtmltoimage,
            escapeshellarg($format),
            $width,
            $height,
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
}