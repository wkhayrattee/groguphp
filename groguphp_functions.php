<?php
/**
 * List of quick handy (and short named) functions to be used specially on the templating
 *
 * @author Wasseem Khayrattee <hey@wk.contact>
 * @github @wkhayrattee
 */

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * A wrapper for kint
 *
 * @param $array
 */
function sk($array): void
{
    if (defined('IS_DEBUG_ENABLED') && IS_DEBUG_ENABLED === true) {
        \Kint\Kint::dump($array);
    }
}

/**
 * Mainly used for debugging
 * A convenience function to output values of Variables and/or Arrays in a more readable manner
 *
 * @param $array
 * @param bool $print
 * @param bool $die
 * @return string
 */
function sp($array, bool $print = true, bool $die = false): string
{
    $result = '<pre>';
    $result .= print_r($array, true);
    $result .= '</pre><br/>';
    if ($print) {
        echo $result;
        if ($die) {
            //die;
            //added the below instead of die() to make the method easier to test
            throw new RuntimeException('Script terminated by sp function');
        }
    }

    return $result;
}

/**
 * Mainly used for debugging
 * A convenience function to output values of Variables and/or Arrays in a more readable manner
 *
 * same as sp(), but with die()
 *
 * @param $array
 * @param bool $print
 */
function sd($array, bool $print = true): void
{
    sp($array, $print, true);
}

/**
 * A wrapper to use Symfony Response for redirects
 * Note: You should call exit after calling this method
 *          - I have not done this here to make the method easy to test
 *
 * The S in (s)redirect stands for Symfony so that we imply do_sumfony_redirect
 *
 * @param string $page
 * @param RedirectResponse|null $response
 * @return RedirectResponse
 */
function sredirect(string $page, ?RedirectResponse $response = null): RedirectResponse
{
    $response = $response ?: new RedirectResponse($page);
    $response->send();

    return $response;
}
