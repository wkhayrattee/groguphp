<?php

use Groguphp\TemplateInterface;
use Groguphp\TemplateTrait;

test('The template trait sets and gets template correctly', function () {
    $template = new class implements TemplateInterface {
        use TemplateTrait;

        public function __construct()
        {
            $this->view_object = new class {
                public function render($tpl_name, $option_list)
                {
                    return $tpl_name . ' rendered';
                }
            };
        }
    };

    $template->setTemplate('testTemplate');
    $output = $template->getOutput([]);

    expect($output)->toBe('testTemplate rendered');
});
