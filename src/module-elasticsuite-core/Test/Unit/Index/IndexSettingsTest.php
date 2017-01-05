<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 *
 * @category  Smile_Elasticsuite
 * @package   Smile\ElasticsuiteCore
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteCore\Test\Unit\Index;

use Smile\ElasticsuiteCore\Index\IndexSettings;
use Smile\ElasticsuiteCore\Helper\IndexSettings as IndexSettingsHelper;
use Smile\ElasticsuiteCore\Index\Analysis\Config as AnalysisConfig;
use Smile\ElasticsuiteCore\Index\Indices\Config as IndicesConfig;

/**
 * Index settings test case.
 *
 * @category  Smile_Elasticsuite
 * @package   Smile\ElasticsuiteCore
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class IndexSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexSettings
     */
    private $indexSettings;

    /**
     * Test getting index alias by identifier.
     *
     * @return void
     */
    public function testGetIndexAliasFromIdentifier()
    {
        $alias = $this->indexSettings->getIndexAliasFromIdentifier('index_identifier', 'store_code');
        $this->assertEquals("index_identifier_store_code", $alias);
    }

    /**
     * Test getting new index name by identifier.
     *
     * @return void
     */
    public function testCreateIndexNameFromIdentifier()
    {

        $indexName = $this->indexSettings->createIndexNameFromIdentifier('index_identifier', 'store_code');
        $this->assertEquals("index_identifier_store_code", $indexName);
    }

    /**
     * Test getting indexing batch size.
     *
     * @return void
     */
    public function testGetBatchIndexingSize()
    {
        $this->assertEquals(100, $this->indexSettings->getBatchIndexingSize());
    }

    /**
     * Test getting index config by identifier.
     *
     * @return void
     */
    public function testGetIndexConfig()
    {
        $this->assertEquals('indexConfiguration', $this->indexSettings->getIndexConfig('index'));
    }

    /**
     * Test an exception is raised when accessing an index that does not exists in the configuration.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage No indices found with identifier invalidIndex
     *
     * @return void
     */
    public function testGetInvalidIndexConfig()
    {
        $this->indexSettings->getIndexConfig('invalidIndex');
    }

    /**
     * Test getting analysis config by store code.
     *
     * @return void
     */
    public function testGetAnalysisSettings()
    {
        $config = $this->indexSettings->getAnalysisSettings('store_code');
        $this->assertEquals('analysis_language_store_code', $config);
    }

    /**
     * Test getting index creation / install settings.
     *
     * @return void
     */
    public function testIndexingSettings()
    {
        $createIndexSettings  = $this->indexSettings->getCreateIndexSettings();
        $installIndexSettings = $this->indexSettings->getInstallIndexSettings();
        $this->assertEquals(1, $createIndexSettings['number_of_shards']);
        $this->assertEquals(0, $createIndexSettings['number_of_replicas']);
        $this->assertEquals(1, $installIndexSettings['number_of_replicas']);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $indexSettingHelper = $this->getIndexSettingsMock();
        $indicesConfig      = $this->getIndicesConfigMock();
        $analysisConfig     = $this->getAnalysisConfigMock();

        $this->indexSettings = new IndexSettings($indexSettingHelper, $indicesConfig, $analysisConfig);
    }

    /**
     * Generate the index settings helper mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getIndexSettingsMock()
    {
        $mockBuilder        = $this->getMockBuilder(IndexSettingsHelper::class);
        $indexSettingHelper = $mockBuilder->disableOriginalConstructor()->getMock();

        $indexIdentifierMethodStub = $this->returnCallback(
            function ($indexIdentifier, $store) {
                return "{$indexIdentifier}_{$store}";
            }
        );

        $getLanguageCodeStub = function ($store) {
            return "language_{$store}";
        };

        $indexSettingHelper->method('getIndexAliasFromIdentifier')->will($indexIdentifierMethodStub);
        $indexSettingHelper->method('createIndexNameFromIdentifier')->will($indexIdentifierMethodStub);
        $indexSettingHelper->method('getBatchIndexingSize')->will($this->returnValue(100));
        $indexSettingHelper->method('getNumberOfShards')->will($this->returnValue(1));
        $indexSettingHelper->method('getNumberOfReplicas')->will($this->returnValue(1));
        $indexSettingHelper->method('getLanguageCode')->will($this->returnCallback($getLanguageCodeStub));

        return $indexSettingHelper;
    }

    /**
     * Generate the indices config mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getIndicesConfigMock()
    {
        $indicesConfig = $this->getMockBuilder(IndicesConfig::class)->disableOriginalConstructor()->getMock();
        $indicesConfig->method('get')->will($this->returnValue(['index' => 'indexConfiguration']));

        return $indicesConfig;
    }

    /**
     * Generate the analysis config mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getAnalysisConfigMock()
    {
        $analysisConfig = $this->getMockBuilder(AnalysisConfig::class)->disableOriginalConstructor()->getMock();
        $getStub = function ($languageCode) {
            return "analysis_{$languageCode}";
        };

        $analysisConfig->method('get')->will($this->returnCallback($getStub));

        return $analysisConfig;
    }
}
