<?php

namespace Burst\Efecty\Setup;

use \Magento\Framework\Setup\InstallSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 *
 * @package Burst\Efecty\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install Blog Posts table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('burst_efecty');

        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'id'
                )
                ->addColumn(
                    'increment_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Increment ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Order ID'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_DECIMAL,
                    null,
                    ['nullable' => false],
                    'Order Amount'
                )
                ->addColumn(
                    'valid_until',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Valid until'
                )
                ->addColumn(
                    'customer_email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Customer Email'
                )
                ->addColumn(
                    'customer_firstname',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Customer Firstname'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Transaction status'
                )
                ->addColumn(
                    'requestId',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    
                    'Reference ID'
                )
                ->addColumn(
                    'payment_url',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Payment URL'
                )
                ->addColumn(
					'created_at',
					Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
					'Created At'
				)->addColumn(
					'updated_at',
					Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
					'Updated At')
                ->setComment('Efecty Payments');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}