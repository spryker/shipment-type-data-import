<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\ShipmentTypeDataImport\Business\DataImportStep\ShipmentTypeStore;

use Orm\Zed\ShipmentType\Persistence\SpyShipmentTypeStoreQuery;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\PublishAwareStep;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use Spryker\Zed\ShipmentTypeDataImport\Business\DataSet\ShipmentTypeStoreDataSetInterface;
use Spryker\Zed\ShipmentTypeDataImport\Business\Validator\DataSetValidatorInterface;

class ShipmentTypeStoreWriteDataImportStep extends PublishAwareStep implements DataImportStepInterface
{
    /**
     * @uses \Spryker\Shared\ShipmentTypeStorage\ShipmentTypeStorageConfig::SHIPMENT_TYPE_PUBLISH
     *
     * @var string
     */
    protected const SHIPMENT_TYPE_PUBLISH = 'ShipmentType.shipment_type.publish';

    /**
     * @var \Spryker\Zed\ShipmentTypeDataImport\Business\Validator\DataSetValidatorInterface
     */
    protected DataSetValidatorInterface $dataSetValidator;

    public function __construct(DataSetValidatorInterface $dataSetValidator)
    {
        $this->dataSetValidator = $dataSetValidator;
    }

    public function execute(DataSetInterface $dataSet): void
    {
        $this->dataSetValidator->assertNoEmptyColumns($dataSet);

        $shipmentTypeStoreEntity = $this->getShipmentTypeStoreQuery()
            ->filterByFkShipmentType($dataSet[ShipmentTypeStoreDataSetInterface::ID_SHIPMENT_TYPE])
            ->filterByFkStore($dataSet[ShipmentTypeStoreDataSetInterface::ID_STORE])
            ->findOneOrCreate();
        $shipmentTypeStoreEntity->fromArray($dataSet->getArrayCopy());

        if (!$shipmentTypeStoreEntity->isNew() && !$shipmentTypeStoreEntity->isModified()) {
            return;
        }
        $shipmentTypeStoreEntity->save();

        $this->addPublishEvents(static::SHIPMENT_TYPE_PUBLISH, $shipmentTypeStoreEntity->getFkShipmentType());
    }

    protected function getShipmentTypeStoreQuery(): SpyShipmentTypeStoreQuery
    {
        return SpyShipmentTypeStoreQuery::create();
    }
}
