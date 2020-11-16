<?php

namespace ManaPHP\Cli\Commands;

use ManaPHP\Cli\Command;
use ManaPHP\Helper\LocalFS;

class ExcelCommand extends Command
{
    /**
     * @param string $file ods content file path
     */
    public function odsAction($file)
    {
        if (LocalFS::dirExists($file)) {
            $file .= '/content.xml';
        }

        $content = LocalFS::fileGet($file);

        $content = preg_replace('#table:formula="[^\"]+" #', '', $content);
        $content = preg_replace('#table:number-rows-repeated="\d+"#', 'table:number-rows-repeated="100"', $content);
        $content = preg_replace('#office:value-type="float" office:value=".*?" #', '', $content);

        LocalFS::filePut($file . '.xml', $content);
    }
}