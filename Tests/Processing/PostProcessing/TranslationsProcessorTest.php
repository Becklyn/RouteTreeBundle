<?php

namespace Tests\Becklyn\RouteTreeBundle\Processing\PostProcessing;

use Becklyn\RouteTreeBundle\Node\Node;
use Becklyn\RouteTreeBundle\PostProcessing\Processor\TranslationsProcessor;
use Becklyn\RouteTreeBundle\Tree\RouteTree;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;


/**
 *
 */
class TranslationsProcessorTest extends TestCase
{
    /**
     * @var TranslationsProcessor
     */
    private $translationPostProcessing;


    public function setUp ()
    {
        $translator = new Translator("de");
        $translator->addLoader("array", new ArrayLoader());
        $translator->addResource("array", [
            "My title" => "Mein Titel"
        ], "de", RouteTree::TREE_TRANSLATION_DOMAIN);

        $this->translationPostProcessing = new TranslationsProcessor($translator);
    }


    /**
     * @return array
     */
    public function dataProviderTestTranslation ()
    {
        return [
            // missing translation
            ["Other title", "Other title"],
            // existing translation
            ["My title", "Mein Titel"],
        ];
    }


    /**
     * @dataProvider dataProviderTestTranslation
     */
    public function testTranslation (string $title, string $expectedTitle)
    {
        $node = new Node("route");
        $node->setTitle($title);
        $this->translationPostProcessing->process($node);

        $this->assertSame($expectedTitle, $node->getTitle());
    }
}
