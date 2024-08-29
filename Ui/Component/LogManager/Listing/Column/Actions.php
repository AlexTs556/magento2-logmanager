<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Ui\Component\LogManager\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /**
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        private readonly UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepares the data source by adding action links (view and delete) for each item.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
            $deleteUrlPath = $this->getData('config/deleteUrlPath') ?: '#';
            $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'filename';
            $name = $this->getData('name'); // Cache the component name

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id'])) {
                    $filename = $item['filename'];
                    $item[$name] = [
                        'view' => $this->createActionItem($viewUrlPath, $urlEntityParamName, $filename),
                        'delete' => $this->createDeleteActionItem($deleteUrlPath, $urlEntityParamName, $filename),
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Creates a view action item.
     *
     * @param string $urlPath
     * @param string $paramName
     * @param string $filename
     * @return array
     */
    private function createActionItem(string $urlPath, string $paramName, string $filename): array
    {
        return [
            'href' => $this->urlBuilder->getUrl($urlPath, [$paramName => $filename]),
            'label' => __('View')
        ];
    }

    /**
     * Creates a delete action item with a confirmation dialog.
     *
     * @param string $urlPath
     * @param string $paramName
     * @param string $filename
     * @return array
     */
    private function createDeleteActionItem(string $urlPath, string $paramName, string $filename): array
    {
        return [
            'href' => $this->urlBuilder->getUrl($urlPath, [$paramName => $filename]),
            'label' => __('Delete'),
            'confirm' => [
                'title' => __('Delete %1', $filename),
                'message' => __('Are you sure you want to delete %1?', $filename)
            ],
            'post' => true
        ];
    }
}
