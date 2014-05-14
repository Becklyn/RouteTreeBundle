<?php

namespace Becklyn\PageTreeBundle\Service;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class PlaceholderParameterGenerator
{
    /**
     * @var array
     */
    private $expressionContext;

    /**
     * @var ExpressionLanguage
     */
    private $language;



    /**
     *
     */
    public function __construct ()
    {
        $this->language          = new ExpressionLanguage();
        $this->initializeContext();
    }



    /**
     * Initializes the context for the expression language
     */
    private function initializeContext ()
    {
        $this->expressionContext = [];

        // register the date function
        $this->language->register(
            "date",
            function ($format)
            {
                return sprintf("date(%s)", $format);
            },
            function ($arguments, $format)
            {
                return date($format);
            }
        );
    }



    /**
     * Prepares the fake parameters
     *
     * @param array $pathVariables
     * @param array $fakeParameters
     *
     * @return array
     */
    public function prepareFakeParameters (array $pathVariables, array $fakeParameters = [])
    {
        $placeholders = [];

        foreach ($pathVariables as $pathVariable)
        {
            if (isset($fakeParameters[$pathVariable]))
            {
                try
                {
                    $placeholders[$pathVariable] = $this->language->evaluate($fakeParameters[$pathVariable], $this->expressionContext);
                }
                catch (SyntaxError $e)
                {
                    // if the compilation fails, we just use the text as string
                    // this allows arbitrary strings, which weren't meant as expression
                    $placeholders[$pathVariable] = $fakeParameters[$pathVariable];
                }
            }
            else
            {
                $placeholders[$pathVariable] = 1;
            }
        }

        return $placeholders;
    }
}