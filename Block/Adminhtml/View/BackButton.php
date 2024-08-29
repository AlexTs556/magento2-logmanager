<?php

declare(strict_types=1);

namespace ProDevTools\LogManager\Block\Adminhtml\View;

use Magento\Backend\Block\Widget\Container;

class BackButton extends Container
{
    /**
     * Constructor method to add a back button to the admin view.
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back',
            ]
        );
    }

    /**
     * Retrieve the URL for the back button to redirect.
     *
     * @return string
     */
    protected function getBackUrl(): string
    {
        return $this->getUrl('logmanager/log/');
    }
}
