<?php

namespace Becklyn\PageTreeBundle\Service;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

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
     * @param array $requirements
     * @param array $fakeParameters
     *
     * @return array
     */
    public function prepareFakeParameters (array $requirements, array $fakeParameters = [])
    {
        $placeholders = [];

        foreach ($requirements as $parameter => $requirement)
        {
            if (isset($fakeParameters[$parameter]))
            {
                $placeholders[$parameter] = $this->language->evaluate($fakeParameters[$parameter], $this->expressionContext);
            }
            else
            {
                $placeholders[$parameter] = 1;
            }
        }

        return $placeholders;
    }
}