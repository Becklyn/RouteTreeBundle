<?php declare(strict_types=1);

namespace Becklyn\RouteTreeBundle\PostProcessing\Processor\Security;

use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;

class ExtendedExpressionLanguage extends ExpressionLanguage
{
    /**
     * @inheritdoc
     *
     * Add
     */
    protected function registerFunctions() : void
    {
        parent::registerFunctions();

        $this->register('is_granted', function ($attributes, $object = 'null') {
            return \sprintf('$auth_checker->isGranted(%s, %s)', $attributes, $object);
        }, function (array $variables, $attributes, $object = null) {
            return $variables['auth_checker']->isGranted($attributes, $object);
        });
    }
}
