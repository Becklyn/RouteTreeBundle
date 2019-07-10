<?php declare(strict_types=1);

namespace Tests\Becklyn\RouteTreeBundle\Parameters;

use Becklyn\Menu\Item\MenuItem;
use Becklyn\RouteTreeBundle\Exception\InvalidParameterValueException;
use Becklyn\RouteTreeBundle\Parameter\ParametersMerger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ParameterMergerTest extends TestCase
{
    /**
     * @return array
     */
    public function provideExamples () : array
    {
        /**
         * The array has 5 entries
         *
         * 1. variable names
         * 2. item parameters
         * 3. route specific values
         * 4. default parameters
         * 5. request parameters
         * 6. expected merged values
         */
        return [
            "item only" => [
                ["abc"], // variables
                ["abc" => 5], // item
                [], // route specific
                [], // defaults
                [], // request
                ["abc" => 5], // expected
            ],
            "route specific only" => [
                ["abc"], // variables
                [], // item
                ["example.route" => ["abc" => 5]], // route specific
                [], // defaults
                [], // request
                ["abc" => 5], // expected
            ],
            "defaults only" => [
                ["abc"], // variables
                [], // item
                [], // route specific
                ["abc" => 5], // defaults
                [], // request
                ["abc" => 5], // expected
            ],
            "request only" => [
                ["abc"], // variables
                [], // item
                [], // route specific
                [], // defaults
                ["abc" => 5], // request
                ["abc" => 5], // expected
            ],
            "nothing" => [
                ["abc"], // variables
                [], // item
                [], // route specific
                [], // defaults
                [], // request
                ["abc" => null], // expected
            ],
            "item most specific" => [
                ["abc"], // variables
                ["abc" => 1], // item
                ["example.route" => ["abc" => 2]], // route specific
                ["abc" => 3], // defaults
                ["abc" => 4], // request
                ["abc" => 1], // expected
            ],
            "route most specific" => [
                ["abc"], // variables
                [], // item
                ["example.route" => ["abc" => 2]], // route specific
                ["abc" => 3], // defaults
                ["abc" => 4], // request
                ["abc" => 2], // expected
            ],
            "defaults most specific" => [
                ["abc"], // variables
                [], // item
                [], // route specific
                ["abc" => 3], // defaults
                ["abc" => 4], // request
                ["abc" => 3], // expected
            ],
            "request most specific" => [
                ["abc"], // variables
                [], // item
                [], // route specific
                [], // defaults
                ["abc" => 4], // request
                ["abc" => 4], // expected
            ],
            "different route" => [
                ["abc"], // variables
                [], // item
                ["other" => ["abc" => 5]], // route specific
                [], // defaults
                [], // request
                ["abc" => null], // expected
            ],
            "object with id route" => [
                ["abc"], // variables
                [], // item
                [], // route specific
                [], // defaults
                [
                    "abc" => new class {
                        public function getId()
                        {
                            return 123;
                        }
                    },
                ], // request
                ["abc" => 123], // expected
            ],
        ];
    }


    /**
     * @dataProvider provideExamples
     *
     * @param array $variables
     * @param array $itemParameters
     * @param array $routeSpecificParameters
     * @param array $defaultParameters
     * @param array $requestParameters
     * @param array $expected
     *
     * @throws \Becklyn\RouteTreeBundle\Exception\InvalidParameterValueException
     */
    public function testExamples (
        array $variables,
        array $itemParameters,
        array $routeSpecificParameters,
        array $defaultParameters,
        array $requestParameters,
        array $expected
    ) : void
    {
        $item = new MenuItem(null, [
            "route" => "example.route",
            "routeParameters" => $itemParameters,
            "extras" => [
                ParametersMerger::VARIABLES_EXTRA_KEY => $variables,
            ],
        ]);

        $this->createParametersMerger($requestParameters)->mergeParameters($item, $defaultParameters, $routeSpecificParameters);
        self::assertEquals($expected, $item->getTarget()->getParameters());
    }


    /**
     * @return array
     */
    public function provideInvalidParameterValues () : array
    {
        return [
            "bool" => [true],
            "object with ->getId()" => [new \stdClass()],
        ];
    }


    /**
     * @dataProvider provideInvalidParameterValues
     *
     * @param mixed $value
     */
    public function testInvalidParameterValues ($value) : void
    {
        $this->expectException(InvalidParameterValueException::class);

        $item = new MenuItem(null, [
            "route" => "example.route",
            "extras" => [
                ParametersMerger::VARIABLES_EXTRA_KEY => ["a"],
            ],
        ]);

        $this->createParametersMerger()->mergeParameters($item, ["a" => $value], []);
    }



    /**
     * @param array $requestAttributes
     *
     * @return ParametersMerger
     */
    private function createParametersMerger (array $requestAttributes = []) : ParametersMerger
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request([], [], $requestAttributes);

        $requestStack
            ->method("getMasterRequest")
            ->willReturn($request);

        return new ParametersMerger($requestStack);
    }
}
