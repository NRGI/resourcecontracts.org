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

        foreach ($text as $page) {
            $section   = $phpWord->addSection();
            $page = $this->escape($page);
            Html::addHtml($section, $page);
        }
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
        $html = str_replace([Chr(12),'<br>','<hr>'] , ['','<br/>', '<hr/>'], $html);
        $html = htmlspecialchars($html);
        $html = str_replace(['<', '>'], ['&lt;','&gt;'], $html);
        $turned = [ '&lt;pre&gt;', '&lt;/pre&gt;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;em&gt;', '&lt;/em&gt;', '&lt;u&gt;', '&lt;/u&gt;', '&lt;ul&gt;', '&lt;/ul&gt;', '&lt;li&gt;', '&lt;/li&gt;', '&lt;ol&gt;', '&lt;/ol&gt;', '&lt;br&gt;', '&lt;br/&gt;' ];
        $turn_back = [ '<pre>', '</pre>', '<b>', '</b>', '<em>', '</em>', '<u>', '</u>', '<ul>', '</ul>', '<li>', '</li>', '<ol>', '</ol>', '<br>', '<br/>' ];
        $html = str_replace( $turned, $turn_back, $html );

        return $html;
    }

}