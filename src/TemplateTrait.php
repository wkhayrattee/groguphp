<?php
/**
 * @author Wasseem Khayrattee <hey@wk.contact>
 * @github @wkhayrattee
 */
namespace Groguphp;

trait TemplateTrait
{
    private object $view_object;
    private string $tpl_name;

    /**
     * @param string $tpl_name
     */
    public function setTemplate(string $tpl_name): void
    {
        $this->tpl_name = $tpl_name;
    }

    /**
     * @param array $option_list
     *
     * @return mixed
     */
    public function getOutput(array $option_list): mixed
    {
        return $this->view_object->render($this->tpl_name, $option_list);
    }
}
