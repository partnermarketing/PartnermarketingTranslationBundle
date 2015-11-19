<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Utilities;

use Partnermarketing\TranslationBundle\Utilities\HasUtilitiesTraitImplementation;

class HasUtilitiesTraitImplementationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HasUtilitiesTraitImplementation
     */
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

        $this->assertSame($expected, $unsortedArray);
    }

    /**
     * Method to provide test cases to sort single dimension arrays.
     *
     * @return array
     */
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

    /**
     * Method to provide test cases to sort multi dimension arrays.
     *
     * @return array
     */
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

        $multi3 = [
            '_campaigns_dropdown' => [
                'campaigns' => 'Campaigns',
                'title' => 'Campaigns and Assets',
                'campaign_builder_grouping' => 'Campaign builder',
                'create_campaign' => 'Create a new campaign',
                'my_activity' => 'My activity'
            ]
        ];

        $multiExpected3 = [
            '_campaigns_dropdown' => [
                'campaign_builder_grouping' => 'Campaign builder',
                'campaigns' => 'Campaigns',
                'create_campaign' => 'Create a new campaign',
                'my_activity' => 'My activity',
                'title' => 'Campaigns and Assets'
            ]
        ];

        return [
            [$multi1, $multiExpected1],
            [$multi2, $multiExpected2],
            [$multi3, $multiExpected3]
        ];
    }

}