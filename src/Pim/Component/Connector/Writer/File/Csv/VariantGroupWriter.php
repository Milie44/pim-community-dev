<?php

namespace Pim\Component\Connector\Writer\File\Csv;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\Batch\Step\WorkingDirectoryAwareInterface;
use Pim\Component\Connector\Writer\File\AbstractItemMediaWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;

/**
 * CSV variant group writer
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupWriter extends AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface,
    WorkingDirectoryAwareInterface,
    ArchivableWriterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getWriterConfiguration()
    {
        $parameters = $this->stepExecution->getJobParameters();

        return [
            'type'           => 'csv',
            'fieldDelimiter' => $parameters->get('delimiter'),
            'fieldEnclosure' => $parameters->get('enclosure'),
            'shouldAddBOM'   => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemIdentifier(array $variantGroup)
    {
        return $variantGroup['code'];
    }
}
