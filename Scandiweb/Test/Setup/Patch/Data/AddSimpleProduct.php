<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\App\State;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;

class AddSimpleProduct implements DataPatchInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected $categoryLink;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AddSimpleProduct construct
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param CategoryLinkManagementInterface $categoryLink
     * @param State $state
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        CategoryLinkManagementInterface $categoryLink,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->categoryLink = $categoryLink;
        $state->setAreaCode('adminhtml');
    }

    /**
     *
     * @return void
     */
    public function apply()
    {
        $product = $this->productFactory->create();

        $simpleProductArray = [
            [
                'sku'               => 'NIKE-SHOES',
                'name'              => 'Air Jordan',
                'attribute_id'      => '4',
                'status'            => 1,
                'weight'            => 1.4,
                'price'             => 350,
                'visibility'        => '4',
                'type_id'           => 'simple',
            ]
        ];

        foreach ($simpleProductArray as $data) {
            /** create product */
            $product = $this->productFactory->create();
            $product->setSku($data['sku'])
                ->setName($data['name'])
                ->setAttributeSetId($data['attribute_id'])
                ->setStatus($data['status'])
                ->setWeight($data['weight'])
                ->setPrice($data['price'])
                ->setVisibility($data['visibility'])
                ->setTypeId($data['type_id'])
                ->setStockData(
                    array(
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => 150
                    )
                );

            $product = $this->productRepository->save($product);
            /** Assign product to category */
            $this->categoryLink->assignProductToCategories($product->getSku(), [2]);
            $product->save();
        }
    }

    /**
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }
}
