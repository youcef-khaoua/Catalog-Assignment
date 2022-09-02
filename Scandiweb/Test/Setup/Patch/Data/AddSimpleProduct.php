<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class AddSimpleProduct implements DataPatchInterface
{
    /**
     *
     * @var ModuleDataSetupInterface
     */
    protected ModuleDataSetupInterface $setup;

    /**
     *
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productInterfaceFactory;

    /**
     *
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     *
     * @var State
     */
    protected State $appState;

    /**
     *
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     *
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     *
     * @var SourceItemInterfaceFactory
     */
    protected SourceItemInterfaceFactory $sourceItemFactory;

    /**
     *
     * @var SourceItemsSaveInterface
     */
    protected SourceItemsSaveInterface $sourceItemsSaveInterface;

    /**
     *
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLink;

    /**
     *
     * @var array
     */
    protected array $sourceItems = [];

    /**
     * AddSimpleProduct construct
     *
     * @param ModuleDataSetupInterface $setup
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param State $appState
     * @param StoreManagerInterface $storeManager
     * @param EavSetup $eavSetup
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param CategoryLinkManagementInterface $categoryLink
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        CategoryLinkManagementInterface $categoryLink
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->setup = $setup;
        $this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->categoryLink = $categoryLink;
    }

    /**
     *
     * @return void
     */
    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     *
     * @return void
     */
    public function execute(): void
    {
        // create the product
        $product = $this->productInterfaceFactory->create();

        // check if the product already exists
        if ($product->getIdBySku('NIKE-SHOES-1')) {
            return;
        }

        // get the attribute set id from EavSetup object
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        // set attributes
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setName('Air Jordan 1')
            ->setSku('NIKE-SHOES-1')
            ->setUrlKey('air-jordan-1')
            ->setPrice(349.99)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);

        // save the product to the repository
        $product = $this->productRepository->save($product);

        
        // create a source item
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode('default');
        
        // set the quantity of items in stock
        $sourceItem->setQuantity(50);
        
        // add the product's SKU that will be linked to this source item
        $sourceItem->setSku($product->getSku());
        
        // set the stock status
        $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);
        $this->sourceItems[] = $sourceItem;
        
        // save the source item
        $this->sourceItemsSaveInterface->execute($this->sourceItems);

        // assign product to a category (Men->Shoes)
        $this->categoryLink->assignProductToCategories($product->getSku(), [3,4]);
    }

    /**
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
}
