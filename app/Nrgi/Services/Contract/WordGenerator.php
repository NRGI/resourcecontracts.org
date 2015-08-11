<?php namespace App\Nrgi\Services\Contract;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class WordGenerator
{
    /**
     * Create word file using phpWord library
     *
     * @param array $text
     * @param       $file
     * @return string
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function create(array $text, $file)
    {
        $file_path = public_path($file);
        $phpWord   = new PhpWord();

        $txt = '';
       foreach ($text as $page) {
            $section   = $phpWord->addSection();

            $page = $this->escape($page);
           $txt .= $page;
            Html::addHtml($section, $page);
        }

        file_put_contents('./word.txt',$txt);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($file_path);

        return $file_path;
    }

    /**
     * Escape Html
     *
     * @param $html
     * @return mixed|string
     */
    protected function escape($html)
    {
        $html = str_replace([Chr(12),'&','<br>'] , ['','_amp_','<br/>'], $html);
        $html = strip_tags($html,'<br>');
        $html  = nl2br($html);

        return $html;
    }

    /**
     * Removes invalid XML
     *
     * @param string $value
     * @return string
     */
    public function stripInvalidXml($value)
    {
        $ret = '';

        if (empty($value))
        {
            return $ret;
        }

        $length = strlen($value);
        for ($i=0; $i < $length; $i++)
        {
            $current = ord($value{$i});
            if (($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) ||
                (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                (($current >= 0x10000) && ($current <= 0x10FFFF)))
            {
                $ret .= chr($current);
            }
            else
            {
                $ret .= " ";
            }
        }
        return $ret;
    }

}