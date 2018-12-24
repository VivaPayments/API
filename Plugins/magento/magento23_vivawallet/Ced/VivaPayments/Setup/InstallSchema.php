<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\VivaPayments\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup; 
        $installer->startSetup();   

        $table = $installer->getConnection()->newTable(
            $installer->getTable('vivapayments_data')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'ref',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            ['nullable' => false],
            'Reference Id'
        )->addColumn(
            'ordercode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            ['nullable' => false],
            'Ordercode'
        )->addColumn(
            'email_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Email Address'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Order Id'
        )->addColumn(
            'total_cost',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,2',
            ['nullable' => false, 'default' => '0.00'],
            'Total Cost'
        )->addColumn(
            'currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '255',
            ['nullable' => false],
            'Currency'
        )->addColumn(
            'order_state',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'order_state'
        )->addColumn(
            'timestamp',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Payment Date'
        )->setComment(
                'VivaPayments Data Table'
        );
        $installer->getConnection()->createTable($table);
      
        $installer->endSetup(); 
    }
}