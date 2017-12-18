<?php

namespace Tests\Becklyn\RouteTreeBundle\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Builder\ParametersGenerator;
use Becklyn\RouteTreeBundle\Builder\TreeBuilder;
use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\Tree\Processing\PostProcessing\TranslationsProcessor;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use Tests\Becklyn\RouteTreeBundle\RouteTestTrait;


/**
 *
 */
class TranslationsProcessorTest extends TestCase
{
    use RouteTestTrait;

    /**
     * @var TreeBuilder
     */
    private $builder;


    /**
     * @var TranslationsProcessor
     */
    private $translationPostProcessing;


    public function setUp ()
    {
        $this->builder = new TreeBuilder(new ParametersGenerator());

        $translator = new Translator("de", new MessageSelector());
        $translator->addLoader("array", new ArrayLoader());
        $translator->addResource("array", [
            "My title" => "Mein Titel"
        ], "de", RouteTree::TREE_TRANSLATION_DOMAIN);

        $this->translationPostProcessing = new TranslationsProcessor($translator);
    }



    public function testMissingTranslation ()
    {
        $node = new Node("route");
        $node->setTitle("Other title");
        $this->translationPostProcessing->process($node);

        $this->assertSame("Other title", $node->getTitle());
    }



    public function testExistingTranslation ()
    {
        $node = new Node("route");
        $node->setTitle("My title");
        $this->translationPostProcessing->process($node);

        $this->assertSame("Mein Titel", $node->getTitle());
    }
}
