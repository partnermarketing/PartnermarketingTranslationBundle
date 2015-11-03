<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Utilities;

use Partnermarketing\TranslationBundle\Utilities\HasUtilitiesTraitImplementation;

class HasUtilitiesTraitImplementationTest extends \PHPUnit_Framework_TestCase
{
    private $traitObject;

    public function setUp()
    {
        $this->traitObject = new HasUtilitiesTraitImplementation();
    }

    /**
     * @dataProvider singleDimensionProvider
     */
    public function testKsortMultiDimensionalSortSingleDimention($unsortedArray, $expected)
    {
        $this->traitObject->ksortMultiDimensional($unsortedArray);

        $this->assertEquals($expected, $unsortedArray);
    }

    /**
     * @dataProvider multiDimensionProvider
     */
    public function testKsortMultiDimensionalSortMultiDimension($unsortedArray, $expected)
    {

        $this->traitObject->ksortMultiDimensional($unsortedArray);


        $this->assertEquals($expected, $unsortedArray);
    }


    public function singleDimensionProvider()
    {
        return [
            [
                // sort number
                ['c' => 3, 'b' => 2],
                ['b' => 2, 'c' => 3]
            ],
            [
                // sort strings
                ['c' => 'c', 'b' => 'b'],
                ['b' => 'b', 'c' => 'c'],
            ],
            [
                // sort integer index
                [3 => 'c', 1 => 'b'],
                [1 => 'b', 3 => 'c'],
            ]
        ];
    }


    public function multiDimensionProvider()
    {
        $multi1 = [
            'page2' => ['section' => 'page.section'],
            'page' => ['section' => 'page.section']
        ];
        $multiExpected1 = [
            'page' => ['section' => 'page.section'],
            'page2' => ['section' => 'page.section']
        ];

        $multi2 = [
            'page2' => ['section2' => 'page.section2'],
            'page' => ['section1' => 'page.section1']
        ];

        $multiExpected2 = [
            'page' => ['section1' => 'page.section1'],
            'page2' => ['section2' => 'page.section2']
        ];


        return [
            [$multi1, $multiExpected1],
            [$multi2, $multiExpected2]
        ];
    }



}