<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\AjaxLayeredNavPro\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $contextInterface)
    {
        $setup->startSetup();
        
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('catalog_eav_attribute'), 'extra_options')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('catalog_eav_attribute'),
                    'extra_options',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Extra Options',
                    ]
                );
        }
        $setup->endSetup();
    }
}
