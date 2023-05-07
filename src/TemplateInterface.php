<?php
/**
 * To be implemented by the project using this lib
 *
 * @author Wasseem Khayrattee <hey@wk.contact>
 * @github @wkhayrattee
 */

namespace Groguphp;

interface TemplateInterface
{
    public function setTemplate(string $tpl_name);
    public function getOutput(array $option_list);
}
