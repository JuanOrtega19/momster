<?php
/**
 * Autoload untuk PdfParser
 */
 
require_once "config.php";
spl_autoload_register(function ($class) {
    $prefix = 'Smalot\\PdfParser\\';
    $base_dir = __DIR__ . '/lib/PdfParser/src/Smalot/PdfParser/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
    
    use Smalot\PdfParser\Parser;
    
    /**
     * Ekstrak text dari DOCX
     */
    function readDocx($filePath) {
        $zip = new ZipArchive;
        $text = "";
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName("word/document.xml")) !== false) {
                $xml = $zip->getFromIndex($index);
                $zip->close();
                $xml = str_replace("</w:p>", "\n", $xml);
                $text = strip_tags($xml);
            }
        }
        return $text;
    }
    
    /**
     * Ekstrak kasar dari DOC (Word lama)
     */
    function readDoc($filePath) {
        $content = @file_get_contents($filePath);
        if (!$content) return "";
        $text = preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $content);
        return trim($text);
    }
    
    /**
     * Ekstrak text dari XLSX (Excel modern)
     */
    function readXlsx($filePath) {
        $zip = new ZipArchive;
        $text = "";
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName("xl/sharedStrings.xml")) !== false) {
                $xml = $zip->getFromIndex($index);
                $zip->close();
                $xmlObj = simplexml_load_string($xml);
                if ($xmlObj) {
                    foreach ($xmlObj->si as $si) {
                        $text .= (string)$si->t . " ";
                    }
                }
            }
        }
        return trim($text);
    }
    
    /**
     * Ekstrak kasar dari XLS (Excel lama)
     */
    function readXls($filePath) {
        $content = @file_get_contents($filePath);
        if (!$content) return "";
        $text = preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $content);
        return trim($text);
    }
    
    /**
     * Ekstrak text dari PDF (pakai Smalot\PdfParser)
     */
    function readPdf($filePath) {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        } catch (Exception $e) {
            return "";
        }
    }
    
    /**
     * Ekstrak text utama dari berbagai format
     */
    function extractText($filePath, $ext) {
        $ext = strtolower($ext);
        switch ($ext) {
            case "txt":
            case "log":
                return file_get_contents($filePath);
                
            case "docx":
                return readDocx($filePath);
                
            case "doc":
                return readDoc($filePath);
                
            case "xlsx":
                return readXlsx($filePath);
                
            case "xls":
                return readXls($filePath);
                
            case "pdf":
                return readPdf($filePath);
                
            default:
                return "";
        }
    }
    
    /**
     * Cari hashtag di dalam text (#example)
     */
    function extractHashtags($text) {
        preg_match_all('/#(\w+)/u', $text, $matches);
        return array_unique($matches[1]); // hanya ambil kata tanpa "#"
    }
    
    /**
     * Ekstrak text + hashtag sekaligus
     */
    function extractTextWithHashtags($filePath, $ext) {
        $text = extractText($filePath, $ext);
        $hashtags = extractHashtags($text);
        return [
            "text" => $text,
            "hashtags" => $hashtags
        ];
    }
    